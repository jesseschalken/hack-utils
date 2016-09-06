<?hh // strict

namespace HackUtils\map;

use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;

type key = arraykey;

function to_pairs<T>(map<T> $map): vector<(key, T)> {
  $r = [];
  foreach ($map as $k => $v) {
    $r[] = tuple($k, $v);
  }
  return $r;
}

function from_pairs<T>(vector<(key, T)> $pairs): map<T> {
  $r = [];
  foreach ($pairs as $p) {
    $r[$p[0]] = $p[1];
  }
  return $r;
}

function chunk<T>(map<T> $map, int $size): vector<map<T>> {
  return \array_chunk($map, $size, true);
}

/**
 * The key of a map is actually a string, but PHP converts intish strings to
 * ints. Use this function to convert them back.
 */
function fixkey(key $key): string {
  return (string) $key;
}

function fixkeys(vector<key> $keys): vector<string> {
  $ret = [];
  foreach ($keys as $key) {
    $ret[] = fixkey($key);
  }
  return $ret;
}

function column<T>(vector<map<T>> $maps, key $key): vector<T> {
  return \array_column($maps, $key);
}

function combine<T>(vector<key> $keys, vector<T> $values): map<T> {
  return \array_combine($keys, $values);
}

function separate<T>(map<T> $map): (vector<key>, vector<T>) {
  $ks = [];
  $vs = [];
  foreach ($map as $k => $v) {
    $ks[] = $k;
    $vs[] = $v;
  }
  return tuple($ks, $vs);
}

function fill_keys<T>(vector<key> $keys, T $value): map<T> {
  return \array_fill_keys($keys, $value);
}

function flip(map<key> $map): map<key> {
  return \array_flip($map);
}

function has_key(map<mixed> $map, key $key): bool {
  return \array_key_exists($key, $map);
}

function keys(map<mixed> $map): vector<key> {
  return \array_keys($map);
}

function values<T>(map<T> $map): vector<T> {
  return \array_values($map);
}

function value_keys<T>(map<T> $map, T $value): vector<key> {
  return \array_keys($map, $value, true);
}

function replace<T>(map<T> $a, map<T> $b): map<T> {
  return \array_replace($a, $b);
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

function count(map<mixed> $map): int {
  return \count($map);
}

function contains<T>(map<T> $map, T $value): bool {
  return \in_array($value, $map, true);
}

function sort_keys<T>(map<T> $map, (function(key, key): int) $cmp): map<T> {
  \uksort($map, $cmp);
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
