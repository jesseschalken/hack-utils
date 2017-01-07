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
  function symbolic_mode($mode) {
    $s = "";
    $type = $mode & S_IFMT;
    if (\hacklib_equals($type, S_IFWHT)) {
      $s .= "w";
    } else {
      if (\hacklib_equals($type, S_IFDOOR)) {
        $s .= "D";
      } else {
        if (\hacklib_equals($type, S_IFSOCK)) {
          $s .= "s";
        } else {
          if (\hacklib_equals($type, S_IFLNK)) {
            $s .= "l";
          } else {
            if (\hacklib_equals($type, S_IFNWK)) {
              $s .= "n";
            } else {
              if (\hacklib_equals($type, S_IFREG)) {
                $s .= "-";
              } else {
                if (\hacklib_equals($type, S_IFBLK)) {
                  $s .= "b";
                } else {
                  if (\hacklib_equals($type, S_IFDIR)) {
                    $s .= "d";
                  } else {
                    if (\hacklib_equals($type, S_IFCHR)) {
                      $s .= "c";
                    } else {
                      if (\hacklib_equals($type, S_IFIFO)) {
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
