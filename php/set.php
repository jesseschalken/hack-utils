<?php
namespace HackUtils\set {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\vector;
  use \HackUtils\map;
  use \HackUtils\set;
  function create($values = array()) {
    return
      \hacklib_cast_as_boolean($values)
        ? \array_fill_keys($values, true)
        : array();
  }
  function values($set) {
    return \array_keys($set);
  }
  function add($set, $val) {
    $set[$val] = true;
    return $set;
  }
  function union($a, $b) {
    return \array_replace($a, $b);
  }
  function union_all($sets) {
    return \call_user_func_array("array_replace", $sets);
  }
  function intersect($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function diff($a, $b) {
    return \array_diff_key($a, $b);
  }
  function equal($a, $b) {
    return
      (!\hacklib_cast_as_boolean(\array_diff_key($a, $b))) &&
      (!\hacklib_cast_as_boolean(\array_diff_key($b, $a)));
  }
  function reverse($set) {
    return \array_reverse($set, true);
  }
  function size($set) {
    return \count($set);
  }
  function contains($set, $value) {
    return \array_key_exists($value, $set);
  }
}
