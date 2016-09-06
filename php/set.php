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
  function union($a, $b) {
    return \array_replace($a, $b);
  }
  function intersect($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function diff($a, $b) {
    return \array_diff_key($a, $b);
  }
  function equals($a, $b) {
    return
      (!\hacklib_cast_as_boolean(\array_diff_key($a, $b))) &&
      (!\hacklib_cast_as_boolean(\array_diff_key($b, $a)));
  }
  function reverse($set) {
    return \array_reverse($set, true);
  }
  function count($set) {
    return \count($set);
  }
}
