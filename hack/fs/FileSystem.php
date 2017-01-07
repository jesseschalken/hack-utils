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

interface SymlinkFileSystemInterface {
  require extends FileSystem;

  public function symlink(string $path, string $target): void;
  public function readlink(string $path): string;
  public function realpath(string $path): string;
  public function lchown(string $path, int $uid): void;
  public function lchgrp(string $path, int $gid): void;
}

class StatFailed extends Exception {}

abstract class FileSystem implements FileSystemPathInterface {

  public abstract function mkdir(string $path, int $mode = 0777): void;
  public abstract function readdir(string $path): array<string>;
  public abstract function rmdir(string $path): void;

  public abstract function open(string $path, string $mode): Stream;
  public abstract function rename(string $oldpath, string $newpath): void;
  public abstract function unlink(string $path): void;

  /** Must throw a StatFailed in the case of failure. */
  public abstract function stat(string $path): Stat;
  /** Must throw a StatFailed in the case of failure. */
  public abstract function lstat(string $path): Stat;

  public abstract function trystat(string $path): ?Stat;
  public abstract function trylstat(string $path): ?Stat;

  public abstract function chmod(string $path, int $mode): void;
  public abstract function chown(string $path, int $uid): void;
  public abstract function chgrp(string $path, int $gid): void;
  public abstract function utime(string $path, int $atime, int $mtime): void;

  /**
   * Remvoe a file or directory (unlink/rmdir).
   */
  public final function remove(string $path): void {
    $stat = $this->lstat($path);
    if ($stat && $stat->isDir())
      $this->rmdir($path); else
      $this->unlink($path);
  }

  /**
   * Same as readdir() but returns complete file paths instead of just names.
   * If the given path is relative, the returned paths will also be relative.
   */
  public final function readdirPaths(string $path): array<string> {
    $ret = [];
    foreach ($this->readdir($path) as $p) {
      $ret[] = $this->join($path, $p);
    }
    return $ret;
  }

  public final function readdirPathsRec(string $path): array<string> {
    $ret = [];
    foreach ($this->readdirPaths($path) as $p) {
      $ret[] = $p;
      $stat = $this->stat($p);
      if ($stat && $stat->isDir()) {
        foreach ($this->readdirPathsRec($p) as $p2) {
          $ret[] = $p2;
        }
      }
    }
    return $ret;
  }

  /**
   * All of the non-directory descendants of the given directory, relative to
   * the directory.
   */
  public final function readdirRec(string $path): array<string> {
    $ret = [];
    foreach ($this->readdir($path) as $p) {
      foreach ($this->expandDirs($this->join($path, $p)) as $p2) {
        $ret[] = $this->join($p, $p2);
      }
    }
    return $ret;
  }

  /**
   * Same as readdirRec(), except if the input is not a directory, returns
   * an empty relative path (the empty string).
   * This is useful if your program takes input that can either be a directory
   * (to be scanned recursively) or a single file.
   */
  public final function expandDirs(string $path): array<string> {
    $stat = $this->stat($path);
    if ($stat && $stat->isDir())
      return $this->readdirRec($path);
    return [''];
  }

  public final function removeRec(string $path): int {
    $stat = $this->lstat($path);
    if ($stat && $stat->isDir()) {
      return $this->rmdirRec($path);
    }
    $this->unlink($path);
    return 1;
  }

  public final function rmdirRec(string $path): int {
    $ret = 0;
    foreach ($this->readdirPaths($path) as $p) {
      $ret += $this->removeRec($p);
    }
    $this->rmdir($path);
    $ret++;
    return $ret;
  }

  public final function createDirs(string $path, int $mode = 0777): void {
    list($dir, $child) = $this->split($path, -1);
    if ($child !== '' && !$this->lexists($dir)) {
      $this->mkdirRec($dir, $mode);
    }
  }

  public final function exists(string $path): bool {
    return $this->stat($path) ? true : false;
  }

  public final function lexists(string $path): bool {
    return $this->lstat($path) ? true : false;
  }

  public final function readFile(string $path): string {
    return $this->open($path, 'rb')->getContents();
  }

  public final function writeFile(string $path, string $contents): int {
    return $this->open($path, 'wb')->write($contents);
  }

  public final function appendFile(string $path, string $contents): int {
    return $this->open($path, 'ab')->write($contents);
  }

  public final function writeFileRec(string $path, string $contents): int {
    $this->createDirs($path);
    return $this->writeFile($path, $contents);
  }

  public final function appendFileRec(string $path, string $contents): int {
    $this->createDirs($path);
    return $this->appendFile($path, $contents);
  }

  public final function mkdirRec(string $path, int $mode = 0777): void {
    $this->createDirs($path, $mode);
    $this->mkdir($path, $mode);
  }
}
