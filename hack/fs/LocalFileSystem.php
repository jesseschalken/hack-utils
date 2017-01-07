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

final class LocalFileSystem extends StreamWrapperFileSystem
  implements SymlinkFileSystemInterface {

  public function __construct() {
    parent::__construct(new LocalStreamWrapper());
  }

  public final function symlink(string $path, string $target): void {
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\symlink($target, $path));
  }

  public final function readlink(string $path): string {
    $path = $this->wrapPath($path);
    return StrictErrors::start()->finishString(\readlink($path));
  }

  public final function realpath(string $path): string {
    $path = $this->wrapPath($path);
    /* HH_IGNORE_ERROR[4105] */
    \clearstatcache(true);
    return StrictErrors::start()->finishString(\realpath($path));
  }

  public final function lchown(string $path, int $uid): void {
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\lchown($path, (int) $uid));
  }

  public final function lchgrp(string $path, int $gid): void {
    $path = $this->wrapPath($path);
    StrictErrors::start()->finishTrue(\lchgrp($path, (int) $gid));
  }
}
