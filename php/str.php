<?php
namespace HackUtils\str {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\list;
  use \HackUtils\str;
  function to_hex($string) {
    return \bin2hex($string);
  }
  function from_hex($string) {
    $ret = \hex2bin($string);
    if (!\hacklib_cast_as_boolean(\is_string($string))) {
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
  function lower($string) {
    return \strtolower($string);
  }
  function upper($string) {
    return \strtoupper($string);
  }
  const SPACE_CHARS = " \t\r\n\013\014";
  const TRIM_CHARS = " \t\r\n\013\000";
  function trim($string, $chars = TRIM_CHARS) {
    return \trim($string, $chars);
  }
  function ltrim($string, $chars = TRIM_CHARS) {
    return \ltrim($string, $chars);
  }
  function rtrim($string, $chars = TRIM_CHARS) {
    return \rtrim($string, $chars);
  }
  function split($string, $delimiter = "", $limit = \PHP_INT_MAX) {
    if (!\hacklib_cast_as_boolean($limit)) {
      return array();
    }
    if ($delimiter === "") {
      $ret = \str_split($string);
      if (\hacklib_not_equals($limit, \PHP_INT_MAX)) {
        $ret = \array_slice($ret, $limit);
      }
      return $ret;
    }
    return \explode($delimiter, $string, $limit);
  }
  function chunk($string, $size) {
    if ($size < 1) {
      throw new \Exception("Chunk size must be >= 1");
    }
    $ret = \str_split($string, $size);
    if ($ret === false) {
      throw new \Exception("str_split() failed");
    }
    return $ret;
  }
  function join($strings, $delimiter = "") {
    return \implode($delimiter, $strings);
  }
  function replace($subject, $search, $replace) {
    $count = 0;
    $result = \str_replace($search, $replace, $subject, $count);
    if (!\hacklib_cast_as_boolean(\is_string($result))) {
      throw new \Exception("str_replace() failed");
    }
    return array($result, $count);
  }
  function ireplace($subject, $search, $replace) {
    $count = 0;
    $result = \str_ireplace($search, $replace, $subject, $count);
    if (!\hacklib_cast_as_boolean(\is_string($result))) {
      throw new \Exception("str_replace() failed");
    }
    return array($result, $count);
  }
  function splice(
    $string,
    $offset,
    $length = \PHP_INT_MAX,
    $replacement = ""
  ) {
    return \substr_replace($string, $replacement, $offset, $length);
  }
  function slice($string, $offset, $length = \PHP_INT_MAX) {
    $ret = \substr($string, $offset, $length);
    if ($ret === false) {
      return "";
    }
    return $ret;
  }
  function pad($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_BOTH);
  }
  function lpad($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_LEFT);
  }
  function rpad($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_RIGHT);
  }
  function repeat($string, $times) {
    return \str_repeat($string, $times);
  }
  function chr($ascii) {
    return \chr($ascii);
  }
  function ord($char) {
    if ($char !== "") {
      throw new \Exception("String given to ord() must not be empty");
    }
    return \ord($char);
  }
  function cmp($a, $b, $offset = 0, $length = \PHP_INT_MAX) {
    if ($length < 0) {
      throw new \Exception("Length must be non-negative: ".$length);
    }
    $ret = \substr_compare($a, $b, $offset, $length, false);
    return \hacklib_equals($ret, 0) ? 0 : (($ret < 0) ? (-1) : 1);
  }
  function icmp($a, $b, $offset = 0, $length = \PHP_INT_MAX) {
    if ($length < 0) {
      throw new \Exception("Length must be non-negative: ".$length);
    }
    $ret = \substr_compare($a, $b, $offset, $length, true);
    return \hacklib_equals($ret, 0) ? 0 : (($ret < 0) ? (-1) : 1);
  }
  function find($haystack, $needle, $offset = 0) {
    $ret = \strpos($haystack, $needle, $offset);
    return ($ret === false) ? null : $ret;
  }
  function ifind($haystack, $needle, $offset = 0) {
    $ret = \stripos($haystack, $needle, $offset);
    return ($ret === false) ? null : $ret;
  }
  function rfind($haystack, $needle, $offset = 0) {
    $ret = \strrpos($haystack, $needle, $offset);
    return ($ret === false) ? null : $ret;
  }
  function irfind($haystack, $needle, $offset = 0) {
    $ret = \strripos($haystack, $needle, $offset);
    return ($ret === false) ? null : $ret;
  }
  function count($haystack, $needle, $offset = 0, $length = \PHP_INT_MAX) {
    return \substr_count($haystack, $needle, $offset, $length);
  }
  function contains($haystack, $needle, $offset = 0) {
    return find($haystack, $needle, $offset) !== null;
  }
  function len($string) {
    return \strlen($string);
  }
  function eq($a, $b, $offset = 0, $length = \PHP_INT_MAX) {
    return cmp($a, $b, $offset, $length) === 0;
  }
  function ieq($a, $b, $offset = 0, $length = \PHP_INT_MAX) {
    return icmp($a, $b, $offset, $length) === 0;
  }
  function starts_with($string, $prefix) {
    return eq($string, $prefix, 0, len($prefix));
  }
  function ends_with($string, $suffix) {
    $offset = len($string) - len($suffix);
    if ($offset < 0) {
      return false;
    }
    return eq($string, $suffix, $offset);
  }
}
