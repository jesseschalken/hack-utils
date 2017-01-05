<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  interface FileSystemPathInterface {
    public function split($path, $i);
    public function join($path1, $path2);
  }
  interface FileSystemInterface extends FileSystemPathInterface {
    public function mkdir($path, $mode = 0777);
    public function readdir($path);
    public function rmdir($path);
    public function rename($oldpath, $newpath);
    public function unlink($path);
    public function stat($path);
    public function lstat($path);
    public function trystat($path);
    public function trylstat($path);
    public function chmod($path, $mode);
    public function chown($path, $uid);
    public function chgrp($path, $gid);
    public function utime($path, $atime, $mtime);
    public function open($path, $mode);
  }
  interface SymlinkFileSystemInterface extends FileSystemInterface {
    public function symlink($path, $target);
    public function readlink($path);
    public function realpath($path);
    public function lchown($path, $uid);
    public function lchgrp($path, $gid);
  }
  interface StreamWrapperInterface extends FileSystemPathInterface {
    public function wrapPath($path);
    public function getContext();
  }
  interface StreamInterface {
    public function truncate($len);
    public function tell();
    public function eof();
    public function seek($offset, $origin = \SEEK_SET);
    public function read($length);
    public function getContents();
    public function write($data);
    public function close();
    public function stat();
    public function lock($flags);
    public function flush();
    public function setbuf($size);
    public function rewind();
  }
  interface StatInterface {
    public function mode();
    public function uid();
    public function gid();
    public function size();
    public function atime();
    public function mtime();
    public function ctime();
  }
}
