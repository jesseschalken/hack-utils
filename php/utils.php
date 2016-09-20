<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function new_null() {
    return null;
  }
  function null_throws($value, $message = "Unexpected null") {
    if ($value === null) {
      throw new \Exception($message);
    }
    return $value;
  }
  function if_null($x, $y) {
    return ($x === null) ? $y : $x;
  }
  final class Ref {
    private $value;
    public function __construct($value) {
      $this->value = $value;
    }
    public function get() {
      return $this->value;
    }
    public function set($value) {
      $this->value = $value;
    }
  }
}
