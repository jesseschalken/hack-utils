<?php
namespace HackUtils\ctype {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use function \HackUtils\str\is_empty;
  function all_alnum($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_alnum($s));
  }
  function all_blank($s) {
    $l = \strlen($s);
    for ($i = 0; $i < $l; $i++) {
      $c = $s[$i];
      if (($c !== "\t") && ($c !== " ")) {
        return false;
      }
    }
    return true;
  }
  function all_alpha($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_alpha($s));
  }
  function all_cntrl($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_cntrl($s));
  }
  function all_digit($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_digit($s));
  }
  function all_graph($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_graph($s));
  }
  function all_lower($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_lower($s));
  }
  function all_print($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_print($s));
  }
  function all_punct($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_punct($s));
  }
  function all_space($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_space($s));
  }
  function all_upper($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_upper($s));
  }
  function all_xdigit($s) {
    return
      \hacklib_cast_as_boolean(is_empty($s)) ||
      \hacklib_cast_as_boolean(\ctype_xdigit($s));
  }
}
