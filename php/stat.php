<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  const S_IFMT = 00170000;
  const S_IFWHT = 0160000;
  const S_IFDOOR = 0150000;
  const S_IFSOCK = 0140000;
  const S_IFSHAD = 0130000;
  const S_IFLNK = 0120000;
  const S_IFNWK = 0110000;
  const S_IFREG = 0100000;
  const S_IFMPB = 0070000;
  const S_IFBLK = 0060000;
  const S_IFNAM = 0050000;
  const S_IFDIR = 0040000;
  const S_IFMPC = 0030000;
  const S_IFCHR = 0020000;
  const S_IFIFO = 0010000;
  const S_ISUID = 0004000;
  const S_ISGID = 0002000;
  const S_ISVTX = 0001000;
  function S_ISLNK($m) {
    return ($m & S_IFMT) == S_IFLNK;
  }
  function S_ISREG($m) {
    return ($m & S_IFMT) == S_IFREG;
  }
  function S_ISDIR($m) {
    return ($m & S_IFMT) == S_IFDIR;
  }
  function S_ISCHR($m) {
    return ($m & S_IFMT) == S_IFCHR;
  }
  function S_ISBLK($m) {
    return ($m & S_IFMT) == S_IFBLK;
  }
  function S_ISFIFO($m) {
    return ($m & S_IFMT) == S_IFIFO;
  }
  function S_ISSOCK($m) {
    return ($m & S_IFMT) == S_IFSOCK;
  }
  const S_IRWXU = 00700;
  const S_IRUSR = 00400;
  const S_IWUSR = 00200;
  const S_IXUSR = 00100;
  const S_IRWXG = 00070;
  const S_IRGRP = 00040;
  const S_IWGRP = 00020;
  const S_IXGRP = 00010;
  const S_IRWXO = 00007;
  const S_IROTH = 00004;
  const S_IWOTH = 00002;
  const S_IXOTH = 00001;
  abstract class Stat {
    public abstract function mtime();
    public abstract function atime();
    public abstract function ctime();
    public abstract function size();
    public abstract function mode();
    public abstract function uid();
    public abstract function gid();
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
      return $this->stat["mtime"];
    }
    public function atime() {
      return $this->stat["atime"];
    }
    public function ctime() {
      return $this->stat["ctime"];
    }
    public function size() {
      return $this->stat["size"];
    }
    public function mode() {
      return $this->stat["mode"];
    }
    public function uid() {
      return $this->stat["uid"];
    }
    public function gid() {
      return $this->stat["gid"];
    }
    public function toArray() {
      return $this->stat;
    }
  }
  function symbolic_mode($mode) {
    $s = "";
    $type = $mode & S_IFMT;
    if ($type == S_IFWHT) {
      $s .= "w";
    } else {
      if ($type == S_IFDOOR) {
        $s .= "D";
      } else {
        if ($type == S_IFSOCK) {
          $s .= "s";
        } else {
          if ($type == S_IFLNK) {
            $s .= "l";
          } else {
            if ($type == S_IFNWK) {
              $s .= "n";
            } else {
              if ($type == S_IFREG) {
                $s .= "-";
              } else {
                if ($type == S_IFBLK) {
                  $s .= "b";
                } else {
                  if ($type == S_IFDIR) {
                    $s .= "d";
                  } else {
                    if ($type == S_IFCHR) {
                      $s .= "c";
                    } else {
                      if ($type == S_IFIFO) {
                        $s .= "p";
                      } else {
                        $s .= "?";
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    $s .= ($mode & S_IRUSR) ? "r" : "-";
    $s .= ($mode & S_IWUSR) ? "w" : "-";
    if ($mode & S_ISUID) {
      $s .= ($mode & S_IXUSR) ? "s" : "S";
    } else {
      $s .= ($mode & S_IXUSR) ? "x" : "-";
    }
    $s .= ($mode & S_IRGRP) ? "r" : "-";
    $s .= ($mode & S_IWGRP) ? "w" : "-";
    if ($mode & S_ISGID) {
      $s .= ($mode & S_IXGRP) ? "s" : "S";
    } else {
      $s .= ($mode & S_IXGRP) ? "x" : "-";
    }
    $s .= ($mode & S_IROTH) ? "r" : "-";
    $s .= ($mode & S_IWOTH) ? "w" : "-";
    if ($mode & S_ISVTX) {
      $s .= ($mode & S_IXOTH) ? "t" : "T";
    } else {
      $s .= ($mode & S_IXOTH) ? "x" : "-";
    }
    return $s;
  }
  function new_stat($fileSize = 0) {
    return array(
      "dev" => 0,
      "ino" => 0,
      "mode" => 0666 | S_IFREG,
      "nlink" => 1,
      "uid" => 0,
      "gid" => 0,
      "rdev" => -1,
      "size" => $fileSize,
      "atime" => 0,
      "mtime" => 0,
      "ctime" => 0,
      "blksize" => -1,
      "blocks" => -1
    );
  }
}
