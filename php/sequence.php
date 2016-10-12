<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function shuffle($list) {
    \shuffle($list);
    return $list;
  }
  function shuffle_string($string) {
    return \str_shuffle($string);
  }
  function reverse($list) {
    return \array_reverse($list, false);
  }
  function reverse_assoc($map) {
    return \array_reverse($map, true);
  }
  function reverse_string($string) {
    return \strrev($string);
  }
  function chunk($list, $size) {
    if ($size < 1) {
      throw new \Exception("Chunk size must be >= 1");
    }
    return \array_chunk($list, $size, false);
  }
  function chunk_assoc($map, $size) {
    if ($size < 1) {
      throw new \Exception("Chunk size must be >= 1");
    }
    return \array_chunk($map, $size, true);
  }
  function chunk_string($string, $size) {
    if ($size < 1) {
      throw new \Exception("Chunk size must be >= 1");
    }
    $ret = \str_split($string, $size);
    if (!\hacklib_cast_as_boolean(\is_array($ret))) {
      throw new \Exception("str_split() failed");
    }
    return $ret;
  }
  function repeat($value, $count) {
    return \array_fill(0, $count, $value);
  }
  function repeat_string($string, $count) {
    return \str_repeat($string, $count);
  }
  function slice($string, $offset, $length = null) {
    $ret = \substr($string, $offset, $length ?? 0x7FFFFFFF);
    return ($ret === false) ? "" : $ret;
  }
  function slice_array($list, $offset, $length = null) {
    return \array_slice($list, $offset, $length);
  }
  function slice_assoc($map, $offset, $length = null) {
    return \array_slice($map, $offset, $length, true);
  }
  function splice($string, $offset, $length = null, $replacement = "") {
    return
      \substr_replace($string, $replacement, $offset, $length ?? 0x7FFFFFFF);
  }
  function splice_array(
    $list,
    $offset,
    $length = null,
    $replacement = array()
  ) {
    $ret = \array_splice($list, $offset, $length, $replacement);
    return array($list, $ret);
  }
  function find($haystack, $needle, $offset = 0, $caseInsensitive = false) {
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
    $ret =
      \hacklib_cast_as_boolean($caseInsensitive)
        ? \strripos($haystack, $needle, $offset)
        : \strrpos($haystack, $needle, $offset);
    return ($ret === false) ? null : $ret;
  }
  function find_count($haystack, $needle, $offset = 0) {
    return \substr_count($haystack, $needle, $offset);
  }
  function contains($haystack, $needle, $offset = 0) {
    return find($haystack, $needle, $offset) !== null;
  }
  function length($string) {
    return \strlen($string);
  }
  function count($map) {
    return \count($map);
  }
  function size($map) {
    return \count($map);
  }
  function find_key($array, $value) {
    $ret = \array_search($array, $value, true);
    return ($ret === false) ? new_null() : $ret;
  }
  function find_keys($array, $value) {
    return \array_keys($array, $value, true);
  }
  function find_last_key($array, $value) {
    \end($array);
    while (!\hacklib_cast_as_boolean(\is_null($key = \key($array)))) {
      if (\current($array) === $value) {
        return $key;
      }
      \prev($array);
    }
    return new_null();
  }
  function in($value, $map) {
    return \in_array($value, $map, true);
  }
}
