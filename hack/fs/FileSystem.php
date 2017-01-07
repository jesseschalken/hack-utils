<?hh // strict

namespace HackUtils;

abstract class FileSystem implements FileSystemInterface {
  public abstract function open(string $path, string $mode): Stream;
  public abstract function stat(string $path): Stat;
  public abstract function lstat(string $path): Stat;
  public abstract function trystat(string $path): ?Stat;
  public abstract function trylstat(string $path): ?Stat;

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
