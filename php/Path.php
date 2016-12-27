<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class Path {
    public static function parse($path) {
      return
        is_windows() ? WindowsPath::parse($path) : PosixPath::parse($path);
    }
    public final function relativeTo($base) {
      if (!$this->hasSameRoot($base)) {
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
        if (($name === "..") && $names) {
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
    public final function join_str($name) {
      return $this->join($this->reparse($name));
    }
    public abstract function isAbsolute();
    public final function join($path) {
      if ($path->isAbsolute()) {
        return $path;
      }
      return $this->withNames(concat($this->names(), $path->names()));
    }
    public final function dir() {
      if (!$this->len()) {
        return new_null();
      }
      return $this->withNames(slice_array($this->names(), 0, -1));
    }
    public final function base() {
      if (!$this->len()) {
        return NULL_STRING;
      }
      return $this->name(-1);
    }
    public final function ext() {
      $name = $this->base();
      if ($name === null) {
        return NULL_STRING;
      }
      $pos = find_last($name, ".");
      if (($pos === null) || ($pos == 0)) {
        return NULL_STRING;
      }
      return slice($name, $pos + 1);
    }
    public abstract function names();
    public abstract function format();
    public abstract function hasSameRoot($path);
    public abstract function withNames($names, $relative = false);
    public abstract function reparse($path);
  }
  final class PosixPath extends Path {
    public static function parse($path) {
      $self = new self();
      $self->fromString($path);
      return $self;
    }
    private $absolute = false;
    private $names = array();
    private function __construct() {}
    public function normalize() {
      $ret = parent::normalize();
      if ($ret->absolute) {
        $i = 0;
        $l = \count($this->names);
        while (($i < $l) && ($this->names[$i] === "..")) {
          $i++;
        }
        if ($i) {
          $ret->names = slice_array($this->names, $i);
        }
      }
      return $ret;
    }
    public function format() {
      $ret = join($this->names, "/");
      if ($this->absolute) {
        $ret = "/".$ret;
      }
      if ($ret === "") {
        $ret = ".";
      }
      return $ret;
    }
    public function names() {
      return $this->names;
    }
    public function withNames($names, $relative = false) {
      $clone = clone $this;
      $clone->names = $names;
      if ($relative) {
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
    public function reparse($path) {
      $clone = clone $this;
      $clone->fromString($path);
      return $clone;
    }
    private function fromString($path) {
      if ($path === "") {
        $this->absolute = false;
        $this->names = array();
      } else {
        $this->absolute = $path[0] === "/";
        $this->names = array();
        foreach (split($path, "/") as $name) {
          if ($name !== "") {
            $this->names[] = $name;
          }
        }
      }
    }
  }
  final class WindowsPath extends Path {
    public static function parse($path) {
      $self = new self();
      $self->fromString($path);
      return $self;
    }
    private static function regex($regex) {
      return PCRE\Pattern::create($regex, "xDsS");
    }
    private $root = "";
    private $names = array();
    private function __construct() {}
    public function format() {
      $ret = $this->root.join($this->names, "\\");
      if ($ret === "") {
        return ".";
      }
      return $ret;
    }
    public function names() {
      return $this->names;
    }
    public function withNames($names, $relative = false) {
      $clone = clone $this;
      $clone->names = $names;
      if ($relative) {
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
    public function reparse($path) {
      $clone = clone $this;
      $clone->fromString($path);
      return $clone;
    }
    private function fromString($path) {
      $regex =
        self::regex(
          "^\n      (\n        [A-Za-z]:[\\\\/]?\n        |\n        [\\\\/]{0,2}\n        (?![\\\\/])\n      )\n      (.*)\n    \044"
        );
      $match = $regex->matchOrThrow($path);
      $root = $match->get(1);
      $path = $match->get(2);
      $this->root = replace($root, "/", "\\");
      $this->names = array();
      foreach (self::regex("[^\\\\/]+")->matchAll($path) as $match) {
        $this->names[] = $match->toString();
      }
    }
  }
}
