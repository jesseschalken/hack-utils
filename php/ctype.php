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
  function is_alnum($s, $i = 0) {
    return \ctype_alnum(char_at($s, $i));
  }
  function is_blank($s, $i = 0) {
    $c = char_at($s, $i);
    return ($c === " ") || ($c === "\t");
  }
  function is_alpha($s, $i = 0) {
    return \ctype_alpha(char_at($s, $i));
  }
  function is_cntrl($s, $i = 0) {
    return \ctype_cntrl(char_at($s, $i));
  }
  function is_digit($s, $i = 0) {
    return \ctype_digit(char_at($s, $i));
  }
  function is_graph($s, $i = 0) {
    return \ctype_graph(char_at($s, $i));
  }
  function is_lower($s, $i = 0) {
    return \ctype_lower(char_at($s, $i));
  }
  function is_print($s, $i = 0) {
    return \ctype_print(char_at($s, $i));
  }
  function is_punct($s, $i = 0) {
    return \ctype_punct(char_at($s, $i));
  }
  function is_space($s, $i = 0) {
    return \ctype_space(char_at($s, $i));
  }
  function is_upper($s, $i = 0) {
    return \ctype_upper(char_at($s, $i));
  }
  function is_xdigit($s, $i = 0) {
    return \ctype_xdigit(char_at($s, $i));
  }
}
