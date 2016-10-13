<?php

function hacklib_cast_as_boolean($x) {
  return $x;
}

function hacklib_equals($a, $b) {
  return $a == $b;
}

function hacklib_not_equals($a, $b) {
  return $a != $b;
}

function hacklib_id($x) {
  return $x;
}

function hacklib_instanceof($x, $class) {
  return $x instanceof $class;
}

function hacklib_nullsafe($v) {
  if ($v === null) {
    return _HackLibNullObj::$instance ?:
      (_HackLibNullObj::$instance = new _HackLibNullObj());
  }
  return $v;
}

final class _HackLibNullObj {
  public static $instance;
  public function __call($method, $arguments) {
    return null;
  }
  public function __get($prop) {
    return null;
  }
}
