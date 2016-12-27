<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class Stream implements \Psr\Http\Message\StreamInterface {
    protected function __construct() {}
    public function __destruct() {}
    public abstract function truncate($len);
    public abstract function tell();
    public abstract function eof();
    public abstract function seek($offset, $whence = \SEEK_SET);
    public abstract function read($length);
    public abstract function write($data);
    public abstract function close();
    public abstract function stat();
    public abstract function lock($flags);
    public abstract function flush();
    public abstract function setbuf($size);
    public function getContents() {
      $ret = "";
      while (!$this->eof()) {
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
    public function isSeekable() {
      return true;
    }
  }
  final class FOpenStream extends Stream {
    private $handle;
    public function __construct($url, $mode, $ctx = NULL_RESOURCE) {
      parent::__construct();
      $this->handle =
        ErrorAssert::isResource("fopen", \fopen($url, $mode, false, $ctx));
    }
    public function read($length) {
      return ErrorAssert::isString("fread", \fread($this->handle, $length));
    }
    public function write($data) {
      return ErrorAssert::isInt("fwrite", \fwrite($this->handle, $data));
    }
    public function eof() {
      return ErrorAssert::isBool("feof", \feof($this->handle));
    }
    public function seek($offset, $whence = \SEEK_SET) {
      ErrorAssert::isZero("fseek", \fseek($this->handle, $offset, $whence));
    }
    public function tell() {
      return ErrorAssert::isInt("ftell", \ftell($this->handle));
    }
    public function close() {
      ErrorAssert::isTrue("fclose", \fclose($this->handle));
    }
    public function flush() {
      ErrorAssert::isTrue("fflush", \fflush($this->handle));
    }
    public function lock($flags) {
      $wb = false;
      $ret = \flock($this->handle, $flags, $wb);
      if ($wb) {
        return false;
      }
      ErrorAssert::isTrue("flock", $ret);
      return true;
    }
    public function rewind() {
      ErrorAssert::isTrue("rewind", \rewind($this->handle));
    }
    public function truncate($length) {
      ErrorAssert::isTrue("ftruncate", \ftruncate($this->handle, $length));
    }
    public function stat() {
      return
        new ArrayStat(ErrorAssert::isArray("fstat", \fstat($this->handle)));
    }
    public function setbuf($size) {
      ErrorAssert::isZero(
        "stream_set_write_buffer",
        \stream_set_write_buffer($this->handle, $size)
      );
    }
    public function getContents() {
      return ErrorAssert::isString(
        "stream_get_contents",
        \stream_get_contents($this->handle)
      );
    }
    public function isReadable() {
      $mode = $this->getMode();
      return \strstr($mode, "r") || \strstr($mode, "+");
    }
    public function isWritable() {
      $mode = $this->getMode();
      return
        \strstr($mode, "x") ||
        \strstr($mode, "w") ||
        \strstr($mode, "c") ||
        \strstr($mode, "a") ||
        \strstr($mode, "+");
    }
    public function isSeekable() {
      $ret = $this->getMetadata_();
      return $ret["seekable"];
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
      return ErrorAssert::isArray(
        "stream_get_meta_data",
        \stream_get_meta_data($this->handle)
      );
    }
    private function getMode() {
      $meta = $this->getMetadata_();
      return $meta["mode"];
    }
  }
}
