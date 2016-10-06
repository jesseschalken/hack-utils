<?hh // strict

namespace HackUtils\map;

use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;
use HackUtils as utils;
use HackUtils\{fun0, fun1, fun2};
use function HackUtils\new_null;

type key = arraykey;

function to_pairs<Tk, Tv>(array<Tk, Tv> $map): array<(Tk, Tv)> {
  $r = [];
  foreach ($map as $k => $v) {
    $r[] = tuple($k, $v);
  }
  return $r;
}

function from_pairs<Tk, Tv>(array<(Tk, Tv)> $pairs): array<Tk, Tv> {
  $r = [];
  foreach ($pairs as $p) {
    $r[$p[0]] = $p[1];
  }
  return $r;
}

function chunk<Tk, Tv>(array<Tk, Tv> $map, int $size): array<array<Tk, Tv>> {
  return \array_chunk($map, $size, true);
}

function get<Tk as arraykey, Tv>(array<Tk, Tv> $map, Tk $key): Tv {
  $res = $map[$key];
  if ($res === null && !has_key($map, $key)) {
    throw new \Exception("Key '$key' does not exist in map");
  }
  return $res;
}

function set<Tk, Tv>(array<Tk, Tv> $map, Tk $key, Tv $val): array<Tk, Tv> {
  $map[$key] = $val;
  return $map;
}

function soft_get<Tk, Tv>(array<Tk, Tv> $map, Tk $key): ?Tv {
  return $map[$key] ?? new_null();
}

function get_default<Tk, Tv>(array<Tk, Tv> $map, Tk $key, Tv $default): Tv {
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

function column<Tk as key, Tv>(
  array<array<Tk, Tv>> $maps,
  Tk $key,
): array<Tv> {
  return \array_column($maps, $key);
}

function combine<Tk, Tv>(array<Tk> $keys, array<Tv> $values): array<Tk, Tv> {
  return \array_combine($keys, $values);
}

function splice<Tk, Tv>(
  array<Tk, Tv> $map,
  int $offset,
  ?int $length = null,
  array<Tk, Tv> $replacement = [],
): (array<Tk, Tv>, array<Tk, Tv>) {
  $left = slice($map, 0, $offset);
  $middle = slice($map, $offset, $length);
  $right = $length !== null ? slice($map, $length) : [];
  return tuple(\array_replace($left, $replacement, $right), $middle);
}

function separate<Tk, Tv>(array<Tk, Tv> $map): (array<Tk>, array<Tv>) {
  $ks = [];
  $vs = [];
  foreach ($map as $k => $v) {
    $ks[] = $k;
    $vs[] = $v;
  }
  return tuple($ks, $vs);
}

function fill_keys<Tk, Tv>(array<Tk> $keys, Tv $value): array<Tk, Tv> {
  return \array_fill_keys($keys, $value);
}

function flip<Tk as key, Tv as key>(array<Tk, Tv> $map): array<Tv, Tk> {
  return \array_flip($map);
}

function has_key<Tk>(array<Tk, mixed> $map, Tk $key): bool {
  return \array_key_exists($key, $map);
}

function keys<Tk>(array<Tk, mixed> $map): array<Tk> {
  return \array_keys($map);
}

function values<Tv>(array<mixed, Tv> $map): array<Tv> {
  return \array_values($map);
}

function value_keys<Tk, Tv>(array<Tk, Tv> $map, Tv $value): array<Tk> {
  return \array_keys($map, $value, true);
}

function union<Tk, Tv>(array<Tk, Tv> $a, array<Tk, Tv> $b): array<Tk, Tv> {
  return \array_replace($a, $b);
}

function union_all<Tk, Tv>(array<array<Tk, Tv>> $maps): array<Tk, Tv> {
  return \call_user_func_array('array_replace', $maps);
}

function reverse<Tk, Tv>(array<Tk, Tv> $map): array<Tk, Tv> {
  return \array_reverse($map, true);
}

function find<Tk, Tv>(array<Tk, Tv> $map, Tv $value): ?Tk {
  $ret = \array_search($map, $value, true);
  return $ret === false ? null : $ret;
}

function slice<Tk, Tv>(
  array<Tk, Tv> $map,
  int $offset,
  ?int $length = null,
): array<Tk, Tv> {
  return \array_slice($map, $offset, $length, true);
}

function size(array<mixed, mixed> $map): int {
  return \count($map);
}

function contains<T>(array<mixed, T> $map, T $value): bool {
  return \in_array($value, $map, true);
}

function sort_keys<Tk, Tv>(
  array<Tk, Tv> $map,
  ?fun2<Tk, Tk, int> $cmp = null,
): array<Tk, Tv> {
  if ($cmp !== null) {
    $ret = \uksort($map, $cmp);
  } else {
    $ret = \ksort($map, \SORT_STRING);
  }
  if ($ret === false) {
    throw new \Exception(($cmp ? 'ksort' : 'uksort').'() failed');
  }
  return $map;
}

function sort<Tk, Tv>(
  array<Tk, Tv> $map,
  fun2<Tv, Tv, int> $cmp,
): array<Tk, Tv> {
  \uasort($map, $cmp);
  return $map;
}

function filter<Tk, Tv>(array<Tk, Tv> $map, fun1<Tv, bool> $f): array<Tk, Tv> {
  return \array_filter($map, $f);
}

function filter_pairs<Tk, Tv>(
  array<Tk, Tv> $map,
  fun1<(Tk, Tv), bool> $f,
): array<Tk, Tv> {
  foreach ($map as $k => $v) {
    if (!$f(tuple($k, $v))) {
      unset($map[$k]);
    }
  }
  return $map;
}

function filter_keys<Tk, Tv>(
  array<Tk, Tv> $map,
  fun1<Tk, bool> $f,
): array<Tk, Tv> {
  foreach ($map as $k => $v) {
    if (!$f($k)) {
      unset($map[$k]);
    }
  }
  return $map;
}

function get_pair<Tk, Tv>(array<Tk, Tv> $array, int $offset): (Tk, Tv) {
  $slice = slice($array, $offset, 1);
  foreach ($slice as $k => $v) {
    return tuple($k, $v);
  }
  throw new \Exception(
    "Offset $offset out of bounds for array of size ".size($array),
  );
}

function map<Tk, Tin, Tout>(
  array<Tk, Tin> $map,
  fun1<Tin, Tout> $f,
): array<Tk, Tout> {
  return \array_map($f, $map);
}

function map_pairs<Tk1, Tv1, Tk2, Tv2>(
  array<Tk1, Tv1> $map,
  fun1<(Tk1, Tv1), (Tk2, Tv2)> $f,
): array<Tk2, Tv2> {
  $res = [];
  foreach ($map as $k => $v) {
    list($k, $v) = $f(tuple($k, $v));
    $res[$k] = $v;
  }
  return $res;
}

function reduce<Tin, Tout>(
  array<mixed, Tin> $map,
  fun2<Tout, Tin, Tout> $f,
  Tout $initial,
): Tout {
  return \array_reduce($map, $f, $initial);
}

/**
 * Extract multiple keys from a map at once.
 */
function select<Tk, Tv>(array<Tk, Tv> $map, array<Tk> $keys): array<Tv> {
  return vector\map($keys, $key ==> $map[$key]);
}

function zip<Tk, Ta, Tb>(
  array<Tk, Ta> $a,
  array<Tk, Tb> $b,
): array<Tk, (Ta, Tb)> {
  $ret = [];
  foreach ($a as $k => $v) {
    if (has_key($b, $k)) {
      $ret[$k] = tuple($v, $b[$k]);
    }
  }
  return $ret;
}

function unzip<Tk, Ta, Tb>(
  array<Tk, (Ta, Tb)> $map,
): (array<Tk, Ta>, array<Tk, Tb>) {
  $a = [];
  $b = [];
  foreach ($map as $k => $v) {
    $a[$k] = $v[0];
    $b[$k] = $v[1];
  }
  return tuple($a, $b);
}

function of_vectors<Tk, T>(): array<Tk, array<T>> {
  return [];
}

function of_maps<Tk1, Tk2, Tv>(): array<Tk1, array<Tk2, Tv>> {
  return [];
}
