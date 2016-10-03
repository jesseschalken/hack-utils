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

function escape(string $string, string $chars): string {
  if ($string === '')
    return $string;
  $string = pair\fst(replace($string, '\\', '\\\\'));
  $length = length($chars);
  for ($i = 0; $i < $length; $i++) {
    $char = $chars[$i];
    $string = pair\fst(replace($string, $char, '\\'.$char));
  }
  return $string;
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
  $offset = utils\fix_offset($offset, length($string));
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
  $offset = utils\fix_offset($offset, length($haystack));
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
  $offset = utils\fix_offset($offset, length($haystack));
  $ret =
    $caseInsensitive
      ? \strripos($haystack, $needle, $offset)
      : \strrpos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function count(string $haystack, string $needle, int $offset = 0): int {
  $offset = utils\fix_offset($offset, length($haystack));
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
