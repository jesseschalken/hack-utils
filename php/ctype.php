<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function all_alnum($s) {
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_alnum($s));
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
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_alpha($s));
  }
  function all_cntrl($s) {
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_cntrl($s));
  }
  function all_digit($s) {
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_digit($s));
  }
  function all_graph($s) {
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_graph($s));
  }
  function all_lower($s) {
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_lower($s));
  }
  function all_print($s) {
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_print($s));
  }
  function all_punct($s) {
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_punct($s));
  }
  function all_space($s) {
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_space($s));
  }
  function all_upper($s) {
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_upper($s));
  }
  function all_xdigit($s) {
    return ($s === "") || \hacklib_cast_as_boolean(\ctype_xdigit($s));
  }
  function _char($s, $i = 0) {
    $l = \strlen($s);
    if ($i < 0) {
      $i += $l;
    }
    if (($i < 0) || ($i >= $l)) {
      throw new \Exception(
        "String offset ".$i." out of bounds in string of length ".$l
      );
    }
    return $s[$i];
  }
  function is_alnum($s, $i = 0) {
    return \ctype_alnum(_char($s, $i));
  }
  function is_blank($s, $i = 0) {
    $c = _char($s, $i);
    return ($c === " ") || ($c === "\t");
  }
  function is_alpha($s, $i = 0) {
    return \ctype_alpha(_char($s, $i));
  }
  function is_cntrl($s, $i = 0) {
    return \ctype_cntrl(_char($s, $i));
  }
  function is_digit($s, $i = 0) {
    return \ctype_digit(_char($s, $i));
  }
  function is_graph($s, $i = 0) {
    return \ctype_graph(_char($s, $i));
  }
  function is_lower($s, $i = 0) {
    return \ctype_lower(_char($s, $i));
  }
  function is_print($s, $i = 0) {
    return \ctype_print(_char($s, $i));
  }
  function is_punct($s, $i = 0) {
    return \ctype_punct(_char($s, $i));
  }
  function is_space($s, $i = 0) {
    return \ctype_space(_char($s, $i));
  }
  function is_upper($s, $i = 0) {
    return \ctype_upper(_char($s, $i));
  }
  function is_xdigit($s, $i = 0) {
    return \ctype_xdigit(_char($s, $i));
  }
}
