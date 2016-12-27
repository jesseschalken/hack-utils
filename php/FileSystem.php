<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class FileSystem {
    protected function __construct() {}
    public function __destruct() {}
    public abstract function mkdir($path, $mode = 0777);
    public abstract function readdir($path);
    public abstract function rmdir($path);
    public abstract function rename($oldpath, $newpath);
    public abstract function unlink($path);
    public final function remove($path) {
      $stat = $this->lstat($path);
      if ($stat && $stat->isDir()) {
        $this->rmdir($path);
      } else {
        $this->unlink($path);
      }
    }
    public final function readdir_rec($path) {
      $parsed = $this->path($path);
      $ret = array();
      foreach ($this->readdir($path) as $p) {
        $ret[] = $p;
        $full = $parsed->join_str($p)->format();
        $stat = $this->stat($full);
        if ($stat && $stat->isDir()) {
          foreach ($this->readdir_rec($full) as $p2) {
            $ret[] = $p.$this->sep().$p2;
          }
        }
      }
      return $ret;
    }
    public final function remove_rec($path) {
      $stat = $this->lstat($path);
      if ($stat && $stat->isDir()) {
        return $this->rmdir_rec($path);
      }
      $this->unlink($path);
      return 1;
    }
    public final function rmdir_rec($path) {
      $parsed = $this->path($path);
      $ret = 0;
      foreach ($this->readdir($path) as $p) {
        $ret += $this->rmdir_rec($parsed->join_str($p)->format());
      }
      $this->rmdir($path);
      $ret++;
      return $ret;
    }
    public final function mkdir_rec($path, $mode = 0777) {
      $dir = \hacklib_nullsafe($this->path($path)->dir())->format();
      if (($dir !== null) && (!$this->lstat($dir))) {
        $this->mkdir_rec($dir, $mode);
      }
      $this->mkdir($path, $mode);
    }
    public final function exists($path) {
      return $this->stat($path) ? true : false;
    }
    public final function lexists($path) {
      return $this->lstat($path) ? true : false;
    }
    public abstract function stat($path);
    public abstract function chmod($path, $mode);
    public abstract function chown($path, $uid);
    public abstract function chgrp($path, $gid);
    public abstract function utime($path, $atime, $mtime);
    public abstract function open($path, $mode);
    public abstract function symlink($path, $contents);
    public abstract function readlink($path);
    public abstract function lstat($path);
    public abstract function lchown($path, $uid);
    public abstract function lchgrp($path, $gid);
    public abstract function realpath($path);
    public abstract function pwd();
    public function path($path) {
      return PosixPath::parse($path);
    }
    public function sep() {
      return "/";
    }
    public function readFile($path) {
      return $this->open($path, "rb")->getContents();
    }
    public function writeFile($path, $contents) {
      return $this->open($path, "wb")->write($contents);
    }
    public function appendFile($path, $contents) {
      return $this->open($path, "ab")->write($contents);
    }
    public function toStreamWrapper() {
      return new FileSystemStreamWrapper($this);
    }
  }
  class ErrorAssert extends \RuntimeException {
    public final static function isZero($name, $ret) {
      if ($ret !== 0) {
        throw self::create($name);
      }
    }
    public final static function isArray($name, $ret) {
      if (!\is_array($ret)) {
        throw self::create($name);
      }
      return $ret;
    }
    public final static function isString($name, $ret) {
      if (!\is_string($ret)) {
        throw self::create($name);
      }
      return $ret;
    }
    public final static function isInt($name, $ret) {
      if (!\is_int($ret)) {
        throw self::create($name);
      }
      return $ret;
    }
    public final static function isTrue($name, $ret) {
      if ($ret !== true) {
        throw self::create($name);
      }
    }
    public final static function isResource($name, $ret) {
      if (!\is_resource($ret)) {
        throw self::create($name);
      }
      return $ret;
    }
    public final static function isBool($name, $ret) {
      if (!\is_bool($ret)) {
        throw self::create($name);
      }
      return $ret;
    }
    private static function create($name) {
      $error = \error_get_last();
      $msg = $name."() failed";
      if ($error) {
        $e = new self($msg.": ".$error["message"]);
        $e->file = $error["file"];
        $e->line = $error["line"];
        return $e;
      }
      return new self($msg);
    }
  }
  abstract class StreamWrapper extends FileSystem {
    public final function open($path, $mode) {
      $path = $this->wrapPath($path);
      return new FOpenStream($path, $mode, $this->getContext());
    }
    public final function stat($path) {
      $path = $this->wrapPath($path);
      \clearstatcache();
      if (!\file_exists($path)) {
        return null;
      }
      return new ArrayStat(ErrorAssert::isArray("stat", \stat($path)));
    }
    public final function rename($from, $to) {
      $from = $this->wrapPath($from);
      $to = $this->wrapPath($to);
      ErrorAssert::isTrue("rename", \rename($from, $to, $this->getContext()));
    }
    public final function readdir($path) {
      $path = $this->wrapPath($path);
      $ret = array();
      $dir = ErrorAssert::isResource(
        "opendir",
        \opendir($path, $this->getContext())
      );
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
      $path = $this->wrapPath($path);
      ErrorAssert::isTrue(
        "mkdir",
        \mkdir($path, $mode, false, $this->getContext())
      );
    }
    public final function unlink($path) {
      $path = $this->wrapPath($path);
      ErrorAssert::isTrue("unlink", \unlink($path, $this->getContext()));
    }
    public final function lstat($path) {
      $path = $this->wrapPath($path);
      \clearstatcache();
      if ((!\file_exists($path)) && (!\is_link($path))) {
        return new_null();
      }
      return new ArrayStat(ErrorAssert::isArray("lstat", \lstat($path)));
    }
    public final function rmdir($path) {
      $path = $this->wrapPath($path);
      ErrorAssert::isTrue("rmdir", \rmdir($path, $this->getContext()));
    }
    public final function chmod($path, $mode) {
      $path = $this->wrapPath($path);
      ErrorAssert::isTrue("chmod", \chmod($path, $mode));
    }
    public final function chown($path, $uid) {
      $path = $this->wrapPath($path);
      ErrorAssert::isTrue("chown", \chown($path, (int) $uid));
    }
    public final function chgrp($path, $gid) {
      $path = $this->wrapPath($path);
      ErrorAssert::isTrue("chgrp", \chgrp($path, (int) $gid));
    }
    public final function utime($path, $atime, $mtime) {
      $path = $this->wrapPath($path);
      ErrorAssert::isTrue("touch", \touch($path, $mtime, $atime));
    }
    public final function readFile($path) {
      $path = $this->wrapPath($path);
      return ErrorAssert::isString(
        "file_get_contents",
        \file_get_contents($path, false, $this->getContext())
      );
    }
    public final function writeFile($path, $contents) {
      $path = $this->wrapPath($path);
      return ErrorAssert::isInt(
        "file_put_contents",
        \file_put_contents($path, $contents, 0, $this->getContext())
      );
    }
    public final function appendFile($path, $contents) {
      $path = $this->wrapPath($path);
      return ErrorAssert::isInt(
        "file_put_contents",
        \file_put_contents(
          $path,
          $contents,
          \FILE_APPEND,
          $this->getContext()
        )
      );
    }
    public final function toStreamWrapper() {
      return $this;
    }
    public abstract function wrapPath($path);
    public function getContext() {
      return \stream_context_get_default();
    }
  }
  final class LocalFileSystem extends StreamWrapper {
    public static function create() {
      return new self();
    }
    public final function symlink($path, $target) {
      $path = $this->wrapPath($path);
      ErrorAssert::isTrue("symlink", \symlink($path, $target));
    }
    public final function readlink($path) {
      $path = $this->wrapPath($path);
      return ErrorAssert::isString("readlink", \readlink($path));
    }
    public final function realpath($path) {
      $path = $this->wrapPath($path);
      \clearstatcache();
      return ErrorAssert::isString("realpath", \realpath($path));
    }
    public final function lchown($path, $uid) {
      $path = $this->wrapPath($path);
      ErrorAssert::isTrue("lchown", \lchown($path, (int) $uid));
    }
    public final function lchgrp($path, $gid) {
      $path = $this->wrapPath($path);
      ErrorAssert::isTrue("lchgrp", \lchgrp($path, (int) $gid));
    }
    public final function pwd() {
      return ErrorAssert::isString("getcwd", \getcwd());
    }
    public function path($path) {
      return Path::parse($path);
    }
    public function sep() {
      return \DIRECTORY_SEPARATOR;
    }
    public function wrapPath($path) {
      $regex =
        "\n      ^(\n          [a-zA-Z0-9+\\-.]{2,}\n          ://\n        |\n          data:\n        |\n          zlib:\n      )\n    ";
      if (self::regex($regex)->matches($path)) {
        return ".".$this->sep().$path;
      }
      return $path;
    }
    private static function regex($regex) {
      return PCRE\Pattern::create($regex, "xDsS");
    }
  }
}
