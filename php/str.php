<?php
namespace HackUtils\str {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\vector;
  use \HackUtils\str;
  use \HackUtils\math;
  function to_hex($string) {
    return \bin2hex($string);
  }
  function from_hex($string) {
    $ret = \hex2bin($string);
    if (!\hacklib_cast_as_boolean(\is_string($ret))) {
      throw new \Exception("Invalid hex string: ".$string);
    }
    return $ret;
  }
  function shuffle($string) {
    return \str_shuffle($string);
  }
  function reverse($string) {
    return \strrev($string);
  }
  function to_lower($string) {
    return \strtolower($string);
  }
  function to_upper($string) {
    return \strtoupper($string);
  }
  const SPACE_CHARS = " \t\r\n\013\014";
  const TRIM_CHARS = " \t\r\n\013\000";
  function trim($string, $chars = TRIM_CHARS) {
    return \trim($string, $chars);
  }
  function trim_left($string, $chars = TRIM_CHARS) {
    return \ltrim($string, $chars);
  }
  function trim_right($string, $chars = TRIM_CHARS) {
    return \rtrim($string, $chars);
  }
  function split($string, $delimiter = "", $limit = 0x7FFFFFFF) {
    if ($limit < 1) {
      throw new \Exception("Limit must be >= 1");
    }
    if ($delimiter === "") {
      if ($string === "") {
        return array();
      }
      if (\hacklib_equals($limit, 1)) {
        return array($string);
      }
      if (length($string) > $limit) {
        $ret = \str_split(slice($string, 0, $limit - 1));
        $ret[] = slice($string, $limit - 1);
        return $ret;
      }
      return \str_split($string);
    }
    return \explode($delimiter, $string, $limit);
  }
  function split_at($string, $offset) {
    $offset = _fix_offset($string, $offset);
    return array(slice($string, 0, $offset), slice($string, $offset));
  }
  function lines($string) {
    $lines = split($string, "\n");
    foreach ($lines as $i => $line) {
      if (slice($line, -1) === "\r") {
        $lines[$i] = slice($line, 0, -1);
      }
    }
    if (\hacklib_cast_as_boolean($lines) &&
        ($lines[vector\length($lines) - 1] === "")) {
      $lines = vector\slice($lines, 0, -1);
    }
    return $lines;
  }
  function unlines($lines, $nl = "\n") {
    return \hacklib_cast_as_boolean($lines) ? (join($lines, $nl).$nl) : "";
  }
  function is_empty($string) {
    return $string === "";
  }
  function sort(
    $strings,
    $caseInsensitive = false,
    $natural = false,
    $reverse = false
  ) {
    $flags = _sort_flags($caseInsensitive, $natural);
    if (\hacklib_cast_as_boolean($reverse)) {
      \rsort($strings, $flags);
    } else {
      \sort($strings, $flags);
    }
    return $strings;
  }
  function sort_map(
    $strings,
    $caseInsensitive = false,
    $natural = false,
    $reverse = false
  ) {
    $flags = _sort_flags($caseInsensitive, $natural);
    if (\hacklib_cast_as_boolean($reverse)) {
      \arsort($strings, $flags);
    } else {
      \asort($strings, $flags);
    }
    return $strings;
  }
  function sort_map_keys(
    $map,
    $caseInsensitive = false,
    $natural = false,
    $reverse = false
  ) {
    $flags = _sort_flags($caseInsensitive, $natural);
    if (\hacklib_cast_as_boolean($reverse)) {
      \krsort($map, $flags);
    } else {
      \ksort($map, $flags);
    }
    return $map;
  }
  function _sort_flags($caseInsensitive, $natural) {
    return
      (\hacklib_cast_as_boolean($natural) ? \SORT_NATURAL : \SORT_STRING) |
      (\hacklib_cast_as_boolean($caseInsensitive) ? \SORT_FLAG_CASE : 0);
  }
  function chunk($string, $size) {
    if ($size < 1) {
      throw new \Exception("Chunk size must be >= 1");
    }
    $ret = \str_split($string, $size);
    if (!\hacklib_cast_as_boolean(\is_array($ret))) {
      throw new \Exception("str_split() failed");
    }
    return $ret;
  }
  function join($strings, $delimiter = "") {
    return \implode($delimiter, $strings);
  }
  function replace($subject, $search, $replace, $caseInsensitive = false) {
    $count = 0;
    $result =
      \hacklib_cast_as_boolean($caseInsensitive)
        ? \str_ireplace($search, $replace, $subject, $count)
        : \str_replace($search, $replace, $subject, $count);
    if (!\hacklib_cast_as_boolean(\is_string($result))) {
      throw new \Exception("str_i?replace() failed");
    }
    return array($result, $count);
  }
  function splice($string, $offset, $length = 0x7FFFFFFF, $replacement = "") {
    return \substr_replace($string, $replacement, $offset, $length);
  }
  function slice($string, $offset, $length = 0x7FFFFFFF) {
    $ret = \substr($string, $offset, $length);
    return ($ret === false) ? "" : $ret;
  }
  function pad($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_BOTH);
  }
  function pad_left($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_LEFT);
  }
  function pad_right($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_RIGHT);
  }
  function repeat($string, $times) {
    return \str_repeat($string, $times);
  }
  function from_code($ascii) {
    if (($ascii < 0) || ($ascii >= 256)) {
      throw new \Exception(
        "ASCII character code must be >= 0 and < 256: ".$ascii
      );
    }
    return \chr($ascii);
  }
  function get_code_at($string, $offset = 0) {
    $length = length($string);
    if ($offset < 0) {
      $offset += $length;
    }
    if (($offset < 0) || ($offset >= $length)) {
      throw new \Exception(
        \sprintf(
          "Offset %d out of bounds in string \"%s\"",
          $offset,
          $string
        )
      );
    }
    return \ord($string[$offset]);
  }
  function compare($a, $b, $caseInsensitive = false, $natural = false) {
    $ret =
      \hacklib_cast_as_boolean($caseInsensitive)
        ? (\hacklib_cast_as_boolean($natural)
             ? \strnatcasecmp($a, $b)
             : \strcasecmp($a, $b))
        : (\hacklib_cast_as_boolean($natural)
             ? \strnatcmp($a, $b)
             : \strcmp($a, $b));
    return math\sign($ret);
  }
  function find($haystack, $needle, $offset = 0, $caseInsensitive = false) {
    $offset = _fix_offset($haystack, $offset);
    $ret =
      \hacklib_cast_as_boolean($caseInsensitive)
        ? \stripos($haystack, $needle, $offset)
        : \strpos($haystack, $needle, $offset);
    return ($ret === false) ? null : $ret;
  }
  function find_last(
    $haystack,
    $needle,
    $offset = 0,
    $caseInsensitive = false
  ) {
    $offset = _fix_offset($haystack, $offset);
    $ret =
      \hacklib_cast_as_boolean($caseInsensitive)
        ? \strripos($haystack, $needle, $offset)
        : \strrpos($haystack, $needle, $offset);
    return ($ret === false) ? null : $ret;
  }
  function count($haystack, $needle, $offset = 0) {
    $offset = _fix_offset($haystack, $offset);
    return \substr_count($haystack, $needle, $offset);
  }
  function contains($haystack, $needle, $offset = 0) {
    return find($haystack, $needle, $offset) !== null;
  }
  function length($string) {
    return \strlen($string);
  }
  function equal($a, $b, $caseInsensitive = false, $natural = false) {
    return \hacklib_equals(compare($a, $b, $caseInsensitive, $natural), 0);
  }
  function starts_with($string, $prefix) {
    return slice($string, 0, length($prefix)) === $prefix;
  }
  function ends_with($string, $suffix) {
    if ($suffix === "") {
      return true;
    }
    return slice($string, -length($suffix)) === $suffix;
  }
  function _fix_offset($string, $offset) {
    return _fix_bounds($offset, length($string));
  }
  function _fix_length($string, $offset, $length) {
    return
      _fix_bounds($length, length($string) - _fix_offset($string, $offset));
  }
  function _fix_bounds($num, $max) {
    if ($num < 0) {
      $num += $max;
    }
    if ($num < 0) {
      return 0;
    }
    if ($num > $max) {
      return $max;
    }
    return $num;
  }
}
