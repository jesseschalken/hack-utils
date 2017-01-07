<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class Stat {
    public abstract function mode();
    public abstract function uid();
    public abstract function gid();
    public abstract function size();
    public abstract function atime();
    public abstract function mtime();
    public abstract function ctime();
    public function toArray() {
      return array(
        "dev" => 0,
        "ino" => 0,
        "mode" => $this->mode(),
        "nlink" => 1,
        "uid" => $this->uid(),
        "gid" => $this->gid(),
        "rdev" => -1,
        "size" => $this->size(),
        "atime" => $this->atime(),
        "mtime" => $this->mtime(),
        "ctime" => $this->ctime(),
        "blksize" => -1,
        "blocks" => -1
      );
    }
    public final function isFile() {
      return S_ISREG($this->mode());
    }
    public final function isDir() {
      return S_ISDIR($this->mode());
    }
    public final function isLink() {
      return S_ISLNK($this->mode());
    }
    public final function isSocket() {
      return S_ISSOCK($this->mode());
    }
    public final function isPipe() {
      return S_ISFIFO($this->mode());
    }
    public final function isChar() {
      return S_ISCHR($this->mode());
    }
    public final function isBlock() {
      return S_ISBLK($this->mode());
    }
    public final function modeSymbolic() {
      return symbolic_mode($this->mode());
    }
    public final function modeOctal() {
      return pad_left(\decoct($this->mode() & 07777), 4, "0");
    }
  }
  final class ArrayStat extends Stat {
    private $stat;
    public function __construct($stat) {
      $this->stat = $stat;
    }
    public function mtime() {
      return $this->stat[\hacklib_id("mtime")];
    }
    public function atime() {
      return $this->stat[\hacklib_id("atime")];
    }
    public function ctime() {
      return $this->stat[\hacklib_id("ctime")];
    }
    public function size() {
      return $this->stat[\hacklib_id("size")];
    }
    public function mode() {
      return $this->stat[\hacklib_id("mode")];
    }
    public function uid() {
      return $this->stat[\hacklib_id("uid")];
    }
    public function gid() {
      return $this->stat[\hacklib_id("gid")];
    }
    public function toArray() {
      return $this->stat;
    }
  }
}
