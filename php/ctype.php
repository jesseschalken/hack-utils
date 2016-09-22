<?php
namespace HackUtils\ctype {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use function \HackUtils\str\is_empty;
  function alnum($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_alnum($s));
  }
  function blank($s) {
    $l = \strlen($s);
    for ($i = 0; $i < $l; $i++) {
      $c = $s[$i];
      if (($c !== "\t") && ($c !== " ")) {
        return false;
      }
    }
    return true;
  }
  function alpha($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_alpha($s));
  }
  function cntrl($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_cntrl($s));
  }
  function digit($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_digit($s));
  }
  function graph($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_graph($s));
  }
  function lower($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_lower($s));
  }
  function print($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_print($s));
  }
  function punct($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_punct($s));
  }
  function space($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_space($s));
  }
  function upper($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_upper($s));
  }
  function xdigit($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_xdigit($s));
  }
}
