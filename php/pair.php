<?php
namespace HackUtils\pair {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\vector;
  use \HackUtils\key;
  use \HackUtils\map;
  use \HackUtils\set;
  function create($a, $b) {
    return array($a, $b);
  }
  function fst($t) {
    return $t[0];
  }
  function snd($t) {
    return $t[1];
  }
  function cast($x) {
    return
      ((!\hacklib_cast_as_boolean(\is_array($x))) ||
       \hacklib_not_equals(\count($x), 2) ||
       (!\hacklib_cast_as_boolean(vector\is_vector($x))))
        ? null
        : array($x[0], $x[1]);
  }
}
