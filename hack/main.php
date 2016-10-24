<?hh // strict

namespace HackUtils;

type datetime = DateTime\datetime;
type timezone = DateTime\timezone;

/**
 * The Hack typechecker reports "null" as "Partially type checked code.
 * Consider adding type annotations". To avoid that, you can replace it with
 * a call to this function.
 */
function new_null<T>(): ?T {
  return null;
}

/**
 * Convert a nullable value into a non-nullable value, throwing an exception
 * in the case of null.
 */
function null_throws<T>(?T $value, string $message = "Unexpected null"): T {
  return $value === null ? throw_(new \Exception($message)) : $value;
}

/**
 * Throw an exception in the context of an expression.
 */
function throw_<T>(\Exception $e): T {
  throw $e;
}

function if_null<T>(?T $x, T $y): T {
  return $x === null ? $y : $x;
}

function fst<T>((T, mixed) $t): T {
  return $t[0];
}

function snd<T>((mixed, T) $t): T {
  return $t[1];
}

interface Gettable<+T> {
  public function get(): T;
}

interface Settable<-T> {
  public function set(T $value): void;
}

/**
 * Simple container for a value of a given type. Useful to replace PHP's
 * built in references, which are not supported in Hack.
 */
final class Ref<T> implements Gettable<T>, Settable<T> {
  public function __construct(private T $value) {}

  public function get(): T {
    return $this->value;
  }

  public function set(T $value): void {
    $this->value = $value;
  }
}

function is_vector(array<mixed, mixed> $x): bool {
  $i = 0;
  foreach ($x as $k => $v) {
    if ($k !== $i++) {
      return false;
    }
  }
  return true;
}

function concat<T>(array<T> $a, array<T> $b): array<T> {
  return \array_merge($a, $b);
}

function concat_all<T>(array<array<T>> $vectors): array<T> {
  return $vectors ? \call_user_func_array('array_merge', $vectors) : [];
}

function push<T>(array<T> $v, T $x): array<T> {
  \array_push($v, $x);
  return $v;
}

function pop<T>(array<T> $v): (array<T>, T) {
  if (!$v) {
    throw new \Exception('Cannot pop last element: Array is empty');
  }
  $x = \array_pop($v);
  return tuple($v, $x);
}

function unshift<T>(T $x, array<T> $v): array<T> {
  \array_unshift($v, $x);
  return $v;
}

function shift<T>(array<T> $v): (T, array<T>) {
  if (!$v) {
    throw new \Exception('Cannot shift first element: Array is empty');
  }
  $x = \array_shift($v);
  return tuple($x, $v);
}

function range(int $start, int $end, int $step = 1): array<int> {
  return \range($start, $end, $step);
}

function filter<T>(array<T> $array, (function(T): bool) $f): array<T> {
  $ret = filter_assoc($array, $f);
  // array_filter() preserves keys, so if some elements were removed,
  // renumber keys 0,1...N.
  return count($ret) != count($array) ? values($ret) : $array;
}

function filter_assoc<Tk, Tv>(
  array<Tk, Tv> $array,
  (function(Tv): bool) $f,
): array<Tk, Tv> {
  return \array_filter($array, $f);
}

function map<Tin, Tout>(
  array<Tin> $array,
  (function(Tin): Tout) $f,
): array<Tout> {
  return \array_map($f, $array);
}

function map_assoc<Tk, Tv1, Tv2>(
  array<Tk, Tv1> $array,
  (function(Tv1): Tv2) $f,
): array<Tk, Tv2> {
  return \array_map($f, $array);
}

function reduce<Tin, Tout>(
  array<arraykey, Tin> $array,
  (function(Tout, Tin): Tout) $f,
  Tout $initial,
): Tout {
  return \array_reduce($array, $f, $initial);
}

function reduce_right<Tin, Tout>(
  array<arraykey, Tin> $array,
  (function(Tout, Tin): Tout) $f,
  Tout $value,
): Tout {
  // Messy, but the easiest way of iterating through an array in reverse
  // without creating a copy.
  \end($array);
  while (!\is_null($key = \key($array))) {
    $value = $f($value, \current($array));
    \prev($array);
  }
  return $value;
}

function group_by<Tk as arraykey, Tv>(
  array<mixed, Tv> $a,
  (function(Tv): Tk) $f,
): array<Tk, array<Tv>> {
  $res = [];
  foreach ($a as $v) {
    $res[$f($v)][] = $v;
  }
  return $res;
}

function any<T>(array<mixed, T> $a, (function(T): bool) $f): bool {
  foreach ($a as $x) {
    if ($f($x)) {
      return true;
    }
  }
  return false;
}

function all<T>(array<mixed, T> $a, (function(T): bool) $f): bool {
  foreach ($a as $x) {
    if (!$f($x)) {
      return false;
    }
  }
  return true;
}

function keys_to_lower<Tk, Tv>(array<Tk, Tv> $array): array<Tk, Tv> {
  return \array_change_key_case($array, \CASE_LOWER);
}

function keys_to_uppper<Tk, Tv>(array<Tk, Tv> $array): array<Tk, Tv> {
  return \array_change_key_case($array, \CASE_UPPER);
}

function to_pairs<Tk, Tv>(array<Tk, Tv> $array): array<(Tk, Tv)> {
  $r = [];
  foreach ($array as $k => $v) {
    $r[] = tuple($k, $v);
  }
  return $r;
}

function from_pairs<Tk as arraykey, Tv>(
  array<(Tk, Tv)> $pairs,
): array<Tk, Tv> {
  $r = [];
  foreach ($pairs as $p) {
    $r[$p[0]] = $p[1];
  }
  return $r;
}

function get<Tk as arraykey, Tv>(array<Tk, Tv> $array, Tk $key): Tv {
  $res = $array[$key];
  if ($res === null && !key_exists($array, $key)) {
    throw new \Exception("Key '$key' does not exist in map");
  }
  return $res;
}

/**
 * Get the key/value pair at the specified offset. Useful to get the first/last
 * key/value.
 *
 * get_pair($map, 0)[0] // first key
 * get_pair($map, 0)[1] // first value
 * get_pair($map, -1)[0] // last key
 * get_pair($map, -1)[1] // last value
 */
function get_pair<Tk, Tv>(array<Tk, Tv> $array, int $offset): (Tk, Tv) {
  foreach (slice_assoc($array, $offset) as $k => $v) {
    return tuple($k, $v);
  }
  throw new \Exception(
    "Offset $offset is out of bounds in array of size ".size($array),
  );
}

function set<Tk, Tv>(array<Tk, Tv> $array, Tk $key, Tv $val): array<Tk, Tv> {
  $array[$key] = $val;
  return $array;
}

function get_or_null<Tk, Tv>(array<Tk, Tv> $array, Tk $key): ?Tv {
  return $array[$key] ?? null;
}

function get_or_default<Tk, Tv>(
  array<Tk, Tv> $array,
  Tk $key,
  Tv $default,
): Tv {
  return key_exists($array, $key) ? $array[$key] : $default;
}

function key_exists<Tk>(array<Tk, mixed> $array, Tk $key): bool {
  return \array_key_exists($key, $array);
}

function get_offset<T>(array<T> $v, int $i): T {
  $l = \count($v);
  if ($i < 0) {
    $i += $l;
  }
  if ($i < 0 || $i >= $l) {
    throw new \Exception("Index $i out of bounds in array of length $l");
  }
  return $v[$i];
}

function set_offset<T>(array<T> $v, int $i, T $x): array<T> {
  $l = \count($v);
  if ($i < 0) {
    $i += $l;
  }
  if ($i < 0 || $i >= $l) {
    throw new \Exception("Index $i out of bounds in array of length $l");
  }
  $v[$i] = $x;
  return $v;
}

function column<Tk as arraykey, Tv>(
  array<array<Tk, Tv>> $arrays,
  Tk $key,
): array<Tv> {
  return \array_column($arrays, $key);
}

function combine<Tk, Tv>(array<Tk> $keys, array<Tv> $values): array<Tk, Tv> {
  return \array_combine($keys, $values);
}

function separate<Tk, Tv>(array<Tk, Tv> $array): (array<Tk>, array<Tv>) {
  $ks = [];
  $vs = [];
  foreach ($array as $k => $v) {
    $ks[] = $k;
    $vs[] = $v;
  }
  return tuple($ks, $vs);
}

function from_keys<Tk as arraykey, Tv>(
  array<Tk> $keys,
  Tv $value,
): array<Tk, Tv> {
  return \array_fill_keys($keys, $value);
}

function flip<Tk as arraykey, Tv as arraykey>(
  array<Tk, Tv> $array,
): array<Tv, Tk> {
  return \array_flip($array);
}

function flip_count<T as arraykey>(array<arraykey, T> $values): array<T, int> {
  return \array_count_values($values);
}

function keys<Tk>(array<Tk, mixed> $array): array<Tk> {
  return \array_keys($array);
}

function keys_strings(array<arraykey, mixed> $array): array<string> {
  return map(keys($array), $k ==> ''.$k);
}

function values<Tv>(array<mixed, Tv> $array): array<Tv> {
  return \array_values($array);
}

/**
 * If a key exists in both arrays, the value from the second array is used.
 */
function union_keys<Tk, Tv>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_replace($a, $b);
}

/**
 * If a key exists in multiple arrays, the value from the later array is used.
 */
function union_keys_all<Tk, Tv>(array<array<Tk, Tv>> $arrays): array<Tk, Tv> {
  return $arrays ? \call_user_func_array('array_replace', $arrays) : [];
}

function intersect<Tk, Tv as arraykey>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_intersect($a, $b);
}

function intersect_assoc<Tk as arraykey, Tv as arraykey>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_intersect_assoc($a, $b);
}

/**
 * Returns an array with only keys that exist in both arrays, using values from
 * the first array.
 */
function intersect_keys<Tk as arraykey, Tv>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_intersect_key($a, $b);
}

function diff<Tk, Tv as arraykey>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_diff($a, $b);
}

function diff_assoc<Tk, Tv as arraykey>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_diff_assoc($a, $b);
}

/**
 * Returns an array with keys that exist in the first array but not the second,
 * using values from the first array.
 */
function diff_keys<Tk as arraykey, Tv>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_intersect_key($a, $b);
}

/**
 * Extract multiple keys from a map at once.
 */
function select<Tk, Tv>(array<Tk, Tv> $array, array<Tk> $keys): array<Tv> {
  return map($keys, $key ==> $array[$key]);
}

function select_or_null<Tk, Tv>(
  array<Tk, Tv> $array,
  array<Tk> $keys,
): array<?Tv> {
  return map(
    $keys,
    function($key) use ($array) {
      return get_or_null($array, $key);
    },
  );
}

function zip<Ta, Tb>(array<Ta> $a, array<Tb> $b): array<(Ta, Tb)> {
  $r = [];
  $l = min(count($a), count($b));
  for ($i = 0; $i < $l; $i++) {
    $r[] = tuple($a[$i], $b[$i]);
  }
  return $r;
}

function zip_assoc<Tk, Ta, Tb>(
  array<Tk, Ta> $a,
  array<Tk, Tb> $b,
): array<Tk, (Ta, Tb)> {
  $ret = [];
  foreach ($a as $k => $v) {
    if (key_exists($b, $k)) {
      $ret[$k] = tuple($v, $b[$k]);
    }
  }
  return $ret;
}

function unzip<Ta, Tb>(array<(Ta, Tb)> $x): (array<Ta>, array<Tb>) {
  $a = [];
  $b = [];
  foreach ($x as $p) {
    $a[] = $p[0];
    $b[] = $p[1];
  }
  return tuple($a, $b);
}

function unzip_assoc<Tk, Ta, Tb>(
  array<Tk, (Ta, Tb)> $array,
): (array<Tk, Ta>, array<Tk, Tb>) {
  $a = [];
  $b = [];
  foreach ($array as $k => $v) {
    $a[$k] = $v[0];
    $b[$k] = $v[1];
  }
  return tuple($a, $b);
}

function transpose<T>(array<array<T>> $arrays): array<array<T>> {
  $num = 0;
  foreach ($arrays as $array) {
    $num = max($num, count($array));
  }
  $ret = repeat([], $num);
  foreach ($arrays as $array) {
    $i = 0;
    foreach ($array as $v) {
      $ret[$i++][] = $v;
    }
  }
  return $ret;
}

function transpose_assoc<Tk1, Tk2, Tv>(
  array<Tk1, array<Tk2, Tv>> $arrays,
): array<Tk2, array<Tk1, Tv>> {
  $ret = [];
  foreach ($arrays as $k1 => $array) {
    foreach ($array as $k2 => $v) {
      $ret[$k2][$k1] = $v;
    }
  }
  return $ret;
}

function transpose_num_assoc<Tk, Tv>(
  array<array<Tk, Tv>> $arrays,
): array<Tk, array<Tv>> {
  $ret = [];
  foreach ($arrays as $array) {
    foreach ($array as $k => $v) {
      $ret[$k][] = $v;
    }
  }
  return $ret;
}

function transpose_assoc_num<Tk, Tv>(
  array<Tk, array<Tv>> $arrays,
): array<array<Tk, Tv>> {
  $num = 0;
  foreach ($arrays as $array) {
    $num = max($num, count($array));
  }
  $ret = repeat([], $num);
  foreach ($arrays as $k => $array) {
    $i = 0;
    foreach ($array as $v) {
      $ret[$i++][$k] = $v;
    }
  }
  return $ret;
}

function shuffle<T>(array<T> $array): array<T> {
  \shuffle($array);
  return $array;
}

function shuffle_string(string $string): string {
  return \str_shuffle($string);
}

function reverse<T>(array<T> $array): array<T> {
  return \array_reverse($array, false);
}

function reverse_assoc<Tk, Tv>(array<Tk, Tv> $array): array<Tk, Tv> {
  return \array_reverse($array, true);
}

function reverse_string(string $string): string {
  return \strrev($string);
}

function chunk<T>(array<T> $array, int $size): array<array<T>> {
  if ($size < 1) {
    throw new \Exception("Chunk size must be >= 1");
  }
  return \array_chunk($array, $size, false);
}

function chunk_assoc<Tk, Tv>(
  array<Tk, Tv> $array,
  int $size,
): array<array<Tk, Tv>> {
  if ($size < 1) {
    throw new \Exception("Chunk size must be >= 1");
  }
  return \array_chunk($array, $size, true);
}

function chunk_string(string $string, int $size): array<string> {
  if ($size < 1) {
    throw new \Exception("Chunk size must be >= 1");
  }
  $ret = \str_split($string, $size);
  if (!\is_array($ret)) {
    throw new \Exception('str_split() failed');
  }
  return $ret;
}

function repeat<T>(T $value, int $count): array<T> {
  return \array_fill(0, $count, $value);
}

function repeat_string(string $string, int $count): string {
  return \str_repeat($string, $count);
}

function slice(string $string, int $offset, ?int $length = null): string {
  $ret = \substr($string, $offset, $length ?? 0x7FFFFFFF);
  // \substr() returns false "on failure".
  return $ret === false ? '' : $ret;
}

function slice_array<T>(
  array<T> $array,
  int $offset,
  ?int $length = null,
): array<T> {
  return \array_slice($array, $offset, $length);
}

function slice_assoc<Tk, Tv>(
  array<Tk, Tv> $array,
  int $offset,
  ?int $length = null,
): array<Tk, Tv> {
  return \array_slice($array, $offset, $length, true);
}

function splice(
  string $string,
  int $offset,
  ?int $length = null,
  string $replacement = '',
): string {
  return
    \substr_replace($string, $replacement, $offset, $length ?? 0x7FFFFFFF);
}

/**
 * Returns a pair of (new list, removed elements).
 */
function splice_array<T>(
  array<T> $array,
  int $offset,
  ?int $length = null,
  array<T> $replacement = [],
): (array<T>, array<T>) {
  $ret = \array_splice($array, $offset, $length, $replacement);
  return tuple($array, $ret);
}

function find(
  string $haystack,
  string $needle,
  int $offset = 0,
  bool $ci = false,
): ?int {
  if (\PHP_VERSION_ID < 70100 && $offset < 0) {
    $offset += length($haystack);
  }
  $ret =
    $ci
      ? \stripos($haystack, $needle, $offset)
      : \strpos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function find_last(
  string $haystack,
  string $needle,
  int $offset = 0,
  bool $ci = false,
): ?int {
  if (\PHP_VERSION_ID < 70100 && $offset < 0) {
    $offset += length($haystack);
  }
  $ret =
    $ci
      ? \strripos($haystack, $needle, $offset)
      : \strrpos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function find_count(string $haystack, string $needle, int $offset = 0): int {
  return \substr_count($haystack, $needle, $offset);
}

function contains(string $haystack, string $needle, int $offset = 0): bool {
  return find($haystack, $needle, $offset) !== null;
}

function length(string $string): int {
  return \strlen($string);
}

function count(array<mixed, mixed> $array): int {
  return \count($array);
}

function size(array<mixed, mixed> $array): int {
  return \count($array);
}

function find_key<Tk, Tv>(array<Tk, Tv> $array, Tv $value): ?Tk {
  $ret = \array_search($array, $value, true);
  return $ret === false ? new_null() : $ret;
}

function find_keys<Tk, Tv>(array<Tk, Tv> $array, Tv $value): array<Tk> {
  return \array_keys($array, $value, true);
}

function find_last_key<Tk, Tv>(array<Tk, Tv> $array, Tv $value): ?Tk {
  // Messy, but the easiest way to iterate in reverse that works
  // with both vector and associative arrays and doesn't create a copy.
  \end($array);
  while (!\is_null($key = \key($array))) {
    if (\current($array) === $value) {
      return $key;
    }
    \prev($array);
  }
  return new_null();
}

function in<T>(T $value, array<mixed, T> $array): bool {
  return \in_array($value, $array, true);
}

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

/**
 * Decode the given utf8 string and convert code points 0-255 to raw bytes
 * and discard code points >255.
 */
function decode_utf8(string $s): string {
  return \utf8_decode($s);
}

/**
 * Treat each byte as a unicode code point between 0 and 255 and encode these
 * characters as utf8.
 */
function encode_utf8(string $s): string {
  return \utf8_encode($s);
}

function split(
  string $string,
  string $delimiter = '',
  ?int $limit = null,
): array<string> {
  $limit = $limit ?? 0x7FFFFFFF;
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

function pad_array<T>(array<T> $array, int $size, T $value): array<T> {
  return \array_pad($array, $size, $value);
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
