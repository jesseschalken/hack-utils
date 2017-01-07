<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class Path {
    public static function parse($path) {
      return
        (\DIRECTORY_SEPARATOR === "\\")
          ? WindowsPath::parse($path)
          : PosixPath::parse($path);
    }
    protected $names;
    public function __construct($names = array()) {
      $this->names = $names;
    }
    public final function relativeTo($base) {
      if (!\hacklib_cast_as_boolean($this->hasSameRoot($base))) {
        return $this;
      }
      $l = min(\count($this->names), \count($base->names));
      $i = 0;
      while (($i < $l) && ($this->names[$i] === $base->names[$i])) {
        $i++;
      }
      return new static(
        concat(
          repeat("..", \count($base->names) - $i),
          slice_array($this->names, $i)
        )
      );
    }
    public function normalize() {
      $names = array();
      foreach ($this->names as $name) {
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
      return new static($names);
    }
    public final function split($i) {
      list($left, $right) = split_array_at($this->names, $i);
      $clone = clone $this;
      $clone->names = $left;
      return array($clone, new static($right));
    }
    public final function join($path) {
      if (\hacklib_cast_as_boolean($path->isAbsolute())) {
        return $path;
      }
      $clone = clone $this;
      $clone->names = concat($this->names, $path->names);
      return $clone;
    }
    public abstract function isAbsolute();
    public abstract function format();
    public abstract function hasSameRoot($path);
  }
}
