<?hh // strict

namespace HackUtils\str;

use HackUtils\vector;
use HackUtils\str;

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

function lower(string $string): string {
  return \strtolower($string);
}

function upper(string $string): string {
  return \strtoupper($string);
}

const string SPACE_CHARS = " \t\r\n\v\f";
const string TRIM_CHARS = " \t\r\n\v\x00";

function trim(string $string, string $chars = TRIM_CHARS): string {
  return \trim($string, $chars);
}

function ltrim(string $string, string $chars = TRIM_CHARS): string {
  return \ltrim($string, $chars);
}

function rtrim(string $string, string $chars = TRIM_CHARS): string {
  return \rtrim($string, $chars);
}

function split(
  string $string,
  string $delimiter = '',
  int $limit = 0x7FFFFFFF,
): vector<string> {
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
    if (len($string) > $limit) {
      $ret = \str_split(slice($string, 0, $limit - 1));
      $ret[] = slice($string, $limit - 1);
      return $ret;
    }
    return \str_split($string);
  }
  return \explode($delimiter, $string, $limit);
}

function chunk(string $string, int $size): vector<string> {
  if ($size < 1) {
    throw new \Exception("Chunk size must be >= 1");
  }
  $ret = \str_split($string, $size);
  if ($ret === false) {
    throw new \Exception('str_split() failed');
  }
  return $ret;
}

function join(vector<string> $strings, string $delimiter = ''): string {
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

function lpad(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_LEFT);
}

function rpad(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_RIGHT);
}

function repeat(string $string, int $times): string {
  return \str_repeat($string, $times);
}

function chr(int $ascii): string {
  if ($ascii < 0 || $ascii >= 256) {
    throw new \Exception('ASCII character code out of bounds: '.$ascii);
  }
  return \chr($ascii);
}

function ord(string $char): int {
  if ($char === '') {
    throw new \Exception('String given to ord() must not be empty');
  }
  return \ord($char);
}

function cmp(string $a, string $b): int {
  $ret = \strcmp($a, $b);
  return $ret > 0 ? 1 : ($ret < 0 ? -1 : 0);
}

function icmp(string $a, string $b): int {
  $ret = \strcasecmp($a, $b);
  return $ret > 0 ? 1 : ($ret < 0 ? -1 : 0);
}

function find(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \strpos($haystack, $needle, _fix_offset($haystack, $offset));
  return $ret === false ? null : $ret;
}

function ifind(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \stripos($haystack, $needle, _fix_offset($haystack, $offset));
  return $ret === false ? null : $ret;
}

function rfind(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \strrpos($haystack, $needle, _fix_offset($haystack, $offset));
  return $ret === false ? null : $ret;
}

function irfind(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \strripos($haystack, $needle, _fix_offset($haystack, $offset));
  return $ret === false ? null : $ret;
}

function count(string $haystack, string $needle, int $offset = 0): int {
  return \substr_count($haystack, $needle, _fix_offset($haystack, $offset));
}

function contains(string $haystack, string $needle, int $offset = 0): bool {
  return find($haystack, $needle, $offset) !== null;
}

function len(string $string): int {
  return \strlen($string);
}

function eq(string $a, string $b): bool {
  return cmp($a, $b) === 0;
}

function ieq(string $a, string $b): bool {
  return icmp($a, $b) === 0;
}

function starts_with(string $string, string $prefix): bool {
  return slice($string, 0, len($prefix)) === $prefix;
}

function ends_with(string $string, string $suffix): bool {
  if ($suffix === '') {
    return true;
  }
  return slice($string, -len($suffix)) === $suffix;
}

function _fix_offset(string $string, int $offset): int {
  return _fix_bounds($offset, len($string));
}

function _fix_length(string $string, int $offset, int $length): int {
  return _fix_bounds($length, len($string) - _fix_offset($string, $offset));
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
