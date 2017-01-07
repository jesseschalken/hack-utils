<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class LocalFileSystem extends StreamWrapper
    implements SymlinkFileSystemInterface {
    public function wrapPath($path) {
      if ($path === "") {
        $path = ".";
      }
      $regex =
        "\n        ^(\n            [a-zA-Z0-9+\\-.]{2,}\n            ://\n          |\n            data:\n          |\n            zlib:\n        )\n      ";
      if (!\hacklib_cast_as_boolean(
            PCRE\Pattern::create($regex, "xDsS")->matches($path)
          )) {
        return $path;
      }
      return ".".\DIRECTORY_SEPARATOR.$path;
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
    public function symlink($path, $target) {
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\symlink($target, $path));
    }
    public function readlink($path) {
      $path = $this->wrapPath($path);
      return StrictErrors::start()->finishString(\readlink($path));
    }
    public function realpath($path) {
      $path = $this->wrapPath($path);
      \clearstatcache(true);
      return StrictErrors::start()->finishString(\realpath($path));
    }
    public function lchown($path, $uid) {
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\lchown($path, (int) $uid));
    }
    public function lchgrp($path, $gid) {
      $path = $this->wrapPath($path);
      StrictErrors::start()->finishTrue(\lchgrp($path, (int) $gid));
    }
  }
}
