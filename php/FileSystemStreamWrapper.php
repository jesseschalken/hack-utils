<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class _streamWrapper {
    public static function className() {
      return __CLASS__;
    }
    public $context;
    private $readdir = array();
    private $readdir_i = 0;
    private $stream;
    public function __construct() {}
    public function __destruct() {}
    public function dir_closedir() {
      return true;
    }
    public function dir_opendir($path, $options) {
      list($fs, $path) = $this->unwrap($path);
      $this->readdir = concat(array(".", ".."), $fs->readdir($path));
      $this->readdir_i = 0;
      return true;
    }
    public function dir_readdir() {
      $i = $this->readdir_i++;
      if ($i >= \count($this->readdir)) {
        return false;
      }
      return $this->readdir[$i];
    }
    public function dir_rewinddir() {
      $this->readdir_i = 0;
      return true;
    }
    public function mkdir($path, $mode, $options) {
      list($fs, $path) = $this->unwrap($path);
      if ($options & \STREAM_MKDIR_RECURSIVE) {
        $fs->mkdir_rec($path, $mode);
      } else {
        $fs->mkdir($path, $mode);
      }
      return true;
    }
    public function rename($path_from, $path_to) {
      list($fs, $path_from) = $this->unwrap($path_from);
      list($_, $path_to) = $this->unwrap($path_to);
      $fs->rename($path_from, $path_to);
      return true;
    }
    public function rmdir($path, $options) {
      list($fs, $path) = $this->unwrap($path);
      $fs->rmdir($path);
      return true;
    }
    public function stream_close() {
      $this->stream()->close();
    }
    public function stream_eof() {
      return $this->stream()->eof();
    }
    public function stream_flush() {
      $this->stream()->flush();
      return true;
    }
    public function stream_lock($operation) {
      return $this->stream()->lock($operation);
    }
    public function stream_metadata($path, $option, $value) {
      list($fs, $path) = $this->unwrap($path);
      switch ($option) {
        case \STREAM_META_TOUCH:
          list($mtime, $atime) = \is_array($value) ? $value : array();
          $fs->utime($path, $atime, $mtime);
          return true;
        case \STREAM_META_OWNER_NAME:
          $fs->chown($path, $this->name2uid((string) $value));
          return true;
        case \STREAM_META_OWNER:
          $fs->chown($path, (int) $value);
          return true;
        case \STREAM_META_GROUP_NAME:
          $fs->chgrp($path, $this->name2gid((string) $value));
          return true;
        case \STREAM_META_GROUP:
          $fs->chgrp($path, (int) $value);
          return true;
        case \STREAM_META_ACCESS:
          $fs->chmod($path, (int) $value);
          return true;
        default:
          return false;
      }
    }
    private function name2gid($name) {
      $data = \posix_getgrnam($name);
      if (!$data) {
        throw new \RuntimeException(\posix_strerror(\posix_get_last_error()));
      }
      return $data["gid"];
    }
    private function name2uid($name) {
      $data = \posix_getpwnam($name);
      if (!$data) {
        throw new \RuntimeException(\posix_strerror(\posix_get_last_error()));
      }
      return $data["uid"];
    }
    public function stream_open($path, $mode, $options, $opened_path) {
      if ($options & \STREAM_USE_PATH) {
        throw new \Exception("STREAM_USE_PATH is not supported");
      }
      list($fs, $path) = $this->unwrap($path);
      $this->stream = $fs->open($path, $mode);
      return true;
    }
    public function stream_read($count) {
      return $this->stream()->read($count);
    }
    public function stream_seek($offset, $whence = \SEEK_SET) {
      $this->stream()->seek($offset, $whence);
      return true;
    }
    public function stream_set_option($option, $arg1, $arg2) {
      switch ($option) {
        case \STREAM_OPTION_BLOCKING:
          throw new \Exception("STREAM_OPTION_BLOCKING is not supported");
        case \STREAM_OPTION_READ_TIMEOUT:
          throw new \Exception("STREAM_OPTION_READ_TIMEOUT is not supported");
        case \STREAM_OPTION_WRITE_BUFFER:
          $this->stream()
            ->setbuf(($arg1 === \STREAM_BUFFER_NONE) ? 0 : $arg2);
          return true;
        default:
          return false;
      }
    }
    public function stream_stat() {
      return $this->stream()->stat()->toArray();
    }
    public function stream_tell() {
      return $this->stream()->tell();
    }
    public function stream_truncate($new_size) {
      $this->stream()->truncate($new_size);
      return true;
    }
    public function stream_write($data) {
      return $this->stream()->write($data);
    }
    public function unlink($path) {
      list($fs, $path) = $this->unwrap($path);
      $fs->unlink($path);
      return true;
    }
    public function url_stat($path, $flags) {
      list($fs, $path) = $this->unwrap($path);
      if ($flags & \STREAM_URL_STAT_LINK) {
        $stat = $fs->lstat($path);
      } else {
        $stat = $fs->stat($path);
      }
      if (!$stat) {
        if ($flags & \STREAM_URL_STAT_QUIET) {
          return false;
        }
        throw new \RuntimeException(
          "Cannot stat '".$path."', path does not exist"
        );
      }
      return $stat->toArray();
    }
    private function unwrap($path) {
      return FileSystemStreamWrapper::unwrapPath($path);
    }
    private function stream() {
      if (!$this->stream) {
        throw new \Exception("No stream is open");
      }
      return $this->stream;
    }
  }
  final class FileSystemStreamWrapper extends StreamWrapper {
    private static $next = 1;
    private static $fss = array();
    private static $registered = false;
    public static function unwrapPath($path) {
      $match =
        PCRE\Pattern::create("^hu-fs://(.*?):(.*)\044", "xDsS")
          ->matchOrThrow($path);
      return array(self::$fss[$match->get(1)], $match->get(2));
    }
    private $id;
    private $fs;
    public function __construct($fs) {
      $this->fs = $fs;
      parent::__construct();
      if (!self::$registered) {
        \stream_wrapper_register("hu-fs", _streamWrapper::className());
        self::$registered = true;
      }
      $this->id = self::$next++;
      self::$fss[$this->id] = $fs;
    }
    public function __destruct() {
      unset(self::$fss[$this->id]);
    }
    public function wrapPath($path) {
      return "hu-fs://".$this->id.":".$path;
    }
    public function sep() {
      return $this->fs->sep();
    }
    public function unwrap() {
      return $this->fs;
    }
    public function path($path) {
      return $this->fs->path($path);
    }
    public function symlink($path, $contents) {
      $this->fs->symlink($path, $contents);
    }
    public function readlink($path) {
      return $this->fs->readlink($path);
    }
    public function lchown($path, $uid) {
      $this->fs->lchown($path, $uid);
    }
    public function lchgrp($path, $gid) {
      $this->fs->lchgrp($path, $gid);
    }
    public function realpath($path) {
      return $this->fs->realpath($path);
    }
    public function pwd() {
      return $this->fs->pwd();
    }
  }
}
