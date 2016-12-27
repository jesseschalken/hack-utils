<?hh // strict

namespace HackUtils;

abstract class FileSystem {
  protected function __construct() {}
  protected function __destruct() {}

  public abstract function mkdir(string $path, int $mode = 0777): void;
  public abstract function readdir(string $path): array<string>;
  public abstract function rmdir(string $path): void;

  public abstract function rename(string $oldpath, string $newpath): void;
  public abstract function unlink(string $path): void;

  public final function remove(string $path): void {
    $stat = $this->lstat($path);
    if ($stat && $stat->isDir())
      $this->rmdir($path); else
      $this->unlink($path);
  }

  public final function readdir_rec(string $path): array<string> {
    $parsed = $this->path($path);
    $ret = [];
    foreach ($this->readdir($path) as $p) {
      $ret[] = $p;
      $full = $parsed->join_str($p)->format();
      $stat = $this->stat($full);
      if ($stat && $stat->isDir()) {
        foreach ($this->readdir_rec($full) as $p2) {
          $ret[] = $p.$this->sep().$p2;
        }
      }
    }
    return $ret;
  }

  public final function remove_rec(string $path): int {
    $stat = $this->lstat($path);
    if ($stat && $stat->isDir()) {
      return $this->rmdir_rec($path);
    }
    $this->unlink($path);
    return 1;
  }

  public final function rmdir_rec(string $path): int {
    $parsed = $this->path($path);
    $ret = 0;
    foreach ($this->readdir($path) as $p) {
      $ret += $this->rmdir_rec($parsed->join_str($p)->format());
    }
    $this->rmdir($path);
    $ret++;
    return $ret;
  }

  public final function mkdir_rec(string $path, int $mode = 0777): void {
    $dir = $this->path($path)->dir()?->format();
    if ($dir !== null && !$this->lstat($dir)) {
      $this->mkdir_rec($dir, $mode);
    }
    $this->mkdir($path, $mode);
  }

  public final function exists(string $path): bool {
    return $this->stat($path) ? true : false;
  }
  public final function lexists(string $path): bool {
    return $this->lstat($path) ? true : false;
  }

  public abstract function stat(string $path): ?Stat;
  public abstract function chmod(string $path, int $mode): void;
  public abstract function chown(string $path, int $uid): void;
  public abstract function chgrp(string $path, int $gid): void;
  public abstract function utime(string $path, int $atime, int $mtime): void;

  public abstract function open(string $path, string $mode): Stream;

  public abstract function symlink(string $path, string $contents): void;
  public abstract function readlink(string $path): string;

  public abstract function lstat(string $path): ?Stat;
  public abstract function lchown(string $path, int $uid): void;
  public abstract function lchgrp(string $path, int $gid): void;

  public abstract function realpath(string $path): string;

  public abstract function pwd(): string;

  public function path(string $path): Path {
    return PosixPath::parse($path);
  }

  public function sep(): string {
    return '/';
  }

  public function readFile(string $path): string {
    return $this->open($path, 'rb')->getContents();
  }

  public function writeFile(string $path, string $contents): void {
    $this->open($path, 'wb')->write($contents);
  }

  public function appendFile(string $path, string $contents): void {
    $this->open($path, 'ab')->write($contents);
  }

  public function toStreamWrapper(): StreamWrapper {
    return new FileSystemStreamWrapper($this);
  }
}

class ErrorAssert extends \RuntimeException {
  public final static function isZero(string $name, mixed $ret): void {
    if ($ret !== 0)
      throw self::create($name);
  }

  public final static function isArray<T>(string $name, T $ret): T {
    if (!\is_array($ret))
      throw self::create($name);
    return $ret;
  }

  public final static function isString(string $name, mixed $ret): string {
    if (!\is_string($ret))
      throw self::create($name);
    return $ret;
  }

  public final static function isInt(string $name, mixed $ret): int {
    if (!\is_int($ret))
      throw self::create($name);
    return $ret;
  }

  public final static function isTrue(string $name, mixed $ret): void {
    if ($ret !== true)
      throw self::create($name);
  }

  public final static function isResource(string $name, mixed $ret): resource {
    if (!\is_resource($ret))
      throw self::create($name);
    return $ret;
  }

  public final static function isBool(string $name, mixed $ret): bool {
    if (!\is_bool($ret))
      throw self::create($name);
    return $ret;
  }

  private static function create(string $name): ErrorAssert {
    $error = \error_get_last();
    $msg = $name.'() failed';
    if ($error) {
      $e = new self($msg.': '.$error['message']);
      $e->file = $error['file'];
      $e->line = $error['line'];
      return $e;
    }
    return new self($msg);
  }
}

abstract class StreamWrapper extends FileSystem {
  public final function open(string $path, string $mode): Stream {
    $path = $this->wrapPath($path);
    return new FOpenStream($path, $mode, $this->getContext());
  }

  public final function stat(string $path): ?Stat {
    $path = $this->wrapPath($path);
    \clearstatcache();
    // We have to avoid calling stat() if the file doesn't exist so we
    // can return null without throwing an exception or triggering a PHP error.
    // PHP's stat cache should ensure only one stat call is actually made
    // to the underlying stream wrapper if the file does exist.
    if (!\file_exists($path))
      return null;
    return new ArrayStat(ErrorAssert::isArray('stat', \stat($path)));
  }

  public final function rename(string $from, string $to): void {
    $from = $this->wrapPath($from);
    $to = $this->wrapPath($to);
    ErrorAssert::isTrue('rename', \rename($from, $to, $this->getContext()));
  }

  public final function readdir(string $path): array<string> {
    $path = $this->wrapPath($path);
    $ret = [];
    $dir = ErrorAssert::isResource(
      'opendir',
      \opendir($path, $this->getContext()),
    );
    for (; $p = \readdir($dir); $p !== false) {
      if ($p === '.' || $p === '..')
        continue;
      $ret[] = $p;
    }
    \closedir($dir);
    return $ret;
  }

  public final function mkdir(string $path, int $mode = 0777): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isTrue(
      'mkdir',
      \mkdir($path, $mode, false, $this->getContext()),
    );
  }

  public final function unlink(string $path): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isTrue('unlink', \unlink($path, $this->getContext()));
  }

  public final function lstat(string $path): ?Stat {
    $path = $this->wrapPath($path);
    \clearstatcache();
    // We have to avoid calling lstat() if the file doesn't exist
    // so we can return null without throwing an Exception
    // or raising a PHP error.
    // PHP's stat cache should ensure only one stat is actually done
    // on the underlying stream wrapper...or maybe not because file_exists()
    // does a stat() not an lstat().
    if (!\file_exists($path) && !\is_link($path))
      return new_null();
    return new ArrayStat(ErrorAssert::isArray('lstat', \lstat($path)));
  }

  public final function rmdir(string $path): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isTrue('rmdir', \rmdir($path, $this->getContext()));
  }

  public final function chmod(string $path, int $mode): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isTrue('chmod', \chmod($path, $mode));
  }

  public final function chown(string $path, int $uid): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isTrue('chown', \chown($path, (int) $uid));
  }

  public final function chgrp(string $path, int $gid): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isTrue('chgrp', \chgrp($path, (int) $gid));
  }

  public final function utime(string $path, int $atime, int $mtime): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isTrue('touch', \touch($path, $mtime, $atime));
  }

  public final function readFile(string $path): string {
    $path = $this->wrapPath($path);
    return ErrorAssert::isString(
      'file_get_contents',
      \file_get_contents($path, false, $this->getContext()),
    );
  }

  public final function writeFile(string $path, string $contents): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isString(
      'file_put_contents',
      \file_put_contents($path, $contents, 0, $this->getContext()),
    );
  }

  public final function appendFile(string $path, string $contents): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isString(
      'file_put_contents',
      \file_put_contents(
        $path,
        $contents,
        \FILE_APPEND,
        $this->getContext(),
      ),
    );
  }

  public final function toStreamWrapper(): StreamWrapper {
    return $this;
  }

  public abstract function wrapPath(string $path): string;

  public function getContext(): ?resource {
    return NULL_RESOURCE;
  }
}

final class LocalFileSystem extends StreamWrapper {
  public final function symlink(string $path, string $target): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isTrue('symlink', \symlink($path, $target));
  }

  public final function readlink(string $path): string {
    $path = $this->wrapPath($path);
    return ErrorAssert::isString('readlink', \readlink($path));
  }

  public final function realpath(string $path): string {
    $path = $this->wrapPath($path);
    \clearstatcache();
    return ErrorAssert::isString('realpath', \realpath($path));
  }

  public final function lchown(string $path, int $uid): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isTrue('lchown', \lchown($path, (int) $uid));
  }

  public final function lchgrp(string $path, int $gid): void {
    $path = $this->wrapPath($path);
    ErrorAssert::isTrue('lchgrp', \lchgrp($path, (int) $gid));
  }

  public final function pwd(): string {
    return ErrorAssert::isString('getcwd', \getcwd());
  }

  public function path(string $path): Path {
    return Path::parse($path);
  }

  public function sep(): string {
    return \DIRECTORY_SEPARATOR;
  }

  public function wrapPath(string $path): string {
    // Look at php_stream_locate_url_wrapper() in PHP source
    // or Stream::getWrapperProtocol() in HHVM
    //
    // Basically, any path that matches this regex is likely to be considered a
    // URL for another stream wrapper.
    //
    // Handily, a path that matches this regex is guaranteed not to be an
    // absolute path on POSIX or Windows, so if it matches we can safely
    // force it not to match by prefixing it with ./ on POSIX and .\ on
    // Windows.
    $regex = '
      ^(
          [a-zA-Z0-9+\\-.]{2,}
          ://
        |
          data:
        |
          zlib:
      )
    ';

    if (self::regex($regex)->matches($path)) {
      return '.'.$this->sep().$path;
    }

    return $path;
  }

  private static function regex(string $regex): PCRE\Pattern {
    return PCRE\Pattern::create($regex, 'xDsS');
  }
}
