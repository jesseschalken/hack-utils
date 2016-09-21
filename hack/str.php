<?hh // strict

namespace HackUtils\str;

use HackUtils\vector;
use HackUtils\str;
use HackUtils\math;

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
  $offset = _fix_offset($string, $offset);
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

function is_empty(string $string): bool {
  return $string === '';
}

function chunk(string $string, int $size): array<string> {
  if ($size < 1) {
    throw new \Exception("Chunk size must be >= 1");
  }
  $ret = \str_split($string, $size);
  if ($ret === false) {
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
): (string, int) {
  $count = 0;
  $result = \str_replace($search, $replace, $subject, $count);
  if (!\is_string($result)) {
    throw new \Exception('str_replace() failed');
  }
  return tuple($result, $count);
}

function ireplace(
  string $subject,
  string $search,
  string $replace,
): (string, int) {
  $count = 0;
  $result = \str_ireplace($search, $replace, $subject, $count);
  if (!\is_string($result)) {
    throw new \Exception('str_ireplace() failed');
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
  if ($ret === false) {
    return '';
  }
  return $ret;
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
    $length += $length;
  }
  if ($offset < 0 || $offset >= $length) {
    throw new \Exception(
      \sprintf('Offset %d out of bounds in string "%s"', $offset, $string),
    );
  }
  return \ord($string[$offset]);
}

function compare(string $a, string $b): int {
  return math\sign(\strcmp($a, $b));
}

function icompare(string $a, string $b): int {
  return math\sign(\strcasecmp($a, $b));
}

function find(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \strpos($haystack, $needle, _fix_offset($haystack, $offset));
  return $ret === false ? null : $ret;
}

function ifind(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \stripos($haystack, $needle, _fix_offset($haystack, $offset));
  return $ret === false ? null : $ret;
}

function find_last(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \strrpos($haystack, $needle, _fix_offset($haystack, $offset));
  return $ret === false ? null : $ret;
}

function ifind_last(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \strripos($haystack, $needle, _fix_offset($haystack, $offset));
  return $ret === false ? null : $ret;
}

function count(string $haystack, string $needle, int $offset = 0): int {
  return \substr_count($haystack, $needle, _fix_offset($haystack, $offset));
}

function contains(string $haystack, string $needle, int $offset = 0): bool {
  return find($haystack, $needle, $offset) !== null;
}

function length(string $string): int {
  return \strlen($string);
}

function equal(string $a, string $b): bool {
  return compare($a, $b) === 0;
}

function iequal(string $a, string $b): bool {
  return icompare($a, $b) === 0;
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

function _fix_offset(string $string, int $offset): int {
  return _fix_bounds($offset, length($string));
}

function _fix_length(string $string, int $offset, int $length): int {
  return
    _fix_bounds($length, length($string) - _fix_offset($string, $offset));
}

function _fix_bounds(int $num, int $max): int {
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
