<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
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
  function lines($string) {
    $lines = split($string, "\n");
    foreach ($lines as $i => $line) {
      if (slice($line, -1) === "\r") {
        $lines[$i] = slice($line, 0, -1);
      }
    }
    if (\hacklib_cast_as_boolean($lines) &&
        ($lines[count($lines) - 1] === "")) {
      $lines = slice_array($lines, 0, -1);
    }
    return $lines;
  }
  function unlines($lines, $nl = "\n") {
    return \hacklib_cast_as_boolean($lines) ? (join($lines, $nl).$nl) : "";
  }
  function join($strings, $delimiter = "") {
    return \implode($delimiter, $strings);
  }
  function replace($subject, $search, $replace, $ci = false) {
    $count = 0;
    $result =
      \hacklib_cast_as_boolean($ci)
        ? \str_ireplace($search, $replace, $subject)
        : \str_replace($search, $replace, $subject);
    if (!\hacklib_cast_as_boolean(\is_string($result))) {
      throw new \Exception("str_i?replace() failed");
    }
    return $result;
  }
  function replace_count($subject, $search, $replace, $ci = false) {
    $count = 0;
    $result =
      \hacklib_cast_as_boolean($ci)
        ? \str_ireplace($search, $replace, $subject, $count)
        : \str_replace($search, $replace, $subject, $count);
    if (!\hacklib_cast_as_boolean(\is_string($result))) {
      throw new \Exception("str_i?replace() failed");
    }
    return array($result, $count);
  }
  function pad($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_BOTH);
  }
  function pad_array($list, $size, $value) {
    return \array_pad($list, $size, $value);
  }
  function pad_left($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_LEFT);
  }
  function pad_right($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_RIGHT);
  }
  function from_char_code($ascii) {
    if (($ascii < 0) || ($ascii >= 256)) {
      throw new \Exception(
        "ASCII character code must be >= 0 and < 256: ".$ascii
      );
    }
    return \chr($ascii);
  }
  function char_at($s, $i = 0) {
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
  function char_code_at($string, $offset = 0) {
    return \ord(char_at($string, $offset));
  }
  function str_cmp($a, $b, $ci = false, $natural = false) {
    $ret =
      \hacklib_cast_as_boolean($ci)
        ? (\hacklib_cast_as_boolean($natural)
             ? \strnatcasecmp($a, $b)
             : \strcasecmp($a, $b))
        : (\hacklib_cast_as_boolean($natural)
             ? \strnatcmp($a, $b)
             : \strcmp($a, $b));
    return sign($ret);
  }
  function str_eq($a, $b, $ci = false, $natural = false) {
    return \hacklib_equals(str_cmp($a, $b, $ci, $natural), 0);
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
}
