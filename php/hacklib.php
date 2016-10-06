<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
function hacklib_cast_as_boolean($x) {
  return (bool) \hacklib_cast_as_boolean($x);
}
function hacklib_equals($a, $b) {
  return \hacklib_equals($a, $b);
}
function hacklib_not_equals($a, $b) {
  return \hacklib_not_equals($a, $b);
}
function hacklib_id($x) {
  return $x;
}
function hacklib_instanceof($x, $class) {
  return \hacklib_instanceof($x, $class);
}
function hacklib_nullsafe($v) {
  if ($v === null) {
    return
      \hacklib_cast_as_boolean(_HackLibNullObj::$instance) ?: (_HackLibNullObj::$instance =
                                                                 new _HackLibNullObj());
  }
  return $v;
}
class _HackLibNullObj {
  public static $instance;
  public function __call($method, $arguments) {
    return null;
  }
  public function __get($prop) {
    return null;
  }
}
