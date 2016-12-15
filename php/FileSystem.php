<?php
namespace HackUtils\FS {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class FileSystem {
    public abstract function mkdir($path, $mode = 0777);
    public abstract function readdir($path);
    public abstract function rmdir($path);
    public abstract function rename($oldpath, $newpath);
    public abstract function unlink($path);
    public abstract function stat($path);
    public abstract function chmod($path, $mode);
    public abstract function chown($path, $uid, $gid);
    public abstract function truncate($path, $len);
    public abstract function utime($path, $atime, $mtime);
    public abstract function open($path, $mode);
    public abstract function symlink($oldpath, $newpath);
    public abstract function readlink($path);
    public abstract function lstat($path);
    public abstract function lchmod($path, $mode);
    public abstract function lchown($path, $uid, $gid);
    public abstract function realpath($path);
    public abstract function join($path);
    public abstract function split($path);
  }
  abstract class Stream {
    public abstract function chmod($mode);
    public abstract function chown($uid, $gid);
    public abstract function truncate($len);
    public abstract function lock($flags);
    public abstract function tell();
    public abstract function eof();
    public abstract function seek($offset, $whence = \SEEK_SET);
    public abstract function read($length);
    public abstract function write($data);
  }
  final class Stat {
    public static function fromArray($stat) {
      return new self($stat);
    }
    private $stat;
    private function __construct($stat) {
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
    public function toArray() {
      return $this->stat;
    }
    public function isFile() {
      return S_ISREG($this->mode());
    }
    public function isDir() {
      return S_ISDIR($this->mode());
    }
    public function isLink() {
      return S_ISLNK($this->mode());
    }
    public function isSocket() {
      return S_ISSOCK($this->mode());
    }
    public function isFIFO() {
      return S_ISSOCK($this->mode());
    }
    public function isChar() {
      return S_ISSOCK($this->mode());
    }
    public function isBlock() {
      return S_ISSOCK($this->mode());
    }
  }
  function format_mode($mode) {
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
}
