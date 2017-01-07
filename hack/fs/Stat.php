<?hh // strict

namespace HackUtils;

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

abstract class Stat {
  public abstract function mode(): int;
  public abstract function uid(): int;
  public abstract function gid(): int;
  public abstract function size(): int;
  public abstract function atime(): int;
  public abstract function mtime(): int;
  public abstract function ctime(): int;

  // Leaving these out for API simplicity.
  // public function dev(): int;
  // public function ino(): int;
  // public function nlink(): int;
  // public function rdev(): int;
  // public function blksize(): int;
  // public function blocks(): int;

  public function toArray(): stat_array {
    return shape(
      'dev' => 0,
      'ino' => 0,
      'mode' => $this->mode(),
      'nlink' => 1,
      'uid' => $this->uid(),
      'gid' => $this->gid(),
      'rdev' => -1,
      'size' => $this->size(),
      'atime' => $this->atime(),
      'mtime' => $this->mtime(),
      'ctime' => $this->ctime(),
      'blksize' => -1,
      'blocks' => -1,
    );
  }

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
