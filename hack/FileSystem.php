<?hh // strict

namespace HackUtils\FS;

abstract class FileSystem {
  public abstract function mkdir(string $path, int $mode = 0777): void;
  public abstract function readdir(string $path): array<string>;
  public abstract function rmdir(string $path): void;

  public abstract function rename(string $oldpath, string $newpath): void;
  public abstract function unlink(string $path): void;

  public abstract function stat(string $path): Stat;
  public abstract function chmod(string $path, int $mode): void;
  public abstract function chown(string $path, int $uid): void;
  public abstract function chgrp(string $path, int $gid): void;

  public abstract function utime(string $path, int $atime, int $mtime): void;
  public abstract function open(string $path, string $mode): Stream;

  public abstract function symlink(string $path, string $contents): void;
  public abstract function readlink(string $path): string;

  public abstract function lstat(string $path): Stat;
  public abstract function lchown(string $path, int $uid): void;
  public abstract function lchgrp(string $path, int $gid): void;

  public abstract function realpath(string $path): string;

  public abstract function join(string $path, string $child): string;
  public abstract function split(string $path): (string, string);
}

class Exception extends \Exception {}

abstract class Stream implements \Psr\Http\Message\StreamInterface {
  // public abstract function chmod(int $mode): void;
  // public abstract function chown(int $uid, int $gid): void;
  public abstract function truncate(int $len): void;
  public abstract function lock(int $flags): bool;
  public abstract function tell(): int;
  public abstract function eof(): bool;
  public abstract function seek(int $offset, int $whence = \SEEK_SET): void;
  public abstract function read(int $length): string;
  public abstract function write(string $data): int;
  public abstract function close(): void;
  public final function __toString(): string {
    return $this->read(\PHP_INT_MAX);
  }
  public final function getContents(): string {
    return $this->read(\PHP_INT_MAX);
  }
  public function detach(): ?resource {
    return null;
  }
}

final class Stat {
  public static function fromArray(stat_array $stat): Stat {
    return new self($stat);
  }

  private function __construct(private stat_array $stat) {}

  public function mtime(): int {
    return $this->stat['mtime'];
  }

  public function atime(): int {
    return $this->stat['atime'];
  }

  public function ctime(): int {
    return $this->stat['ctime'];
  }

  public function size(): int {
    return $this->stat['size'];
  }

  public function mode(): int {
    return $this->stat['mode'];
  }

  public function toArray(): stat_array {
    return $this->stat;
  }

  public function isFile(): bool {
    return S_ISREG($this->mode());
  }

  public function isDir(): bool {
    return S_ISDIR($this->mode());
  }

  public function isLink(): bool {
    return S_ISLNK($this->mode());
  }

  public function isSocket(): bool {
    return S_ISSOCK($this->mode());
  }

  public function isFIFO(): bool {
    return S_ISSOCK($this->mode());
  }

  public function isChar(): bool {
    return S_ISSOCK($this->mode());
  }

  public function isBlock(): bool {
    return S_ISSOCK($this->mode());
  }
}

function format_mode(int $mode): string {
  $s = '';

  $type = $mode & S_IFMT;
  if ($type == S_IFWHT)
    $s .= 'w'; else if ($type == S_IFDOOR)
    $s .= 'D'; else if ($type == S_IFSOCK)
    $s .= 's'; else if ($type == S_IFLNK)
    $s .= 'l'; else if ($type == S_IFNWK)
    $s .= 'n'; else if ($type == S_IFREG)
    $s .= '-'; else if ($type == S_IFBLK)
    $s .= 'b'; else if ($type == S_IFDIR)
    $s .= 'd'; else if ($type == S_IFCHR)
    $s .= 'c'; else if ($type == S_IFIFO)
    $s .= 'p'; else
    $s .= '?';

  $s .= $mode & S_IRUSR ? 'r' : '-';
  $s .= $mode & S_IWUSR ? 'w' : '-';
  if ($mode & S_ISUID)
    $s .= $mode & S_IXUSR ? 's' : 'S'; else
    $s .= $mode & S_IXUSR ? 'x' : '-';

  $s .= $mode & S_IRGRP ? 'r' : '-';
  $s .= $mode & S_IWGRP ? 'w' : '-';
  if ($mode & S_ISGID)
    $s .= $mode & S_IXGRP ? 's' : 'S'; else
    $s .= $mode & S_IXGRP ? 'x' : '-';

  $s .= $mode & S_IROTH ? 'r' : '-';
  $s .= $mode & S_IWOTH ? 'w' : '-';
  if ($mode & S_ISVTX)
    $s .= $mode & S_IXOTH ? 't' : 'T'; else
    $s .= $mode & S_IXOTH ? 'x' : '-';

  return $s;
}

type stat_array = shape(
  'dev' => int,
  'ino' => int,
  'mode' => int,
  'nlink' => int,
  'uid' => int,
  'gid' => int,
  'rdev' => int,
  'size' => int,
  'atime' => int,
  'mtime' => int,
  'ctime' => int,
  'blksize' => int,
  'blocks' => int,
);

class MixedFileSystem extends FileSystem {
  public function open(string $path, string $mode): Stream {
    return new MixedStream($path, $mode);
  }
  public function symlink(string $path, string $target): void {
    notfalse(\symlink($target, $path), 'symlink');
  }
  public function stat(string $path): Stat {
    return Stat::fromArray(notfalse(\stat($path), 'stat'));
  }
  public function readlink(string $path): string {
    return notfalse(\readlink($path), 'readlink');
  }
  public function rename(string $from, string $to): void {
    notfalse(\rename($from, $to), 'rename');
  }
  public function readdir(string $path): array<string> {
    return notfalse(\scandir($path), 'scandir');
  }
  public function mkdir(string $path, int $mode = 0777): void {
    notfalse(\mkdir($path, $mode), 'mkdir');
  }
  public function unlink(string $path): void {
    notfalse(\unlink($path), 'unlink');
  }
  public function realpath(string $path): string {
    return notfalse(\realpath($path), 'realpath');
  }
  public function lstat(string $path): Stat {
    return Stat::fromArray(notfalse(\lstat($path), 'lstat'));
  }
  public function rmdir(string $path): void {
    notfalse(\rmdir($path), 'rmdir');
  }
  public function chmod(string $path, int $mode): void {
    notfalse(\chmod($path, $mode), 'chmod');
  }
  public function chown(string $path, int $uid): void {
    notfalse(\chown($path, (int) $uid), 'chown');
  }
  public function chgrp(string $path, int $gid): void {
    notfalse(\chgrp($path, (int) $gid), 'chgrp');
  }
  public function lchown(string $path, int $uid): void {
    notfalse(\lchown($path, (int) $uid), 'lchown');
  }
  public function lchgrp(string $path, int $gid): void {
    notfalse(\lchgrp($path, (int) $gid), 'lchgrp');
  }
  public function utime(string $path, int $atime, int $mtime): void {
    notfalse(\touch($path, $mtime, $atime), 'touch');
  }
  public function join(string $path, string $child): string {
    // TODO
    return '';
  }
  public function split(string $path): (string, string) {
    // TODO
    return tuple('', '');
  }
}

final class MixedStream extends Stream {
  private resource $handle;
  public function __construct(string $path, string $mode) {
    $this->handle = notfalse(\fopen($path, $mode), 'fopen');
  }
  public function read(int $length): string {
    return notfalse(\fread($this->handle, $length), 'fread');
  }
  public function write(string $data): int {
    return notfalse(\fwrite($this->handle, $data), 'fwrite');
  }
  public function eof(): bool {
    return \feof($this->handle);
  }
  public function seek(int $offset, int $whence = \SEEK_SET): void {
    notfalse(\fseek($this->handle, $offset, $whence), 'fseek');
  }
  public function tell(): int {
    return notfalse(\ftell($this->handle), 'ftell');
  }
  public function close(): void {
    notfalse(\fclose($this->handle), 'fclose');
  }
  public function lock(int $flags): bool {
    $wouldblock = false;
    $ret = \flock($this->handle, $flags, $wouldblock);
    if ($wouldblock)
      return false;
    notfalse($ret, 'flock');
    return true;
  }
  public function truncate(int $length): void {
    notfalse(\ftruncate($this->handle, $length), 'ftruncate');
  }
  public function getMetadata(?string $key = null): mixed {
    $metadata =
      notfalse(\stream_get_meta_data($this->handle), 'stream_get_meta_data');
    return $key === null ? $metadata[$key] : $metadata;
  }
}

function notfalse<T>(T $x, string $f): T {
  if ($x === false)
    throw new Exception("$f() failed");
  return $x;
}
