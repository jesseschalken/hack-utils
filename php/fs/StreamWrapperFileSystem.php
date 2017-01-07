<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
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
      StrictErrors::start()->finishTrue(\rename($from, $to, $ctx));
    }
    public final function readdir($path) {
      $ctx = $this->getContext();
      $path = $this->wrapPath($path);
      $ret = array();
      $dir = StrictErrors::start()->finishResource(\opendir($path, $ctx));
      for (; \hacklib_cast_as_boolean($p = \readdir($dir)); $p !== false) {
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
      StrictErrors::start()->finishTrue(\mkdir($path, $mode, false, $ctx));
    }
    public final function unlink($path) {
      $ctx = $this->getContext();
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\unlink($path, $ctx));
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
      $errors = StrictErrors::start();
      $stat = \hacklib_cast_as_boolean($lstat) ? \lstat($path) : \stat($path);
      $stat = StatFailed::assertArray($stat);
      $errors->finish();
      return new ArrayStat($stat);
    }
    private function _trystat($path, $lstat) {
      try {
        return $this->_stat($path, $lstat);
      } catch (\ErrorException $e) {
        return new_null();
      } catch (StatFailed $e) {
        return new_null();
      }
    }
    public final function rmdir($path) {
      $ctx = $this->getContext();
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\rmdir($path, $ctx));
    }
    public final function chmod($path, $mode) {
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\chmod($path, $mode));
    }
    public final function chown($path, $uid) {
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\chown($path, (int) $uid));
    }
    public final function chgrp($path, $gid) {
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\chgrp($path, (int) $gid));
    }
    public final function utime($path, $atime, $mtime) {
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\touch($path, $mtime, $atime));
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
}
