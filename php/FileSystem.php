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
    public abstract function join($path, $child);
    public abstract function split($path);
  }
  class Exception extends \Exception {}
  abstract class Stream {
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
  class MixedFileSystem extends FileSystem {
    public function open($path, $mode) {
      return new MixedStream($path, $mode);
    }
    public function symlink($path, $target) {
      notfalse(\symlink($target, $path), "symlink");
    }
    public function stat($path) {
      return Stat::fromArray(notfalse(\stat($path), "stat"));
    }
    public function readlink($path) {
      return notfalse(\readlink($path), "readlink");
    }
    public function rename($from, $to) {
      notfalse(\rename($from, $to), "rename");
    }
    public function readdir($path) {
      return notfalse(\scandir($path), "scandir");
    }
    public function mkdir($path, $mode = 0777) {
      notfalse(\mkdir($path, $mode), "mkdir");
    }
    public function unlink($path) {
      notfalse(\unlink($path), "unlink");
    }
    public function realpath($path) {
      return notfalse(\realpath($path), "realpath");
    }
    public function lstat($path) {
      return Stat::fromArray(notfalse(\lstat($path), "lstat"));
    }
    public function rmdir($path) {
      notfalse(\rmdir($path), "rmdir");
    }
    public function chmod($path, $mode) {
      notfalse(\chmod($path, $mode), "chmod");
    }
    public function chown($path, $uid) {
      notfalse(\chown($path, (int) $uid), "chown");
    }
    public function chgrp($path, $gid) {
      notfalse(\chgrp($path, (int) $gid), "chgrp");
    }
    public function lchown($path, $uid) {
      notfalse(\lchown($path, (int) $uid), "lchown");
    }
    public function lchgrp($path, $gid) {
      notfalse(\lchgrp($path, (int) $gid), "lchgrp");
    }
    public function utime($path, $atime, $mtime) {
      notfalse(\touch($path, $mtime, $atime), "touch");
    }
    public function join($path, $child) {
      return "";
    }
    public function split($path) {
      return array("", "");
    }
  }
  final class MixedStream extends Stream {
    private $handle;
    public function __construct($path, $mode) {
      $this->handle = notfalse(\fopen($path, $mode), "fopen");
    }
    public function read($length) {
      return notfalse(\fread($this->handle, $length), "fread");
    }
    public function write($data) {
      return notfalse(\fwrite($this->handle, $data), "fwrite");
    }
    public function eof() {
      return \feof($this->handle);
    }
    public function seek($offset, $whence = \SEEK_SET) {
      notfalse(\fseek($this->handle, $offset, $whence), "fseek");
    }
    public function tell() {
      return notfalse(\ftell($this->handle), "ftell");
    }
    public function lock($flags) {
      $wouldblock = false;
      $ret = \flock($this->handle, $flags, $wouldblock);
      if ($wouldblock) {
        return false;
      }
      notfalse($ret, "flock");
      return true;
    }
    public function truncate($length) {
      notfalse(\ftruncate($this->handle, $length), "ftruncate");
    }
  }
  function notfalse($x, $f) {
    if ($x === false) {
      throw new Exception($f."() failed");
    }
    return $x;
  }
}
