<?hh // strict

namespace HackUtils;

// From https://en.wikibooks.org/wiki/C_Programming/POSIX_Reference/sys/stat.h
const int S_IFMT = 00170000;
const int S_IFWHT = 0160000;
const int S_IFDOOR = 0150000;
const int S_IFSOCK = 0140000;
const int S_IFSHAD = 0130000;
const int S_IFLNK = 0120000;
const int S_IFNWK = 0110000;
const int S_IFREG = 0100000;
const int S_IFMPB = 0070000;
const int S_IFBLK = 0060000;
const int S_IFNAM = 0050000;
const int S_IFDIR = 0040000;
const int S_IFMPC = 0030000;
const int S_IFCHR = 0020000;
const int S_IFIFO = 0010000;

const int S_ISUID = 0004000;
const int S_ISGID = 0002000;
const int S_ISVTX = 0001000;

function S_ISLNK(int $m): bool {
  return ($m & S_IFMT) == S_IFLNK;
}
function S_ISREG(int $m): bool {
  return ($m & S_IFMT) == S_IFREG;
}
function S_ISDIR(int $m): bool {
  return ($m & S_IFMT) == S_IFDIR;
}
function S_ISCHR(int $m): bool {
  return ($m & S_IFMT) == S_IFCHR;
}
function S_ISBLK(int $m): bool {
  return ($m & S_IFMT) == S_IFBLK;
}
function S_ISFIFO(int $m): bool {
  return ($m & S_IFMT) == S_IFIFO;
}
function S_ISSOCK(int $m): bool {
  return ($m & S_IFMT) == S_IFSOCK;
}

const int S_IRWXU = 00700;
const int S_IRUSR = 00400;
const int S_IWUSR = 00200;
const int S_IXUSR = 00100;

const int S_IRWXG = 00070;
const int S_IRGRP = 00040;
const int S_IWGRP = 00020;
const int S_IXGRP = 00010;

const int S_IRWXO = 00007;
const int S_IROTH = 00004;
const int S_IWOTH = 00002;
const int S_IXOTH = 00001;

abstract class Stat implements StatInterface {
  public final function isFile(): bool {
    return S_ISREG($this->mode());
  }
  public final function isDir(): bool {
    return S_ISDIR($this->mode());
  }
  public final function isLink(): bool {
    return S_ISLNK($this->mode());
  }
  public final function isSocket(): bool {
    return S_ISSOCK($this->mode());
  }
  public final function isPipe(): bool {
    return S_ISFIFO($this->mode());
  }
  public final function isChar(): bool {
    return S_ISCHR($this->mode());
  }
  public final function isBlock(): bool {
    return S_ISBLK($this->mode());
  }
  public final function modeSymbolic(): string {
    return symbolic_mode($this->mode());
  }
  public final function modeOctal(): string {
    return pad_left(\decoct($this->mode() & 07777), 4, '0');
  }
}

final class ArrayStat extends Stat {
  public function __construct(private stat_array $stat) {}
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
  public function uid(): int {
    return $this->stat['uid'];
  }
  public function gid(): int {
    return $this->stat['gid'];
  }
  public function toArray(): stat_array {
    return $this->stat;
  }
}

function symbolic_mode(int $mode): string {
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
