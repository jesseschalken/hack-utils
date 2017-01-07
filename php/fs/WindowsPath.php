<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class WindowsPath extends Path {
    public static function parse($path) {
      $regex =
        self::regex(
          "^\n      (\n        [A-Za-z]:[\\\\/]?\n        |\n        [\\\\/]{0,2}\n        (?![\\\\/])\n      )\n      (.*)\n    \044"
        );
      $match = $regex->matchOrThrow($path);
      $root = $match->get(1);
      $path = $match->get(2);
      $self = new self();
      $self->root = replace($root, "/", "\\");
      foreach (self::regex("[^\\\\/]+")->matchAll($path) as $match) {
        $self->names[] = $match->toString();
      }
      return $self;
    }
    private static function regex($regex) {
      return PCRE\Pattern::create($regex, "xDsS");
    }
    private $root = "";
    public function format() {
      return $this->root.join($this->names, "\\");
    }
    public function isAbsolute() {
      return $this->root !== "";
    }
    public function hasSameRoot($path) {
      return ($path instanceof WindowsPath) && ($path->root === $this->root);
    }
  }
}
