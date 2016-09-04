<?hh // strict

namespace HackUtils\str;

use HackUtils\list;
use HackUtils\str;

function to_hex(string $string): string {
  return \bin2hex($string);
}

function from_hex(string $string): string {
  $ret = \hex2bin($string);
  if (!\is_string($string)) {
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
  int $limit = \PHP_INT_MAX,
): list<string> {
  // \explode() treats limit=0 as limit=1
  if (!$limit) {
    return [];
  }
  // \explode() doesn't accept an empty delimiter
  if ($delimiter === '') {
    $ret = \str_split($string);
    // Try to avoid the overhead of \array_slice() if the limit wasn't given.
    if ($limit != \PHP_INT_MAX) {
      $ret = \array_slice($ret, $limit);
    }
    return $ret;
  }
  return \explode($delimiter, $string, $limit);
}

function chunk(string $string, int $size): list<string> {
  if ($size < 1) {
    throw new \Exception("Chunk size must be >= 1");
  }
  $ret = \str_split($string, $size);
  if ($ret === false) {
    throw new \Exception('str_split() failed');
  }
  return $ret;
}

function join(list<string> $strings, string $delimiter = ''): string {
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
    throw new \Exception('str_replace() failed');
  }
  return tuple($result, $count);
}

function splice(
  string $string,
  int $offset,
  int $length = \PHP_INT_MAX,
  string $replacement = '',
): string {
  return \substr_replace($string, $replacement, $offset, $length);
}

function slice(
  string $string,
  int $offset,
  int $length = \PHP_INT_MAX,
): string {
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
  return \chr($ascii);
}

function ord(string $char): int {
  if ($char !== '') {
    throw new \Exception('String given to ord() must not be empty');
  }
  return \ord($char);
}

function cmp(
  string $a,
  string $b,
  int $offset = 0,
  int $length = \PHP_INT_MAX,
): int {
  if ($length < 0) {
    throw new \Exception("Length must be non-negative: $length");
  }
  $ret = \substr_compare($a, $b, $offset, $length, false);
  return ($ret == 0 ? 0 : ($ret < 0 ? -1 : 1));
}

function icmp(
  string $a,
  string $b,
  int $offset = 0,
  int $length = \PHP_INT_MAX,
): int {
  if ($length < 0) {
    throw new \Exception("Length must be non-negative: $length");
  }
  $ret = \substr_compare($a, $b, $offset, $length, true);
  return ($ret == 0 ? 0 : ($ret < 0 ? -1 : 1));
}

function find(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \strpos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function ifind(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \stripos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function rfind(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \strrpos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function irfind(string $haystack, string $needle, int $offset = 0): ?int {
  $ret = \strripos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function count(
  string $haystack,
  string $needle,
  int $offset = 0,
  int $length = \PHP_INT_MAX,
): int {
  return \substr_count($haystack, $needle, $offset, $length);
}

function contains(string $haystack, string $needle, int $offset = 0): bool {
  return find($haystack, $needle, $offset) !== null;
}

function len(string $string): int {
  return \strlen($string);
}

function eq(
  string $a,
  string $b,
  int $offset = 0,
  int $length = \PHP_INT_MAX,
): bool {
  return cmp($a, $b, $offset, $length) === 0;
}

function ieq(
  string $a,
  string $b,
  int $offset = 0,
  int $length = \PHP_INT_MAX,
): bool {
  return icmp($a, $b, $offset, $length) === 0;
}

function starts_with(string $string, string $prefix): bool {
  return eq($string, $prefix, 0, len($prefix));
}

function ends_with(string $string, string $suffix): bool {
  $offset = len($string) - len($suffix);
  // Suffix is longer than the test string
  // We have to check this because offset < 0 has special meaning to eq()
  if ($offset < 0) {
    return false;
  }
  return eq($string, $suffix, $offset);
}
