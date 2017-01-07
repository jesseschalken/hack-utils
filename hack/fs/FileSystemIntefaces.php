<?hh // strict

namespace HackUtils;

interface FileSystemPathInterface {
  /**
   * Split a path into two at the given offset into the list of path
   * components. The first path has the same root as the input path and the
   * second path is always relative. Negative offsets are supported.
   *
   * Example:
   * list($root, $path) = $fs->split($path, 0);
   * list($path, $basename) = $fs->split($path, -1);
   */
  public function split(string $path, int $i): (string, string);

  /**
   * Join two paths together. If the second path is absolute, it is returned.
   */
  public function join(string $path1, string $path2): string;
}

interface FileSystemInterface extends FileSystemPathInterface {
  public function mkdir(string $path, int $mode = 0777): void;
  public function readdir(string $path): array<string>;
  public function rmdir(string $path): void;

  public function open(string $path, string $mode): StreamInterface;
  public function rename(string $oldpath, string $newpath): void;
  public function unlink(string $path): void;

  /** Must throw a StatFailed in the case of failure. */
  public function stat(string $path): StatInterface;
  /** Must throw a StatFailed in the case of failure. */
  public function lstat(string $path): StatInterface;

  public function trystat(string $path): ?StatInterface;
  public function trylstat(string $path): ?StatInterface;

  public function chmod(string $path, int $mode): void;
  public function chown(string $path, int $uid): void;
  public function chgrp(string $path, int $gid): void;
  public function utime(string $path, int $atime, int $mtime): void;
}

interface SymlinkFileSystemInterface extends FileSystemInterface {
  public function symlink(string $path, string $target): void;
  public function readlink(string $path): string;
  public function realpath(string $path): string;
  public function lchown(string $path, int $uid): void;
  public function lchgrp(string $path, int $gid): void;
}

interface StreamWrapperInterface extends FileSystemPathInterface {
  public function wrapPath(string $path): string;
  public function getContext(): resource;
}

interface StreamInterface {
  public function truncate(int $len): void;
  public function tell(): int;
  public function eof(): bool;
  public function seek(int $offset, int $origin = \SEEK_SET): void;
  public function read(int $length): string;
  public function getContents(): string;
  public function write(string $data): int;
  public function close(): void;
  public function stat(): StatInterface;
  public function lock(int $flags): bool;
  public function flush(): void;
  public function setbuf(int $size): void;
  public function rewind(): void;
}

interface StatInterface {
  public function mode(): int;
  public function uid(): int;
  public function gid(): int;
  public function size(): int;
  public function atime(): int;
  public function mtime(): int;
  public function ctime(): int;

  // Leaving these out for API simplicity.
  // public function dev(): int;
  // public function ino(): int;
  // public function nlink(): int;
  // public function rdev(): int;
  // public function blksize(): int;
  // public function blocks(): int;
}

class StatFailed extends Exception {}
