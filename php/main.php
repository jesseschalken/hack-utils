<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function new_null() {
    return null;
  }
  function null_throws($value, $message = "Unexpected null") {
    return ($value === null) ? throw_(new \Exception($message)) : $value;
  }
  function throw_($e) {
    throw $e;
  }
  function if_null($x, $y) {
    return ($x === null) ? $y : $x;
  }
  function fst($t) {
    return $t[0];
  }
  function snd($t) {
    return $t[1];
  }
  interface Gettable {
    public function get();
  }
  interface Settable {
    public function set($value);
  }
  final class Ref implements Gettable, Settable {
    private $value;
    public function __construct($value) {
      $this->value = $value;
    }
    public function get() {
      return $this->value;
    }
    public function set($value) {
      $this->value = $value;
    }
  }
  function is_vector($x) {
    $i = 0;
    foreach ($x as $k => $v) {
      if ($k !== ($i++)) {
        return false;
      }
    }
    return true;
  }
  function concat($a, $b) {
    return \array_merge($a, $b);
  }
  function concat_all($vectors) {
    return
      \hacklib_cast_as_boolean($vectors)
        ? \call_user_func_array("array_merge", $vectors)
        : array();
  }
  function push($v, $x) {
    \array_push($v, $x);
    return $v;
  }
  function pop($v) {
    _check_empty($v, "remove last element");
    $x = \array_pop($v);
    return array($v, $x);
  }
  function unshift($x, $v) {
    \array_unshift($v, $x);
    return $v;
  }
  function shift($v) {
    _check_empty($v, "remove first element");
    $x = \array_shift($v);
    return array($x, $v);
  }
  function _check_empty($a, $op) {
    if (!\hacklib_cast_as_boolean($a)) {
      throw new \Exception("Cannot ".$op.": Array is empty");
    }
  }
  function range($start, $end, $step = 1) {
    return \range($start, $end, $step);
  }
  function filter($list, $f) {
    $ret = filter_assoc($list, $f);
    return
      \hacklib_not_equals(count($ret), count($list)) ? values($ret) : $list;
  }
  function filter_assoc($map, $f) {
    return \array_filter($map, $f);
  }
  function map($list, $f) {
    return \array_map($f, $list);
  }
  function map_assoc($map, $f) {
    return \array_map($f, $map);
  }
  function reduce($list, $f, $initial) {
    return \array_reduce($list, $f, $initial);
  }
  function reduce_right($list, $f, $value) {
    \end($list);
    while (!\hacklib_cast_as_boolean(\is_null($key = \key($list)))) {
      $value = $f($value, \current($list));
      \prev($list);
    }
    return $value;
  }
  function group_by($a, $f) {
    $res = array();
    foreach ($a as $v) {
      $res[$f($v)][] = $v;
    }
    return $res;
  }
  function any($a, $f) {
    foreach ($a as $x) {
      if (\hacklib_cast_as_boolean($f($x))) {
        return true;
      }
    }
    return false;
  }
  function all($a, $f) {
    foreach ($a as $x) {
      if (!\hacklib_cast_as_boolean($f($x))) {
        return false;
      }
    }
    return true;
  }
  function keys_to_lower($array) {
    return \array_change_key_case($array, \CASE_LOWER);
  }
  function keys_to_uppper($array) {
    return \array_change_key_case($array, \CASE_UPPER);
  }
  function to_pairs($map) {
    $r = array();
    foreach ($map as $k => $v) {
      $r[] = array($k, $v);
    }
    return $r;
  }
  function from_pairs($pairs) {
    $r = array();
    foreach ($pairs as $p) {
      $r[$p[0]] = $p[1];
    }
    return $r;
  }
  function get_key($map, $key) {
    $res = $map[$key];
    if (($res === null) &&
        (!\hacklib_cast_as_boolean(key_exists($map, $key)))) {
      throw new \Exception("Key '".$key."' does not exist in map");
    }
    return $res;
  }
  function set_key($map, $key, $val) {
    $map[$key] = $val;
    return $map;
  }
  function get_key_or_null($map, $key) {
    return $map[$key] ?? new_null();
  }
  function get_key_or_default($map, $key, $default) {
    return
      \hacklib_cast_as_boolean(key_exists($map, $key))
        ? $map[$key]
        : $default;
  }
  function key_exists($map, $key) {
    return \array_key_exists($key, $map);
  }
  function get_offset($v, $i) {
    $l = \count($v);
    if ($i < 0) {
      $i += $l;
    }
    if (($i < 0) || ($i >= $l)) {
      throw new \Exception(
        "Index ".$i." out of bounds in array of length ".$l
      );
    }
    return $v[$i];
  }
  function set_offset($v, $i, $x) {
    $l = \count($v);
    if ($i < 0) {
      $i += $l;
    }
    if (($i < 0) || ($i >= $l)) {
      throw new \Exception(
        "Index ".$i." out of bounds in array of length ".$l
      );
    }
    $v[$i] = $x;
    return $v;
  }
  function fixkey($key) {
    return $key."";
  }
  function fixkeys($keys) {
    return map(
      $keys,
      function($key) {
        return $key."";
      }
    );
  }
  function column($maps, $key) {
    return \array_column($maps, $key);
  }
  function combine($keys, $values) {
    return \array_combine($keys, $values);
  }
  function separate($map) {
    $ks = array();
    $vs = array();
    foreach ($map as $k => $v) {
      $ks[] = $k;
      $vs[] = $v;
    }
    return array($ks, $vs);
  }
  function from_keys($keys, $value) {
    return \array_fill_keys($keys, $value);
  }
  function flip($map) {
    return \array_flip($map);
  }
  function flip_count($values) {
    return \array_count_values($values);
  }
  function keys($map) {
    return \array_keys($map);
  }
  function keys_strings($map) {
    return map(
      keys($map),
      function($k) {
        return "".$k;
      }
    );
  }
  function values($map) {
    return \array_values($map);
  }
  function union_keys($a, $b) {
    return \array_replace($a, $b);
  }
  function union_keys_all($maps) {
    return \call_user_func_array("array_replace", $maps);
  }
  function intersect($a, $b) {
    return \array_values(\array_intersect($a, $b));
  }
  function intersect_keys($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function diff($a, $b) {
    return \array_values(\array_diff($a, $b));
  }
  function diff_keys($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function select($map, $keys) {
    $ret = array();
    foreach ($keys as $key) {
      $ret[] = $map[$key];
    }
    return $ret;
  }
  function zip($a, $b) {
    $r = array();
    $l = min(count($a), count($b));
    for ($i = 0; $i < $l; $i++) {
      $r[] = array($a[$i], $b[$i]);
    }
    return $r;
  }
  function zip_assoc($a, $b) {
    $ret = array();
    foreach ($a as $k => $v) {
      if (\hacklib_cast_as_boolean(key_exists($b, $k))) {
        $ret[$k] = array($v, $b[$k]);
      }
    }
    return $ret;
  }
  function unzip($x) {
    $a = array();
    $b = array();
    foreach ($x as $p) {
      $a[] = $p[0];
      $b[] = $p[1];
    }
    return array($a, $b);
  }
  function unzip_assoc($map) {
    $a = array();
    $b = array();
    foreach ($map as $k => $v) {
      $a[$k] = $v[0];
      $b[$k] = $v[1];
    }
    return array($a, $b);
  }
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
  function count($array) {
    return \count($array);
  }
  function size($array) {
    return \count($array);
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
  function in($value, $array) {
    return \in_array($value, $array, true);
  }
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
  function split_lines($string) {
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
  function join($strings, $delimiter = "") {
    return \implode($delimiter, $strings);
  }
  function join_lines($lines, $nl = "\n") {
    return \hacklib_cast_as_boolean($lines) ? (join($lines, $nl).$nl) : "";
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
    $length = length($suffix);
    return
      \hacklib_cast_as_boolean($length)
        ? (slice($string, -$length) === $suffix)
        : true;
  }
}
