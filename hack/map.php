<?hh // strict

namespace HackUtils\map;

use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;
use function HackUtils\new_null;

type key = arraykey;

function to_pairs<T>(map<T> $map): array<(key, T)> {
  $r = [];
  foreach ($map as $k => $v) {
    $r[] = tuple($k, $v);
  }
  return $r;
}

function from_pairs<T>(array<(key, T)> $pairs): map<T> {
  $r = [];
  foreach ($pairs as $p) {
    $r[$p[0]] = $p[1];
  }
  return $r;
}

function chunk<T>(map<T> $map, int $size): array<map<T>> {
  return \array_chunk($map, $size, true);
}

function soft_get<T>(map<T> $map, key $key): ?T {
  return $map[$key] ?? new_null();
}

function get_default<T>(map<T> $map, key $key, T $default): T {
  return has_key($map, $key) ? $map[$key] : $default;
}

/**
 * The key of a map is actually a string, but PHP converts intish strings to
 * ints. Use this function to convert them back.
 */
function fixkey(key $key): string {
  return (string) $key;
}

function fixkeys(array<key> $keys): array<string> {
  return vector\map($keys, $key ==> (string) $key);
}

function column<T>(array<map<T>> $maps, key $key): array<T> {
  return \array_column($maps, $key);
}

function combine<T>(array<key> $keys, array<T> $values): map<T> {
  return \array_combine($keys, $values);
}

function separate<T>(map<T> $map): (array<key>, array<T>) {
  $ks = [];
  $vs = [];
  foreach ($map as $k => $v) {
    $ks[] = $k;
    $vs[] = $v;
  }
  return tuple($ks, $vs);
}

function fill_keys<T>(array<key> $keys, T $value): map<T> {
  return \array_fill_keys($keys, $value);
}

function flip(map<key> $map): map<key> {
  return \array_flip($map);
}

function has_key(map<mixed> $map, key $key): bool {
  return \array_key_exists($key, $map);
}

function keys(map<mixed> $map): array<key> {
  return \array_keys($map);
}

function values<T>(map<T> $map): array<T> {
  return \array_values($map);
}

function value_keys<T>(map<T> $map, T $value): array<key> {
  return \array_keys($map, $value, true);
}

function union<T>(map<T> $a, map<T> $b): map<T> {
  return \array_replace($a, $b);
}

function union_all<T>(array<map<T>> $maps): map<T> {
  return \call_user_func_array('array_replace', $maps);
}

function reverse<T>(map<T> $map): map<T> {
  return \array_reverse($map, true);
}

function find<T>(map<T> $map, T $value): ?key {
  $ret = \array_search($map, $value, true);
  return $ret === false ? null : $ret;
}

function slice<T>(map<T> $map, int $offset, ?int $length = null): map<T> {
  return \array_slice($map, $offset, $length, true);
}

function size(map<mixed> $map): int {
  return \count($map);
}

function contains<T>(map<T> $map, T $value): bool {
  return \in_array($value, $map, true);
}

function sort_keys<T>(
  map<T> $map,
  ?(function(key, key): int) $cmp = null,
): map<T> {
  if ($cmp !== null) {
    \uksort($map, $cmp);
  } else {
    \ksort($map, \SORT_STRING);
  }
  return $map;
}

function sort<T>(map<T> $map, (function(T, T): int) $cmp): map<T> {
  \uasort($map, $cmp);
  return $map;
}

function filter<T>(map<T> $map, (function(T): bool) $f): map<T> {
  return \array_filter($map, $f);
}

function map<Tin, Tout>(map<Tin> $map, (function(Tin): Tout) $f): map<Tout> {
  return \array_map($f, $map);
}

function reduce<Tin, Tout>(
  map<Tin> $map,
  (function(Tout, Tin): Tout) $f,
  Tout $initial,
): Tout {
  return \array_reduce($map, $f, $initial);
}

/**
 * Extract multiple keys from a map at once.
 */
function select<T>(map<T> $map, array<key> $keys): array<T> {
  return vector\map($keys, $key ==> $map[$key]);
}

function zip<Ta, Tb>(map<Ta> $a, map<Tb> $b): map<(Ta, Tb)> {
  $ret = [];
  foreach ($a as $k => $v) {
    if (has_key($b, $k)) {
      $ret[$k] = tuple($v, $b[$k]);
    }
  }
  return $ret;
}

function unzip<Ta, Tb>(map<(Ta, Tb)> $map): (map<Ta>, map<Tb>) {
  $a = [];
  $b = [];
  foreach ($map as $k => $v) {
    $a[$k] = $v[0];
    $b[$k] = $v[1];
  }
  return tuple($a, $b);
}
