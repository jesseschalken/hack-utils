<?hh // strict

namespace HackUtils\str;

use HackUtils as utils;
use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;
use HackUtils\str;
use HackUtils\math;
use HackUtils\pair;

function to_hex(string $string): string {
  return \bin2hex($string);
}

function from_hex(string $string): string {
  $ret = \hex2bin($string);
  if (!\is_string($ret)) {
    throw new \Exception("Invalid hex string: $string");
  }
  return $ret;
}

function shuffle(string $string): string {
  return \str_shuffle($string);
}

function reverse(string $string): string {
  return \strrev($string);
}

function to_lower(string $string): string {
  return \strtolower($string);
}

function to_upper(string $string): string {
  return \strtoupper($string);
}

const string SPACE_CHARS = " \t\r\n\v\f";
const string TRIM_CHARS = " \t\r\n\v\x00";

function trim(string $string, string $chars = TRIM_CHARS): string {
  return \trim($string, $chars);
}

function trim_left(string $string, string $chars = TRIM_CHARS): string {
  return \ltrim($string, $chars);
}

function trim_right(string $string, string $chars = TRIM_CHARS): string {
  return \rtrim($string, $chars);
}

/**
 * Prefixes characters with a backslash, including backslashes.
 */
function escape_chars(string $s, string $chars): string {
  if ($s === '')
    return $s;
  $s = replace($s, '\\', '\\\\');
  $l = length($chars);
  for ($i = 0; $i < $l; $i++) {
    $c = $chars[$i];
    $s = replace($s, $c, '\\'.$c);
  }
  return $s;
}

function encode_list(array<string> $list): string {
  $r = '';
  foreach ($list as $x) {
    $r .= escape_chars($x, ';').';';
  }
  return $r;
}

function decode_list(string $s): array<string> {
  $r = [];
  $b = '';
  $e = false;
  $l = \strlen($s);
  for ($i = 0; $i < $l; $i++) {
    $c = $s[$i];
    if ($e) {
      $b .= $c;
      $e = false;
    } else if ($c === '\\') {
      $e = true;
    } else if ($c === ';') {
      $r[] = $b;
      $b = '';
    } else {
      $b .= $c;
    }
  }
  return $r;
}

function encode_map(array<arraykey, string> $map): string {
  $r = '';
  foreach ($map as $k => $v) {
    $k .= '';
    $r .= escape_chars($k, '=;').'=';
    $r .= escape_chars($v, '=;').';';
  }
  return $r;
}

function decode_map(string $s): array<arraykey, string> {
  $r = [];
  $k = null;
  $b = '';
  $l = \strlen($s);
  $e = false;
  for ($i = 0; $i < $l; $i++) {
    $c = $s[$i];
    if ($e) {
      $b .= $c;
      $e = false;
    } else if ($c === '\\') {
      $e = true;
    } else if ($c === '=') {
      // Make sure we are expecting a key
      if ($k !== null) {
        throw new \Exception('Double key');
      }
      $k = $b;
      $b = '';
    } else if ($c === ';') {
      // Make sure we are expecting a value
      if ($k === null) {
        throw new \Exception('Value without key');
      }
      $r[$k] = $b;
      $k = null;
      $b = '';
    } else {
      $b .= $c;
    }
  }
  return $r;
}

function split(
  string $string,
  string $delimiter = '',
  int $limit = 0x7FFFFFFF,
): array<string> {
  if ($limit < 1) {
    throw new \Exception("Limit must be >= 1");
  }
  // \explode() doesn't accept an empty delimiter
  if ($delimiter === '') {
    if ($string === '') {
      // The only case where we return an empty array is if both the delimiter
      // and string are empty, i.e. if they are tring to split the string
      // into characters and the string is empty.
      return [];
    }
    if ($limit == 1) {
      return [$string];
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

function split_at(string $string, int $offset): (string, string) {
  return tuple(slice($string, 0, $offset), slice($string, $offset));
}

/**
 * Split a string into lines terminated by \n or \r\n.
 * A final line terminator is optional.
 */
function lines(string $string): array<string> {
  $lines = split($string, "\n");
  // Remove a final \r at the end of any lines
  foreach ($lines as $i => $line) {
    if (slice($line, -1) === "\r") {
      $lines[$i] = slice($line, 0, -1);
    }
  }
  // Remove a final empty line
  if ($lines && $lines[vector\length($lines) - 1] === '') {
    $lines = vector\slice($lines, 0, -1);
  }
  return $lines;
}

/**
 * Join lines back together with the given line separator. A final
 * separator is included in the output.
 */
function unlines(array<string> $lines, string $nl = "\n"): string {
  return $lines ? join($lines, $nl).$nl : '';
}

function is_empty(string $string): bool {
  return $string === '';
}

function sort<T as arraykey>(
  array<T> $strings,
  bool $caseInsensitive = false,
  bool $natural = false,
  bool $reverse = false,
): array<T> {
  $flags = _sort_flags($caseInsensitive, $natural);
  if ($reverse) {
    \rsort($strings, $flags);
  } else {
    \sort($strings, $flags);
  }
  return $strings;
}

function sort_map<Tk, Tv as arraykey>(
  array<Tk, Tv> $strings,
  bool $caseInsensitive = false,
  bool $natural = false,
  bool $reverse = false,
): array<Tk, Tv> {
  $flags = _sort_flags($caseInsensitive, $natural);
  if ($reverse) {
    \arsort($strings, $flags);
  } else {
    \asort($strings, $flags);
  }
  return $strings;
}

function sort_map_keys<Tk as arraykey, Tv>(
  array<Tk, Tv> $map,
  bool $caseInsensitive = false,
  bool $natural = false,
  bool $reverse = false,
): array<Tk, Tv> {
  $flags = _sort_flags($caseInsensitive, $natural);
  if ($reverse) {
    \krsort($map, $flags);
  } else {
    \ksort($map, $flags);
  }
  return $map;
}

function _sort_flags(bool $caseInsensitive, bool $natural): int {
  return
    ($natural ? \SORT_NATURAL : \SORT_STRING) |
    ($caseInsensitive ? \SORT_FLAG_CASE : 0);
}

function chunk(string $string, int $size): array<string> {
  if ($size < 1) {
    throw new \Exception("Chunk size must be >= 1");
  }
  $ret = \str_split($string, $size);
  if (!\is_array($ret)) {
    throw new \Exception('str_split() failed');
  }
  return $ret;
}

function join(array<string> $strings, string $delimiter = ''): string {
  return \implode($delimiter, $strings);
}

function replace(
  string $subject,
  string $search,
  string $replace,
  bool $caseInsensitive = false,
): string {
  $count = 0;
  $result =
    $caseInsensitive
      ? \str_ireplace($search, $replace, $subject)
      : \str_replace($search, $replace, $subject);
  if (!\is_string($result)) {
    throw new \Exception('str_i?replace() failed');
  }
  return $result;
}

function replace_count(
  string $subject,
  string $search,
  string $replace,
  bool $caseInsensitive = false,
): (string, int) {
  $count = 0;
  $result =
    $caseInsensitive
      ? \str_ireplace($search, $replace, $subject, $count)
      : \str_replace($search, $replace, $subject, $count);
  if (!\is_string($result)) {
    throw new \Exception('str_i?replace() failed');
  }
  return tuple($result, $count);
}

function splice(
  string $string,
  int $offset,
  int $length = 0x7FFFFFFF,
  string $replacement = '',
): string {
  return \substr_replace($string, $replacement, $offset, $length);
}

function slice(string $string, int $offset, int $length = 0x7FFFFFFF): string {
  $ret = \substr($string, $offset, $length);
  // \substr() returns false "on failure".
  return $ret === false ? '' : $ret;
}

function pad(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_BOTH);
}

function pad_left(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_LEFT);
}

function pad_right(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_RIGHT);
}

function repeat(string $string, int $times): string {
  return \str_repeat($string, $times);
}

function from_code(int $ascii): string {
  if ($ascii < 0 || $ascii >= 256) {
    throw new \Exception(
      'ASCII character code must be >= 0 and < 256: '.$ascii,
    );
  }

  return \chr($ascii);
}

function get_code_at(string $string, int $offset = 0): int {
  $length = length($string);
  if ($offset < 0) {
    $offset += $length;
  }
  if ($offset < 0 || $offset >= $length) {
    throw new \Exception(
      \sprintf('Offset %d out of bounds in string "%s"', $offset, $string),
    );
  }
  return \ord($string[$offset]);
}

function compare(
  string $a,
  string $b,
  bool $caseInsensitive = false,
  bool $natural = false,
): int {
  $ret =
    $caseInsensitive
      ? ($natural ? \strnatcasecmp($a, $b) : \strcasecmp($a, $b))
      : ($natural ? \strnatcmp($a, $b) : \strcmp($a, $b));
  return math\sign($ret);
}

function find(
  string $haystack,
  string $needle,
  int $offset = 0,
  bool $caseInsensitive = false,
): ?int {
  $ret =
    $caseInsensitive
      ? \stripos($haystack, $needle, $offset)
      : \strpos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function find_last(
  string $haystack,
  string $needle,
  int $offset = 0,
  bool $caseInsensitive = false,
): ?int {
  $ret =
    $caseInsensitive
      ? \strripos($haystack, $needle, $offset)
      : \strrpos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function count(string $haystack, string $needle, int $offset = 0): int {
  return \substr_count($haystack, $needle, $offset);
}

function contains(string $haystack, string $needle, int $offset = 0): bool {
  return find($haystack, $needle, $offset) !== null;
}

function length(string $string): int {
  return \strlen($string);
}

function equal(
  string $a,
  string $b,
  bool $caseInsensitive = false,
  bool $natural = false,
): bool {
  return compare($a, $b, $caseInsensitive, $natural) == 0;
}

function starts_with(string $string, string $prefix): bool {
  return slice($string, 0, length($prefix)) === $prefix;
}

function ends_with(string $string, string $suffix): bool {
  if ($suffix === '') {
    return true;
  }
  return slice($string, -length($suffix)) === $suffix;
}
