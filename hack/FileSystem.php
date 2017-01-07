<?hh // strict

namespace HackUtils;

final class LocalStreamWrapper implements StreamWrapperInterface {
  public function wrapPath(string $path): string {
    // Passing an empty path is supported to refer to the current directory.
    if ($path === '')
      $path = '.';
    return make_path_local($path);
  }
  public function getContext(): resource {
    return \stream_context_get_default();
  }
  public function join(string $path1, string $path2): string {
    return Path::parse($path1)->join(Path::parse($path2))->format();
  }
  public function split(string $path, int $i): (string, string) {
    list($a, $b) = Path::parse($path)->split($i);
    return tuple($a->format(), $b->format());
  }
}

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

class StreamWrapperFileSystem extends FileSystem
  implements StreamWrapperInterface {
  public function __construct(private StreamWrapperInterface $wrapper) {}

  public final function open(string $path, string $mode): Stream {
    $ctx = $this->getContext();
    $path = $this->wrapPath($path);
    return new FOpenStream($path, $mode, $ctx);
  }

  public final function stat(string $path): Stat {
    return $this->_stat($path, false);
  }

  public final function trystat(string $path): ?Stat {
    return $this->_trystat($path, false);
  }

  public final function rename(string $from, string $to): void {
    $ctx = $this->getContext();
    $from = $this->wrapPath($from);
    $to = $this->wrapPath($to);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\rename($from, $to, $ctx));
  }

  public final function readdir(string $path): array<string> {
    $ctx = $this->getContext();
    $path = $this->wrapPath($path);
    $ret = [];
    FileSystemException::prepare();
    $dir = FileSystemException::assertResource(\opendir($path, $ctx));
    for (; $p = \readdir($dir); $p !== false) {
      // Skip dots
      if ($p === '.' || $p === '..')
        continue;
      $ret[] = $p;
    }
    \closedir($dir);
    return $ret;
  }

  public final function mkdir(string $path, int $mode = 0777): void {
    $ctx = $this->getContext();
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\mkdir($path, $mode, false, $ctx));
  }

  public final function unlink(string $path): void {
    $ctx = $this->getContext();
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\unlink($path, $ctx));
  }

  public final function lstat(string $path): Stat {
    return $this->_stat($path, true);
  }

  public final function trylstat(string $path): ?Stat {
    return $this->_trystat($path, true);
  }

  private function _stat(string $path, bool $lstat): Stat {
    $path = $this->wrapPath($path);
    \clearstatcache();
    FileSystemException::prepare();
    $stat =
      FileSystemException::assertArray($lstat ? \lstat($path) : \stat($path));
    return new ArrayStat($stat);
  }

  private function _statThrowPhpErrors(string $path, bool $lstat): Stat {
    ErrorException::setErrorHandler();
    try {
      $stat = $this->_stat($path, $lstat);
    } catch (\Exception $e) {
      // "finally" is only supported on PHP >= 5.5
      ErrorException::restoreErrorHandler();
      throw $e;
    }
    ErrorException::restoreErrorHandler();
    return $stat;
  }

  private function _trystat(string $path, bool $lstat): ?Stat {
    try {
      return $this->_statThrowPhpErrors($path, $lstat);
    } catch (\ErrorException $e) {
      return new_null();
    } catch (FileSystemException $e) {
      return new_null();
    }
  }

  public final function rmdir(string $path): void {
    $ctx = $this->getContext();
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\rmdir($path, $ctx));
  }

  public final function chmod(string $path, int $mode): void {
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\chmod($path, $mode));
  }

  public final function chown(string $path, int $uid): void {
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\chown($path, (int) $uid));
  }

  public final function chgrp(string $path, int $gid): void {
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\chgrp($path, (int) $gid));
  }

  public final function utime(string $path, int $atime, int $mtime): void {
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\touch($path, $mtime, $atime));
  }

  public final function wrapPath(string $path): string {
    return $this->wrapper->wrapPath($path);
  }

  public final function getContext(): resource {
    return $this->wrapper->getContext();
  }

  public final function join(string $path1, string $path2): string {
    return $this->wrapper->join($path1, $path2);
  }

  public final function split(string $path, int $i): (string, string) {
    return $this->wrapper->split($path, $i);
  }
}

final class LocalFileSystem extends StreamWrapperFileSystem
  implements SymlinkFileSystemInterface {

  public function __construct() {
    parent::__construct(new LocalStreamWrapper());
  }

  public final function symlink(string $path, string $target): void {
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\symlink($target, $path));
  }

  public final function readlink(string $path): string {
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    return FileSystemException::assertString(\readlink($path));
  }

  public final function realpath(string $path): string {
    $path = $this->wrapPath($path);
    \clearstatcache();
    FileSystemException::prepare();
    return FileSystemException::assertString(\realpath($path));
  }

  public final function lchown(string $path, int $uid): void {
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\lchown($path, (int) $uid));
  }

  public final function lchgrp(string $path, int $gid): void {
    $path = $this->wrapPath($path);
    FileSystemException::prepare();
    FileSystemException::assertTrue(\lchgrp($path, (int) $gid));
  }
}

class FileSystemException extends Exception {
  public final static function prepare(): void {
    ErrorException::clearLast();
  }
  public final static function create(
    string $actual,
    string $expected,
  ): \Exception {
    return ErrorException::getLast() ?: parent::create($actual, $expected);
  }
}
