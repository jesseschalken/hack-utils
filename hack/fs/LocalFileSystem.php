<?hh // strict

namespace HackUtils;

final class LocalFileSystem extends StreamWrapper
  implements SymlinkFileSystemInterface {

  public function wrapPath(string $path): string {
    // Passing an empty path is supported to refer to the current directory.
    if ($path === '')
      $path = '.';

    // Look at php_stream_locate_url_wrapper() in PHP source
    // or Stream::getWrapperProtocol() in HHVM
    //
    // Basically, any path that matches this regex will to be considered a
    // URL for another stream wrapper. Handily, a path that matches is guaranteed
    // not to be an absolute path on POSIX or Windows, so if it matches we can
    // safely force it not to match by prefixing it with ./ on POSIX and .\ on
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

    if (!PCRE\Pattern::create($regex, 'xDsS')->matches($path))
      return $path;

    return '.'.\DIRECTORY_SEPARATOR.$path;
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

  public function symlink(string $path, string $target): void {
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\symlink($target, $path));
  }

  public function readlink(string $path): string {
    $path = $this->wrapPath($path);
    return StrictErrors::start()->finishString(\readlink($path));
  }

  public function realpath(string $path): string {
    $path = $this->wrapPath($path);
    /* HH_IGNORE_ERROR[4105] */
    \clearstatcache(true);
    return StrictErrors::start()->finishString(\realpath($path));
  }

  public function lchown(string $path, int $uid): void {
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\lchown($path, (int) $uid));
  }

  public function lchgrp(string $path, int $gid): void {
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\lchgrp($path, (int) $gid));
  }
}
