<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class TestCtype extends Test {
    public function run() {
      self::assertEqual(all_alnum(""), true);
      self::assertEqual(all_blank(""), true);
      self::assertEqual(all_alpha(""), true);
      self::assertEqual(all_cntrl(""), true);
      self::assertEqual(all_digit(""), true);
      self::assertEqual(all_graph(""), true);
      self::assertEqual(all_lower(""), true);
      self::assertEqual(all_print(""), true);
      self::assertEqual(all_punct(""), true);
      self::assertEqual(all_space(""), true);
      self::assertEqual(all_upper(""), true);
      self::assertEqual(all_xdigit(""), true);
      self::assertEqual(all_alnum("\000"), false);
      self::assertEqual(all_blank("\000"), false);
      self::assertEqual(all_alpha("\000"), false);
      self::assertEqual(all_cntrl("\000"), true);
      self::assertEqual(all_digit("\000"), false);
      self::assertEqual(all_graph("\000"), false);
      self::assertEqual(all_lower("\000"), false);
      self::assertEqual(all_print("\000"), false);
      self::assertEqual(all_punct("\000"), false);
      self::assertEqual(all_space("\000"), false);
      self::assertEqual(all_upper("\000"), false);
      self::assertEqual(all_xdigit("\000"), false);
      self::assertEqual(all_alnum("hd8whASDe1"), true);
      self::assertEqual(all_blank("   \t\t  \t"), true);
      self::assertEqual(all_alpha("jashbAUJSHYBDef"), true);
      self::assertEqual(all_cntrl("\000\r\013\014\t\n\r"), true);
      self::assertEqual(all_digit("24976230"), true);
      self::assertEqual(all_graph("\044%&'(56?@NO]^bclmn|"), true);
      self::assertEqual(all_lower("jefjwehfasd"), true);
      self::assertEqual(all_print("\044%&'(56?@NO]^bclmn| "), true);
      self::assertEqual(all_punct("\044%&'(?@]^|"), true);
      self::assertEqual(all_space("\r\013\014\t\n\r "), true);
      self::assertEqual(all_upper("AHBFJHEBFOIAS"), true);
      self::assertEqual(all_xdigit("98234ABCabdfDEF"), true);
      self::assertEqual(all_alnum("hd8,whASDe1"), false);
      self::assertEqual(all_blank("   \t\td  \t"), false);
      self::assertEqual(all_alpha("jashbA|UJSHYBDef"), false);
      self::assertEqual(all_cntrl("\000\rf\013\014\t\n\r"), false);
      self::assertEqual(all_digit("24976d230"), false);
      self::assertEqual(all_graph("\044%&'(5 6?@\tNO]^bclmn|"), false);
      self::assertEqual(all_lower("jefjwehfDasd"), false);
      self::assertEqual(all_print("\044%&'(56?@\r\tNO]^bclmn| "), false);
      self::assertEqual(all_punct("\044%&'(?@da]^|"), false);
      self::assertEqual(all_space("\r\013\014\tp\n\r "), false);
      self::assertEqual(all_upper("AHBFJHEBasdFOIAS"), false);
      self::assertEqual(all_xdigit("98234ABCDEFGHIJklm"), false);
      self::assertEqual(is_alnum("hd8,whASDe1", 3), false);
      self::assertEqual(is_blank("   \t\td  \t", 5), false);
      self::assertEqual(is_alpha("jashbA|UJSHYBDef", 6), false);
      self::assertEqual(is_cntrl("\000\rf\013\014\t\n\r", 2), false);
      self::assertEqual(is_digit("24976d230", 5), false);
      self::assertEqual(is_graph("\044%&'(5 6?@\tNO]^bclmn|", 6), false);
      self::assertEqual(is_lower("jefjwehfDasd", 8), false);
      self::assertEqual(is_print("\044%&'(56?@\r\tNO]^bclmn| ", 9), false);
      self::assertEqual(is_punct("\044%&'(?@da]^|", 8), false);
      self::assertEqual(is_space("\r\013\014\tp\n\r ", 4), false);
      self::assertEqual(is_upper("AHBFJHEBasdFOIAS", 10), false);
      self::assertEqual(is_xdigit("98234ABCDEFGHIJklm", 11), false);
      self::assertEqual(is_alnum("hd8,whASDe1", 2), true);
      self::assertEqual(is_blank("   \t\td  \t", 8), true);
      self::assertEqual(is_alpha("jashbA|UJSHYBDef", 2), true);
      self::assertEqual(is_cntrl("\000\rf\013\014\t\n\r", 6), true);
      self::assertEqual(is_digit("24976d230", 3), true);
      self::assertEqual(is_graph("\044%&'(5 6?@\tNO]^bclmn|", 8), true);
      self::assertEqual(is_lower("jefjwehfDasd", 5), true);
      self::assertEqual(is_print("\044%&'(56?@\r\tNO]^bclmn| ", 13), true);
      self::assertEqual(is_punct("\044%&'(?@da]^|", 4), true);
      self::assertEqual(is_space("\r\013\014\tp\n\r ", 6), true);
      self::assertEqual(is_upper("AHBFJHEBasdFOIAS", 4), true);
      self::assertEqual(is_xdigit("98234ABCDEFGHIJklm", 5), true);
    }
  }
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
