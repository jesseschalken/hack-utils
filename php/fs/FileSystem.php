<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class TestFileSystem extends Test {
    public function run() {
      $fs = new LocalFileSystem();
      $path = "/tmp/hufs-test-".\mt_rand();
      self::testFilesystem($fs, $path);
      $fs = new FileSystemStreamWrapper($fs);
      self::testFilesystem($fs, $path);
    }
    private static function testFilesystem($fs, $base) {
      self::assertEqual($fs->trystat($base), NULL_INT);
      $fs->mkdir($base);
      self::assertEqual($fs->stat($base)->modeSymbolic(), "drwxr-xr-x");
      $file = $fs->join($base, "foo");
      $fs->writeFile($file, "contents");
      self::assertEqual($fs->readFile($file), "contents");
      $open = $fs->open($file, "rb");
      self::assertEqual($open->eof(), false);
      self::assertEqual($open->tell(), 0);
      self::assertEqual($open->read(4), "cont");
      self::assertEqual($open->eof(), false);
      self::assertEqual($open->tell(), 4);
      $open->seek(2);
      self::assertEqual($open->tell(), 2);
      $open->seek(2, \SEEK_CUR);
      self::assertEqual($open->tell(), 4);
      self::assertEqual($open->eof(), false);
      self::assertEqual($open->read(100), "ents");
      self::assertEqual($open->read(100), "");
      self::assertEqual($open->eof(), true);
      self::assertEqual($open->getSize(), 8);
      self::assertEqual($open->stat()->modeSymbolic(), "-rw-r--r--");
      self::assertEqual($open->getContents(), "");
      self::assertEqual($open->__toString(), "contents");
      self::assertEqual($open->getContents(), "");
      $open->rewind();
      self::assertEqual($open->getContents(), "contents");
      self::assertEqual($open->tell(), 8);
      self::assertEqual($open->isReadable(), true);
      self::assertEqual($open->isWritable(), false);
      self::assertEqual($open->isSeekable(), true);
      $open->close();
      $open = $fs->open($file, "wb+");
      self::assertEqual($open->tell(), 0);
      self::assertEqual($open->eof(), false);
      self::assertEqual($open->getSize(), 0);
      self::assertEqual($open->getContents(), "");
      self::assertEqual($open->__toString(), "");
      self::assertEqual($open->write("hello"), 5);
      self::assertEqual($open->tell(), 5);
      self::assertEqual($open->eof(), true);
      self::assertEqual($open->getContents(), "");
      self::assertEqual($open->__toString(), "hello");
      $open->rewind();
      self::assertEqual($open->getContents(), "hello");
      self::assertEqual($open->getContents(), "");
      $open->seek(2);
      self::assertEqual($open->tell(), 2);
      self::assertEqual($open->write("__"), 2);
      self::assertEqual($open->tell(), 4);
      self::assertEqual($open->getContents(), "o");
      self::assertEqual($open->tell(), 5);
      self::assertEqual($open->__toString(), "he__o");
      self::assertEqual($open->tell(), 5);
      self::assertEqual($open->eof(), true);
      if ($fs instanceof SymlinkFileSystemInterface) {
        $fs->symlink($file."2", $file);
        self::assertEqual($fs->stat($file)->modeSymbolic(), "-rw-r--r--");
        self::assertEqual($fs->stat($file."2")->modeSymbolic(), "-rw-r--r--");
        self::assertEqual($fs->lstat($file)->modeSymbolic(), "-rw-r--r--");
        self::assertEqual(
          $fs->lstat($file."2")->modeSymbolic(),
          "lrwxrwxrwx"
        );
      }
      $fs->unlink($file);
      $fs->rmdirRec($base);
    }
  }
  interface FileSystemPathInterface {
    public function split($path, $i);
    public function join($path1, $path2);
  }
  interface SymlinkFileSystemInterface {
    public function symlink($path, $target);
    public function readlink($path);
    public function realpath($path);
    public function lchown($path, $uid);
    public function lchgrp($path, $gid);
  }
  class StatFailed extends Exception {}
  abstract class FileSystem implements FileSystemPathInterface {
    public abstract function mkdir($path, $mode = 0777);
    public abstract function readdir($path);
    public abstract function rmdir($path);
    public abstract function open($path, $mode);
    public abstract function rename($oldpath, $newpath);
    public abstract function unlink($path);
    public abstract function stat($path);
    public abstract function lstat($path);
    public abstract function trystat($path);
    public abstract function trylstat($path);
    public abstract function chmod($path, $mode);
    public abstract function chown($path, $uid);
    public abstract function chgrp($path, $gid);
    public abstract function utime($path, $atime, $mtime);
    public final function remove($path) {
      $stat = $this->lstat($path);
      if (\hacklib_cast_as_boolean($stat) &&
          \hacklib_cast_as_boolean($stat->isDir())) {
        $this->rmdir($path);
      } else {
        $this->unlink($path);
      }
    }
    public final function readdirPaths($path) {
      $ret = array();
      foreach ($this->readdir($path) as $p) {
        $ret[] = $this->join($path, $p);
      }
      return $ret;
    }
    public final function readdirPathsRec($path) {
      $ret = array();
      foreach ($this->readdirPaths($path) as $p) {
        $ret[] = $p;
        $stat = $this->stat($p);
        if (\hacklib_cast_as_boolean($stat) &&
            \hacklib_cast_as_boolean($stat->isDir())) {
          foreach ($this->readdirPathsRec($p) as $p2) {
            $ret[] = $p2;
          }
        }
      }
      return $ret;
    }
    public final function readdirRec($path) {
      $ret = array();
      foreach ($this->readdir($path) as $p) {
        foreach ($this->expandDirs($this->join($path, $p)) as $p2) {
          $ret[] = $this->join($p, $p2);
        }
      }
      return $ret;
    }
    public final function expandDirs($path) {
      $stat = $this->stat($path);
      if (\hacklib_cast_as_boolean($stat) &&
          \hacklib_cast_as_boolean($stat->isDir())) {
        return $this->readdirRec($path);
      }
      return array("");
    }
    public final function removeRec($path) {
      $stat = $this->lstat($path);
      if (\hacklib_cast_as_boolean($stat) &&
          \hacklib_cast_as_boolean($stat->isDir())) {
        return $this->rmdirRec($path);
      }
      $this->unlink($path);
      return 1;
    }
    public final function rmdirRec($path) {
      $ret = 0;
      foreach ($this->readdirPaths($path) as $p) {
        $ret += $this->removeRec($p);
      }
      $this->rmdir($path);
      $ret++;
      return $ret;
    }
    public final function createDirs($path, $mode = 0777) {
      list($dir, $child) = $this->split($path, -1);
      if (($child !== "") &&
          (!\hacklib_cast_as_boolean($this->lexists($dir)))) {
        $this->mkdirRec($dir, $mode);
      }
    }
    public final function exists($path) {
      return \hacklib_cast_as_boolean($this->stat($path)) ? true : false;
    }
    public final function lexists($path) {
      return \hacklib_cast_as_boolean($this->lstat($path)) ? true : false;
    }
    public final function readFile($path) {
      return $this->open($path, "rb")->getContents();
    }
    public final function writeFile($path, $contents) {
      return $this->open($path, "wb")->write($contents);
    }
    public final function appendFile($path, $contents) {
      return $this->open($path, "ab")->write($contents);
    }
    public final function writeFileRec($path, $contents) {
      $this->createDirs($path);
      return $this->writeFile($path, $contents);
    }
    public final function appendFileRec($path, $contents) {
      $this->createDirs($path);
      return $this->appendFile($path, $contents);
    }
    public final function mkdirRec($path, $mode = 0777) {
      $this->createDirs($path, $mode);
      $this->mkdir($path, $mode);
    }
  }
}
