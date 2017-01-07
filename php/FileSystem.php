<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class LocalStreamWrapper implements StreamWrapperInterface {
    public function wrapPath($path) {
      if ($path === "") {
        $path = ".";
      }
      return make_path_local($path);
    }
    public function getContext() {
      return \stream_context_get_default();
    }
    public function join($path1, $path2) {
      return Path::parse($path1)->join(Path::parse($path2))->format();
    }
    public function split($path, $i) {
      list($a, $b) = Path::parse($path)->split($i);
      return array($a->format(), $b->format());
    }
  }
  abstract class FileSystem implements FileSystemInterface {
    public abstract function open($path, $mode);
    public abstract function stat($path);
    public abstract function lstat($path);
    public abstract function trystat($path);
    public abstract function trylstat($path);
    public final function remove($path) {
      $stat = $this->lstat($path);
      if ($stat && $stat->isDir()) {
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
        if ($stat && $stat->isDir()) {
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
      if ($stat && $stat->isDir()) {
        return $this->readdirRec($path);
      }
      return array("");
    }
    public final function removeRec($path) {
      $stat = $this->lstat($path);
      if ($stat && $stat->isDir()) {
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
      if (($child !== "") && (!$this->lexists($dir))) {
        $this->mkdirRec($dir, $mode);
      }
    }
    public final function exists($path) {
      return $this->stat($path) ? true : false;
    }
    public final function lexists($path) {
      return $this->lstat($path) ? true : false;
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
  class StreamWrapperFileSystem extends FileSystem
    implements StreamWrapperInterface {
    private $wrapper;
    public function __construct($wrapper) {
      $this->wrapper = $wrapper;
    }
    public final function open($path, $mode) {
      $ctx = $this->getContext();
      $path = $this->wrapPath($path);
      return new FOpenStream($path, $mode, $ctx);
    }
    public final function stat($path) {
      return $this->_stat($path, false);
    }
    public final function trystat($path) {
      return $this->_trystat($path, false);
    }
    public final function rename($from, $to) {
      $ctx = $this->getContext();
      $from = $this->wrapPath($from);
      $to = $this->wrapPath($to);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\rename($from, $to, $ctx));
    }
    public final function readdir($path) {
      $ctx = $this->getContext();
      $path = $this->wrapPath($path);
      $ret = array();
      FileSystemException::prepare();
      $dir = FileSystemException::assertResource(\opendir($path, $ctx));
      for (; $p = \readdir($dir); $p !== false) {
        if (($p === ".") || ($p === "..")) {
          continue;
        }
        $ret[] = $p;
      }
      \closedir($dir);
      return $ret;
    }
    public final function mkdir($path, $mode = 0777) {
      $ctx = $this->getContext();
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\mkdir($path, $mode, false, $ctx));
    }
    public final function unlink($path) {
      $ctx = $this->getContext();
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\unlink($path, $ctx));
    }
    public final function lstat($path) {
      return $this->_stat($path, true);
    }
    public final function trylstat($path) {
      return $this->_trystat($path, true);
    }
    private function _stat($path, $lstat) {
      $path = $this->wrapPath($path);
      \clearstatcache();
      FileSystemException::prepare();
      $stat = FileSystemException::assertArray(
        $lstat ? \lstat($path) : \stat($path)
      );
      return new ArrayStat($stat);
    }
    private function _statThrowPhpErrors($path, $lstat) {
      ErrorException::setErrorHandler();
      try {
        $stat = $this->_stat($path, $lstat);
      } catch (\Exception $e) {
        ErrorException::restoreErrorHandler();
        throw $e;
      }
      ErrorException::restoreErrorHandler();
      return $stat;
    }
    private function _trystat($path, $lstat) {
      try {
        return $this->_statThrowPhpErrors($path, $lstat);
      } catch (\ErrorException $e) {
        return new_null();
      } catch (FileSystemException $e) {
        return new_null();
      }
    }
    public final function rmdir($path) {
      $ctx = $this->getContext();
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\rmdir($path, $ctx));
    }
    public final function chmod($path, $mode) {
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\chmod($path, $mode));
    }
    public final function chown($path, $uid) {
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\chown($path, (int) $uid));
    }
    public final function chgrp($path, $gid) {
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\chgrp($path, (int) $gid));
    }
    public final function utime($path, $atime, $mtime) {
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\touch($path, $mtime, $atime));
    }
    public final function wrapPath($path) {
      return $this->wrapper->wrapPath($path);
    }
    public final function getContext() {
      return $this->wrapper->getContext();
    }
    public final function join($path1, $path2) {
      return $this->wrapper->join($path1, $path2);
    }
    public final function split($path, $i) {
      return $this->wrapper->split($path, $i);
    }
  }
  final class LocalFileSystem extends StreamWrapperFileSystem
    implements SymlinkFileSystemInterface {
    public function __construct() {
      parent::__construct(new LocalStreamWrapper());
    }
    public final function symlink($path, $target) {
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\symlink($target, $path));
    }
    public final function readlink($path) {
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      return FileSystemException::assertString(\readlink($path));
    }
    public final function realpath($path) {
      $path = $this->wrapPath($path);
      \clearstatcache();
      FileSystemException::prepare();
      return FileSystemException::assertString(\realpath($path));
    }
    public final function lchown($path, $uid) {
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\lchown($path, (int) $uid));
    }
    public final function lchgrp($path, $gid) {
      $path = $this->wrapPath($path);
      FileSystemException::prepare();
      FileSystemException::assertTrue(\lchgrp($path, (int) $gid));
    }
  }
  class FileSystemException extends Exception {
    public final static function prepare() {
      ErrorException::clearLast();
    }
    public final static function create($actual, $expected) {
      return ErrorException::getLast() ?: parent::create($actual, $expected);
    }
  }
}
