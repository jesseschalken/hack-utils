<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class Stream
    implements \Psr\Http\Message\StreamInterface, StreamInterface {
    public abstract function stat();
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
  final class FOpenStream extends Stream {
    private $handle;
    public function __construct($url, $mode, $ctx = NULL_RESOURCE) {
      $this->handle =
        StrictErrors::start()
          ->finishResource(\fopen($url, $mode, false, $ctx));
    }
    public function read($length) {
      return
        StrictErrors::start()->finishString(\fread($this->handle, $length));
    }
    public function write($data) {
      return StrictErrors::start()->finishInt(\fwrite($this->handle, $data));
    }
    public function eof() {
      return StrictErrors::start()->finishBool(\feof($this->handle));
    }
    public function seek($offset, $whence = \SEEK_SET) {
      StrictErrors::start()
        ->finishZero(\fseek($this->handle, $offset, $whence));
    }
    public function tell() {
      return StrictErrors::start()->finishInt(\ftell($this->handle));
    }
    public function close() {
      StrictErrors::start()->finishTrue(\fclose($this->handle));
    }
    public function flush() {
      StrictErrors::start()->finishTrue(\fflush($this->handle));
    }
    public function lock($flags) {
      $wb = false;
      $ret =
        StrictErrors::start()->finishBool(\flock($this->handle, $flags, $wb));
      if (\hacklib_cast_as_boolean($wb)) {
        return false;
      }
      Exception::assertTrue($ret);
      return true;
    }
    public function rewind() {
      StrictErrors::start()->finishTrue(\rewind($this->handle));
    }
    public function truncate($length) {
      StrictErrors::start()->finishTrue(\ftruncate($this->handle, $length));
    }
    public function stat() {
      return new ArrayStat(
        StrictErrors::start()->finishArray(\fstat($this->handle))
      );
    }
    public function setbuf($size) {
      StrictErrors::start()
        ->finishZero(\stream_set_write_buffer($this->handle, $size));
    }
    public function getContents() {
      return
        StrictErrors::start()
          ->finishString(\stream_get_contents($this->handle));
    }
    public function isReadable() {
      $mode = $this->getMode();
      return
        \hacklib_cast_as_boolean(\strstr($mode, "r")) ||
        \hacklib_cast_as_boolean(\strstr($mode, "+"));
    }
    public function isWritable() {
      $mode = $this->getMode();
      return
        \hacklib_cast_as_boolean(\strstr($mode, "x")) ||
        \hacklib_cast_as_boolean(\strstr($mode, "w")) ||
        \hacklib_cast_as_boolean(\strstr($mode, "c")) ||
        \hacklib_cast_as_boolean(\strstr($mode, "a")) ||
        \hacklib_cast_as_boolean(\strstr($mode, "+"));
    }
    public function isSeekable() {
      $ret = $this->getMetadata_();
      return $ret[\hacklib_id("seekable")];
    }
    public function getMetadata($key = null) {
      $ret = $this->getMetadata_();
      if ($key === null) {
        return $ret;
      }
      return $ret[$key];
    }
    public function detach() {
      $handle = $this->handle;
      $this->handle = NULL_RESOURCE;
      return $handle;
    }
    private function getMetadata_() {
      return
        StrictErrors::start()
          ->finishArray(\stream_get_meta_data($this->handle));
    }
    private function getMode() {
      $meta = $this->getMetadata_();
      return $meta[\hacklib_id("mode")];
    }
  }
}
