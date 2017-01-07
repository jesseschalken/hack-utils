<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class FileSystem implements FileSystemInterface {
    public abstract function open($path, $mode);
    public abstract function stat($path);
    public abstract function lstat($path);
    public abstract function trystat($path);
    public abstract function trylstat($path);
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
