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
  function fix_range($offset, $length, $total) {
    $offset = fix_offset($offset, $total);
    $length = fix_offset($length ?? $total, $total - $offset);
    return array($offset, $length);
  }
  function fix_offset($num, $len) {
    if ($len < 0) {
      throw new \Exception("Length must be >= 0, ".$len." given");
    }
    if ($num < 0) {
      $num += $len;
    }
    if ($num < 0) {
      return 0;
    }
    if ($num > $len) {
      return $len;
    }
    return $num;
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
