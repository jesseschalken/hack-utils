<?hh // strict

namespace HackUtils;

abstract class Stream implements \Psr\Http\Message\StreamInterface {
  // public abstract function chmod(int $mode): void;
  // public abstract function chown(int $uid, int $gid): void;
  public abstract function truncate(int $len): void;
  public abstract function tell(): int;
  public abstract function eof(): bool;
  public abstract function seek(int $offset, int $whence = \SEEK_SET): void;
  public abstract function read(int $length): string;
  public abstract function write(string $data): int;
  public abstract function close(): void;
  public abstract function stat(): Stat;
  public abstract function lock(int $flags): bool;
  public abstract function flush(): void;
  public abstract function setbuf(int $size): void;
  public function getContents(): string {
    $ret = '';
    while (!$this->eof())
      $ret .= $this->read(8192);
    return $ret;
  }
  public function rewind(): void {
    $this->seek(0);
  }
  public function __toString(): string {
    $this->rewind();
    return $this->getContents();
  }
  public function getSize(): int {
    return $this->stat()->size();
  }
  public function getMetadata(?string $key = null): mixed {
    throw new \Exception(__METHOD__.' is not supported');
  }
  public function detach(): ?resource {
    return null;
  }
  public function isSeekable(): bool {
    return true;
  }
}

final class FOpenStream extends Stream {
  private ?resource $handle;
  public function __construct(
    string $url,
    string $mode,
    ?resource $ctx = NULL_RESOURCE,
  ) {
    parent::__construct();
    $this->handle =
      ErrorAssert::isResource('fopen', \fopen($url, $mode, false, $ctx));
  }
  public function read(int $length): string {
    return ErrorAssert::isString('fread', \fread($this->handle, $length));
  }
  public function write(string $data): int {
    return ErrorAssert::isInt('fwrite', \fwrite($this->handle, $data));
  }
  public function eof(): bool {
    return ErrorAssert::isBool('feof', \feof($this->handle));
  }
  public function seek(int $offset, int $whence = \SEEK_SET): void {
    ErrorAssert::isZero('fseek', \fseek($this->handle, $offset, $whence));
  }
  public function tell(): int {
    return ErrorAssert::isInt('ftell', \ftell($this->handle));
  }
  public function close(): void {
    ErrorAssert::isTrue('fclose', \fclose($this->handle));
  }
  public function flush(): void {
    ErrorAssert::isTrue('fflush', \fflush($this->handle));
  }
  public function lock(int $flags): bool {
    $wb = false;
    $ret = \flock($this->handle, $flags, $wb);
    // An EWOULDBLOCK should quietly return false
    if ($wb)
      return false;
    ErrorAssert::isTrue('flock', $ret);
    return true;
  }
  public function rewind(): void {
    ErrorAssert::isTrue('rewind', \rewind($this->handle));
  }
  public function truncate(int $length): void {
    ErrorAssert::isTrue('ftruncate', \ftruncate($this->handle, $length));
  }
  public function stat(): Stat {
    return
      new ArrayStat(ErrorAssert::isArray('fstat', \fstat($this->handle)));
  }
  public function setbuf(int $size): void {
    ErrorAssert::isZero(
      'stream_set_write_buffer',
      \stream_set_write_buffer($this->handle, $size),
    );
  }
  public function getContents(): string {
    return ErrorAssert::isString(
      \stream_get_contents($this->handle),
      'stream_get_contents',
    );
  }
  public function isReadable(): bool {
    $mode = $this->getMode();
    return \strstr($mode, 'r') || \strstr($mode, '+');
  }
  public function isWritable(): bool {
    $mode = $this->getMode();
    return
      \strstr($mode, 'x') ||
      \strstr($mode, 'w') ||
      \strstr($mode, 'c') ||
      \strstr($mode, 'a') ||
      \strstr($mode, '+');
  }
  public function isSeekable(): bool {
    $ret = $this->getMetadata_();
    return $ret['seekable'];
  }
  public function getMetadata(?string $key = null): mixed {
    $ret = $this->getMetadata_();
    if ($key === null)
      return $ret;
    /* HH_IGNORE_ERROR[4051] */
    return $ret[$key];
  }
  public function detach(): ?resource {
    $handle = $this->handle;
    $this->handle = NULL_RESOURCE;
    return $handle;
  }
  private function getMetadata_(): stream_meta_data {
    return ErrorAssert::isArray(
      'stream_get_meta_data',
      \stream_get_meta_data($this->handle),
    );
  }
  private function getMode(): string {
    $meta = $this->getMetadata_();
    return $meta['mode'];
  }
}

type stream_meta_data = shape(
  'timed_out' => bool,
  'blocked' => bool,
  'eof' => bool,
  'unread_bytes' => int,
  'stream_type' => string,
  'wrapper_type' => string,
  'wrapper_data' => mixed,
  'mode' => string,
  'seekable' => bool,
  'uri' => string,
);
