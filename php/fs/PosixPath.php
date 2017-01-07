<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class PosixPath extends Path {
    public static function parse($path) {
      $self = new self();
      if ($path === "") {
        return $self;
      }
      $self->absolute = $path[0] === "/";
      foreach (split($path, "/") as $name) {
        if ($name !== "") {
          $self->names[] = $name;
        }
      }
      return $self;
    }
    private $absolute = false;
    public function normalize() {
      $ret = parent::normalize();
      if (\hacklib_cast_as_boolean($ret->absolute)) {
        $i = 0;
        $l = \count($ret->names);
        while (($i < $l) && ($ret->names[$i] === "..")) {
          $i++;
        }
        if (\hacklib_cast_as_boolean($i)) {
          $ret->names = slice_array($this->names, $i);
        }
      }
      return $ret;
    }
    public function format() {
      $ret = join($this->names, "/");
      if (\hacklib_cast_as_boolean($this->absolute)) {
        $ret = "/".$ret;
      }
      return $ret;
    }
    public function isAbsolute() {
      return $this->absolute;
    }
    public function hasSameRoot($path) {
      return
        ($path instanceof PosixPath) && ($path->absolute === $this->absolute);
    }
  }
}
