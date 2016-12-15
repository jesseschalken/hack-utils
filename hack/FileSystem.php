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
  public abstract function chown(string $path, int $uid, int $gid): void;

  public abstract function truncate(string $path, int $len): void;
  public abstract function utime(string $path, int $atime, int $mtime): void;
  public abstract function open(string $path, string $mode): Stream;

  public abstract function symlink(string $oldpath, string $newpath): void;
  public abstract function readlink(string $path): string;

  public abstract function lstat(string $path): Stat;
  public abstract function lchmod(string $path, int $mode): void;
  public abstract function lchown(string $path, int $uid, int $gid): void;

  public abstract function realpath(string $path): string;

  public abstract function join(array<string> $path): string;
  public abstract function split(string $path): array<string>;
}

abstract class Stream {
  public abstract function chmod(int $mode): void;
  public abstract function chown(int $uid, int $gid): void;
  public abstract function truncate(int $len): void;
  public abstract function lock(int $flags): bool;
  public abstract function tell(): int;
  public abstract function eof(): bool;
  public abstract function seek(int $offset, int $whence = \SEEK_SET): void;
  public abstract function read(int $length): string;
  public abstract function write(string $data): int;
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
