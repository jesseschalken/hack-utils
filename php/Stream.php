<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class Stream
    implements \Psr\Http\Message\StreamInterface, StreamInterface {
    public abstract function stat();
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
  }
  final class FOpenStream extends Stream {
    private $handle;
    public function __construct($url, $mode, $ctx = NULL_RESOURCE) {
      FileSystemException::prepare();
      $this->handle =
        FileSystemException::assertResource(\fopen($url, $mode, false, $ctx));
    }
    public function read($length) {
      FileSystemException::prepare();
      return
        FileSystemException::assertString(\fread($this->handle, $length));
    }
    public function write($data) {
      FileSystemException::prepare();
      return FileSystemException::assertInt(\fwrite($this->handle, $data));
    }
    public function eof() {
      FileSystemException::prepare();
      return FileSystemException::assertBool(\feof($this->handle));
    }
    public function seek($offset, $whence = \SEEK_SET) {
      FileSystemException::prepare();
      FileSystemException::assertZero(
        \fseek($this->handle, $offset, $whence)
      );
    }
    public function tell() {
      FileSystemException::prepare();
      return FileSystemException::assertInt(\ftell($this->handle));
    }
    public function close() {
      FileSystemException::prepare();
      FileSystemException::assertTrue(\fclose($this->handle));
    }
    public function flush() {
      FileSystemException::prepare();
      FileSystemException::assertTrue(\fflush($this->handle));
    }
    public function lock($flags) {
      $wb = false;
      $ret = \flock($this->handle, $flags, $wb);
      if ($wb) {
        return false;
      }
      FileSystemException::prepare();
      FileSystemException::assertTrue($ret);
      return true;
    }
    public function rewind() {
      FileSystemException::prepare();
      FileSystemException::assertTrue(\rewind($this->handle));
    }
    public function truncate($length) {
      FileSystemException::prepare();
      FileSystemException::assertTrue(\ftruncate($this->handle, $length));
    }
    public function stat() {
      FileSystemException::prepare();
      return new ArrayStat(
        FileSystemException::assertArray(\fstat($this->handle))
      );
    }
    public function setbuf($size) {
      FileSystemException::prepare();
      FileSystemException::assertZero(
        \stream_set_write_buffer($this->handle, $size)
      );
    }
    public function getContents() {
      FileSystemException::prepare();
      return FileSystemException::assertString(
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
      FileSystemException::prepare();
      return FileSystemException::assertArray(
        \stream_get_meta_data($this->handle)
      );
    }
    private function getMode() {
      $meta = $this->getMetadata_();
      return $meta["mode"];
    }
  }
}
