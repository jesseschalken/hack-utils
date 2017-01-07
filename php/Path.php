<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class Path {
    public static function parse($path) {
      return
        \hacklib_cast_as_boolean(is_windows())
          ? WindowsPath::parse($path)
          : PosixPath::parse($path);
    }
    public final function relativeTo($base) {
      if (!\hacklib_cast_as_boolean($this->hasSameRoot($base))) {
        return $this;
      }
      $l = min($this->len(), $base->len());
      $i = 0;
      while (($i < $l) && ($this->name($i) === $base->name($i))) {
        $i++;
      }
      $names = concat(
        repeat("..", $base->len() - $i),
        slice_array($this->names(), $i)
      );
      return $this->withNames($names, true);
    }
    public final function len() {
      return \count($this->names());
    }
    public final function name($i) {
      return get_offset($this->names(), $i);
    }
    public function normalize() {
      $names = array();
      foreach ($this->names() as $name) {
        if (($name === "") || ($name === ".")) {
          continue;
        }
        if (($name === "..") && \hacklib_cast_as_boolean($names)) {
          list($rest, $last) = pop($names);
          if ($last !== "..") {
            $names = $rest;
            continue;
          }
        }
        $names[] = $name;
      }
      return $this->withNames($names);
    }
    public final function split($i) {
      list($left, $right) = split_array_at($this->names(), $i);
      return array($this->withNames($left), $this->withNames($right, true));
    }
    public final function join($path) {
      if (\hacklib_cast_as_boolean($path->isAbsolute())) {
        return $path;
      }
      return $this->withNames(concat($this->names(), $path->names()));
    }
    public abstract function isAbsolute();
    public abstract function names();
    public abstract function format();
    public abstract function hasSameRoot($path);
    public abstract function withNames($names, $relative = false);
  }
  final class PosixPath extends Path {
    public static function parse($path) {
      $self = new self();
      if ($path === "") {
        $self->absolute = false;
        $self->names = array();
      } else {
        $self->absolute = $path[0] === "/";
        $self->names = array();
        foreach (split($path, "/") as $name) {
          if ($name !== "") {
            $self->names[] = $name;
          }
        }
      }
      return $self;
    }
    private $absolute = false;
    private $names = array();
    private function __construct() {}
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
    public function names() {
      return $this->names;
    }
    public function withNames($names, $relative = false) {
      $clone = clone $this;
      $clone->names = $names;
      if (\hacklib_cast_as_boolean($relative)) {
        $clone->absolute = false;
      }
      return $clone;
    }
    public function isAbsolute() {
      return $this->absolute;
    }
    public function hasSameRoot($path) {
      return
        ($path instanceof PosixPath) && ($path->absolute === $this->absolute);
    }
  }
  final class WindowsPath extends Path {
    public static function parse($path) {
      $self = new self();
      $regex =
        self::regex(
          "^\n      (\n        [A-Za-z]:[\\\\/]?\n        |\n        [\\\\/]{0,2}\n        (?![\\\\/])\n      )\n      (.*)\n    \044"
        );
      $match = $regex->matchOrThrow($path);
      $root = $match->get(1);
      $path = $match->get(2);
      $self->root = replace($root, "/", "\\");
      $self->names = array();
      foreach (self::regex("[^\\\\/]+")->matchAll($path) as $match) {
        $self->names[] = $match->toString();
      }
      return $self;
    }
    private static function regex($regex) {
      return PCRE\Pattern::create($regex, "xDsS");
    }
    private $root = "";
    private $names = array();
    private function __construct() {}
    public function format() {
      return $this->root.join($this->names, "\\");
    }
    public function names() {
      return $this->names;
    }
    public function withNames($names, $relative = false) {
      $clone = clone $this;
      $clone->names = $names;
      if (\hacklib_cast_as_boolean($relative)) {
        $clone->root = "";
      }
      return $clone;
    }
    public function isAbsolute() {
      return $this->root !== "";
    }
    public function hasSameRoot($path) {
      return ($path instanceof WindowsPath) && ($path->root === $this->root);
    }
  }
}
