<?hh // strict

namespace HackUtils;

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

/**
 * Split a string into lines terminated by \n or \r\n.
 * A final line terminator is optional.
 */
function split_lines(string $string): array<string> {
  $lines = split($string, "\n");
  // Remove a final \r at the end of any lines
  foreach ($lines as $i => $line) {
    if (slice($line, -1) === "\r") {
      $lines[$i] = slice($line, 0, -1);
    }
  }
  // Remove a final empty line
  if ($lines && $lines[count($lines) - 1] === '') {
    $lines = slice_array($lines, 0, -1);
  }
  return $lines;
}

function join(array<string> $strings, string $delimiter = ''): string {
  return \implode($delimiter, $strings);
}

/**
 * Join lines back together with the given line separator. A final
 * separator is included in the output.
 */
function join_lines(array<string> $lines, string $nl = "\n"): string {
  return $lines ? join($lines, $nl).$nl : '';
}

function replace(
  string $subject,
  string $search,
  string $replace,
  bool $ci = false,
): string {
  $count = 0;
  $result =
    $ci
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
  bool $ci = false,
): (string, int) {
  $count = 0;
  $result =
    $ci
      ? \str_ireplace($search, $replace, $subject, $count)
      : \str_replace($search, $replace, $subject, $count);
  if (!\is_string($result)) {
    throw new \Exception('str_i?replace() failed');
  }
  return tuple($result, $count);
}

function pad(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_BOTH);
}

function pad_array<T>(array<T> $list, int $size, T $value): array<T> {
  return \array_pad($list, $size, $value);
}

function pad_left(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_LEFT);
}

function pad_right(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_RIGHT);
}

function from_char_code(int $ascii): string {
  if ($ascii < 0 || $ascii >= 256) {
    throw new \Exception(
      'ASCII character code must be >= 0 and < 256: '.$ascii,
    );
  }

  return \chr($ascii);
}

function char_at(string $s, int $i = 0): string {
  $l = \strlen($s);
  // Allow caller to specify negative offsets for characters from the end of
  // the string
  if ($i < 0) {
    $i += $l;
  }
  if ($i < 0 || $i >= $l) {
    throw new \Exception(
      "String offset $i out of bounds in string of length $l",
    );
  }
  return $s[$i];
}

function char_code_at(string $string, int $offset = 0): int {
  return \ord(char_at($string, $offset));
}

function str_cmp(
  string $a,
  string $b,
  bool $ci = false,
  bool $natural = false,
): int {
  $ret =
    $ci
      ? ($natural ? \strnatcasecmp($a, $b) : \strcasecmp($a, $b))
      : ($natural ? \strnatcmp($a, $b) : \strcmp($a, $b));
  return sign($ret);
}

function str_eq(
  string $a,
  string $b,
  bool $ci = false,
  bool $natural = false,
): bool {
  return str_cmp($a, $b, $ci, $natural) == 0;
}

function starts_with(string $string, string $prefix): bool {
  return slice($string, 0, length($prefix)) === $prefix;
}

function ends_with(string $string, string $suffix): bool {
  $length = length($suffix);
  return $length ? slice($string, -$length) === $suffix : true;
}
