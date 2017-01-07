<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class Stream implements \Psr\Http\Message\StreamInterface {
    public abstract function truncate($len);
    public abstract function tell();
    public abstract function eof();
    public abstract function seek($offset, $origin = \SEEK_SET);
    public abstract function read($length);
    public abstract function write($data);
    public abstract function stat();
    public abstract function lock($flags);
    public abstract function setbuf($size);
    public function flush() {}
    public function close() {}
    public function getContents() {
      $ret = "";
      while (!\hacklib_cast_as_boolean($this->eof())) {
        $ret .= $this->read(8192);
      }
      return $ret;
    }
    public function rewind() {
      $this->seek(0);
    }
    public function __toString() {
      $this->rewind();
      return $this->getContents();
    }
    public function getSize() {
      return $this->stat()->size();
    }
    public function getMetadata($key = null) {
      throw new \Exception(__METHOD__." is not supported");
    }
    public function detach() {
      return null;
    }
  }
}
