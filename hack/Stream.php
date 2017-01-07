<?hh // strict

namespace HackUtils;

abstract class Stream
  implements \Psr\Http\Message\StreamInterface, StreamInterface {
  public abstract function stat(): Stat;
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
}

final class FOpenStream extends Stream {
  private ?resource $handle;
  public function __construct(
    string $url,
    string $mode,
    ?resource $ctx = NULL_RESOURCE,
  ) {
    FileSystemException::prepare();
    $this->handle =
      FileSystemException::assertResource(\fopen($url, $mode, false, $ctx));
  }
  public function read(int $length): string {
    FileSystemException::prepare();
    return FileSystemException::assertString(\fread($this->handle, $length));
  }
  public function write(string $data): int {
    FileSystemException::prepare();
    return FileSystemException::assertInt(\fwrite($this->handle, $data));
  }
  public function eof(): bool {
    FileSystemException::prepare();
    return FileSystemException::assertBool(\feof($this->handle));
  }
  public function seek(int $offset, int $whence = \SEEK_SET): void {
    FileSystemException::prepare();
    FileSystemException::assertZero(\fseek($this->handle, $offset, $whence));
  }
  public function tell(): int {
    FileSystemException::prepare();
    return FileSystemException::assertInt(\ftell($this->handle));
  }
  public function close(): void {
    FileSystemException::prepare();
    FileSystemException::assertTrue(\fclose($this->handle));
  }
  public function flush(): void {
    FileSystemException::prepare();
    FileSystemException::assertTrue(\fflush($this->handle));
  }
  public function lock(int $flags): bool {
    $wb = false;
    $ret = \flock($this->handle, $flags, $wb);
    // An EWOULDBLOCK should quietly return false
    if ($wb)
      return false;
    FileSystemException::prepare();
    FileSystemException::assertTrue($ret);
    return true;
  }
  public function rewind(): void {
    FileSystemException::prepare();
    FileSystemException::assertTrue(\rewind($this->handle));
  }
  public function truncate(int $length): void {
    FileSystemException::prepare();
    FileSystemException::assertTrue(\ftruncate($this->handle, $length));
  }
  public function stat(): Stat {
    FileSystemException::prepare();
    return
      new ArrayStat(FileSystemException::assertArray(\fstat($this->handle)));
  }
  public function setbuf(int $size): void {
    FileSystemException::prepare();
    FileSystemException::assertZero(
      \stream_set_write_buffer($this->handle, $size),
    );
  }
  public function getContents(): string {
    FileSystemException::prepare();
    return
      FileSystemException::assertString(\stream_get_contents($this->handle));
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
    FileSystemException::prepare();
    return
      FileSystemException::assertArray(\stream_get_meta_data($this->handle));
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
