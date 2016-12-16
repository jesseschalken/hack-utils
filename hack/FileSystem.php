<?hh // strict

namespace HackUtils\FS;

use HackUtils as HU;

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

  /** Split a path into dirname and basename */
  public abstract function split(string $path): (string, string);
  /** Join a dirname and basename together */
  public abstract function join(string $path, string $child): string;
}

class Exception extends \Exception {}

abstract class Stream implements \Psr\Http\Message\StreamInterface {
  private ?array<arraykey, (function(stream_metadata): mixed)> $getters;
  public function __construct() {}
  // public abstract function chmod(int $mode): void;
  // public abstract function chown(int $uid, int $gid): void;
  public abstract function truncate(int $len): void;
  public abstract function lock(int $flags): bool;
  public abstract function tell(): int;
  public abstract function eof(): bool;
  public abstract function seek(int $offset, int $whence = \SEEK_SET): void;
  public abstract function read(int $length): string;
  public abstract function getContents(): string;
  public abstract function write(string $data): int;
  public abstract function close(): void;
  public abstract function stat(): Stat;
  public abstract function getMetadata_(): stream_metadata;
  public final function isReadable(): bool {
    $metadata = $this->getMetadata_();
    $mode = $metadata['mode'];
    return \strstr($mode, 'r') || \strstr($mode, '+');
  }
  public final function isSeekable(): bool {
    $metadata = $this->getMetadata_();
    return $metadata['seekable'];
  }
  public final function isWritable(): bool {
    $metadata = $this->getMetadata_();
    $mode = $metadata['mode'];
    return
      \strstr($mode, 'x') ||
      \strstr($mode, 'w') ||
      \strstr($mode, 'c') ||
      \strstr($mode, 'a') ||
      \strstr($mode, '+');
  }
  public final function rewind(): void {
    $this->seek(0);
  }
  public final function __toString(): string {
    $this->rewind();
    return $this->getContents();
  }
  public final function getSize(): int {
    return $this->stat()->size();
  }
  public final function getMetadata(?string $key = null): mixed {
    $metadata = $this->getMetadata_();
    if ($key === null)
      return $metadata;
    if ($this->getters === null)
      $this->getters = [
        'timed_out' => $m ==> $m['timed_out'],
        'blocked' => $m ==> $m['blocked'],
        'eof' => $m ==> $m['eof'],
        'unread_bytes' => $m ==> $m['unread_bytes'],
        'stream_type' => $m ==> $m['stream_type'],
        'wrapper_type' => $m ==> $m['wrapper_type'],
        'wrapper_data' => $m ==> $m['wrapper_data'],
        'mode' => $m ==> $m['mode'],
        'seekable' => $m ==> $m['seekable'],
        'uri' => $m ==> $m['uri'],
      ];
    return $this->getters[$key]($metadata);
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

type stream_metadata = shape(
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

class MixedFileSystem extends FileSystem {
  public function open(string $path, string $mode): Stream {
    return new MixedStream($path, $mode);
  }
  public function symlink(string $path, string $target): void {
    notfalse(\symlink($target, $path), 'symlink');
  }
  public function stat(string $path): Stat {
    \clearstatcache();
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
    \clearstatcache();
    return notfalse(\realpath($path), 'realpath');
  }
  public function lstat(string $path): Stat {
    \clearstatcache();
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
    if (HU\ends_with($path, '/') || HU\starts_with($child, '/'))
      return $path.$child;
    $sep = \DIRECTORY_SEPARATOR;
    if ($sep === '\\') {
      if (HU\ends_with($path, '\\') || HU\starts_with($child, '\\'))
        return $path.$child;
    }
    return $path.$sep.$child;
  }
  public function split(string $path): (string, string) {
    $sep = \DIRECTORY_SEPARATOR;
    // TODO
    return tuple('', '');
  }
}

final class MixedStream extends Stream {
  private resource $handle;
  public function __construct(string $path, string $mode) {
    parent::__construct();
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
  public function stat(): Stat {
    return Stat::fromArray(notfalse(\fstat($this->handle), 'fstat'));
  }
  public function getContents(): string {
    return
      notfalse(\stream_get_contents($this->handle), 'stream_get_contents');
  }
  public function getMetadata_(): stream_metadata {
    return
      notfalse(\stream_get_meta_data($this->handle), 'stream_get_meta_data');
  }
}

function notfalse<T>(T $x, string $f): T {
  if ($x === false)
    throw new Exception("$f() failed");
  return $x;
}
