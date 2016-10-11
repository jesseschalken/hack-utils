<?hh // strict

namespace HackUtils;

function shuffle<T>(array<T> $list): array<T> {
  \shuffle($list);
  return $list;
}

function shuffle_string(string $string): string {
  return \str_shuffle($string);
}

function reverse<T>(array<T> $list): array<T> {
  return \array_reverse($list, false);
}

function reverse_assoc<Tk, Tv>(array<Tk, Tv> $map): array<Tk, Tv> {
  return \array_reverse($map, true);
}

function reverse_string(string $string): string {
  return \strrev($string);
}

function chunk<T>(array<T> $map, int $size): array<array<T>> {
  if ($size < 1) {
    throw new \Exception("Chunk size must be >= 1");
  }
  return \array_chunk($map, $size, false);
}

function chunk_assoc<Tk, Tv>(
  array<Tk, Tv> $map,
  int $size,
): array<array<Tk, Tv>> {
  if ($size < 1) {
    throw new \Exception("Chunk size must be >= 1");
  }
  return \array_chunk($map, $size, true);
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

function sub(string $string, int $offset, ?int $length = null): string {
  $ret = \substr($string, $offset, $length ?? 0x7FFFFFFF);
  // \substr() returns false "on failure".
  return $ret === false ? '' : $ret;
}

function slice<T>(array<T> $list, int $offset, ?int $length = null): array<T> {
  return \array_slice($list, $offset, $length);
}

function slice_assoc<Tk, Tv>(
  array<Tk, Tv> $map,
  int $offset,
  ?int $length = null,
): array<Tk, Tv> {
  return \array_slice($map, $offset, $length, true);
}

function replace(
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
function splice<T>(
  array<T> $list,
  int $offset,
  ?int $length = null,
  array<T> $replacement = [],
): (array<T>, array<T>) {
  $ret = \array_splice($list, $offset, $length, $replacement);
  return tuple($list, $ret);
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

function contains(string $haystack, string $needle, int $offset = 0): bool {
  return find($haystack, $needle, $offset) !== null;
}

function len(string $string): int {
  return \strlen($string);
}

function count(array<mixed, mixed> $map): int {
  return \count($map);
}

function size(array<mixed, mixed> $map): int {
  return \count($map);
}

function find_key<Tk, Tv>(array<Tk, Tv> $array, Tv $value): ?Tk {
  $ret = \array_search($array, $value, true);
  return $ret === false ? new_null() : $ret;
}

function find_keys<Tk, Tv>(array<Tk, Tv> $array, Tv $value): array<Tk> {
  return \array_keys($array, $value, true);
}

function find_last_key<Tk, Tv>(array<Tk, Tv> $array, Tv $value): ?Tk {
  // Messy, but the easiest way to iterator in reverse that works
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

function in<T>(T $value, array<mixed, T> $map): bool {
  return \in_array($value, $map, true);
}
