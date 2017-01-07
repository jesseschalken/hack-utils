<?hh // strict

namespace HackUtils;

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
    StrictErrors::start()->finishTrue(\rename($from, $to, $ctx));
  }

  public final function readdir(string $path): array<string> {
    $ctx = $this->getContext();
    $path = $this->wrapPath($path);
    $ret = [];
    $dir = StrictErrors::start()->finishResource(\opendir($path, $ctx));
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
    StrictErrors::start()->finishTrue(\mkdir($path, $mode, false, $ctx));
  }

  public final function unlink(string $path): void {
    $ctx = $this->getContext();
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\unlink($path, $ctx));
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
    $errors = StrictErrors::start();
    $stat = $lstat ? \lstat($path) : \stat($path);
    // Make sure we do the assertion with StatFailed instead of Exception
    $stat = StatFailed::assertArray($stat);
    $errors->finish();
    return new ArrayStat($stat);
  }

  private function _trystat(string $path, bool $lstat): ?Stat {
    try {
      return $this->_stat($path, $lstat);
    } catch (\ErrorException $e) {
      return new_null();
    } catch (StatFailed $e) {
      return new_null();
    }
  }

  public final function rmdir(string $path): void {
    $ctx = $this->getContext();
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\rmdir($path, $ctx));
  }

  public final function chmod(string $path, int $mode): void {
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\chmod($path, $mode));
  }

  public final function chown(string $path, int $uid): void {
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\chown($path, (int) $uid));
  }

  public final function chgrp(string $path, int $gid): void {
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\chgrp($path, (int) $gid));
  }

  public final function utime(string $path, int $atime, int $mtime): void {
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\touch($path, $mtime, $atime));
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
