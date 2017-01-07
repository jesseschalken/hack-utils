<?hh // strict

namespace HackUtils;

abstract class Stream implements \Psr\Http\Message\StreamInterface {

  public abstract function truncate(int $len): void;
  public abstract function tell(): int;
  public abstract function eof(): bool;
  public abstract function seek(int $offset, int $origin = \SEEK_SET): void;
  public abstract function read(int $length): string;
  public abstract function write(string $data): int;
  public abstract function stat(): Stat;
  public abstract function lock(int $flags): bool;
  public abstract function setbuf(int $size): void;

  public function flush(): void {}
  public function close(): void {}
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
