<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class LocalStreamWrapper implements StreamWrapperInterface {
    public function wrapPath($path) {
      if ($path === "") {
        $path = ".";
      }
      return make_path_local($path);
    }
    public function getContext() {
      return \stream_context_get_default();
    }
    public function join($path1, $path2) {
      return Path::parse($path1)->join(Path::parse($path2))->format();
    }
    public function split($path, $i) {
      list($a, $b) = Path::parse($path)->split($i);
      return array($a->format(), $b->format());
    }
  }
  final class LocalFileSystem extends StreamWrapperFileSystem
    implements SymlinkFileSystemInterface {
    public function __construct() {
      parent::__construct(new LocalStreamWrapper());
    }
    public final function symlink($path, $target) {
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\symlink($target, $path));
    }
    public final function readlink($path) {
      $path = $this->wrapPath($path);
      return StrictErrors::start()->finishString(\readlink($path));
    }
    public final function realpath($path) {
      $path = $this->wrapPath($path);
      \clearstatcache(true);
      return StrictErrors::start()->finishString(\realpath($path));
    }
    public final function lchown($path, $uid) {
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\lchown($path, (int) $uid));
    }
    public final function lchgrp($path, $gid) {
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\lchgrp($path, (int) $gid));
    }
  }
}
