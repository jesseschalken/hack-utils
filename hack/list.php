<?hh // strict

namespace HackUtils\list;

use HackUtils\list;
use HackUtils\map;
use HackUtils\set;

function chunk<T>(list<T> $map, int $size): list<list<T>> {
  return \array_chunk($map, $size, false);
}

function count_values(list<map\key> $values): map<int> {
  return \array_count_values($values);
}

function repeat<T>(T $value, int $count): list<T> {
  return \array_fill(0, $count, $value);
}

function concat<T>(list<T> $a, list<T> $b): list<T> {
  return \array_merge($a, $b);
}

function pad<T>(list<T> $list, int $size, T $value): list<T> {
  return \array_pad($list, $size, $value);
}

function reverse<T>(list<T> $list): list<T> {
  return \array_reverse($list, false);
}

function find<T>(list<T> $list, T $value): ?int {
  $ret = \array_search($list, $value, true);
  return $ret === false ? null : $ret;
}

function slice<T>(list<T> $list, int $offset, ?int $length = null): list<T> {
  return \array_slice($list, $offset, $length);
}

/**
 * Returns a pair of (new list, removed elements).
 */
function splice<T>(
  list<T> $list,
  int $offset,
  ?int $length = null,
  list<T> $replacement = [],
): (list<T>, list<T>) {
  $ret = \array_splice($list, $offset, $length, $replacement);
  return tuple($list, $ret);
}

function unique<T as map\key>(list<T> $list): list<T> {
  return \array_unique($list);
}

function shuffle<T>(list<T> $list): list<T> {
  \shuffle($list);
  return $list;
}

function count(list<mixed> $list): int {
  return \count($list);
}

function contains<T>(list<T> $list, T $value): bool {
  return \in_array($value, $list, true);
}

function range(int $start, int $end, int $step = 1): list<int> {
  return \range($start, $end, $step);
}

function sum<T as num>(list<T> $list): T {
  return \array_sum($list);
}

function product<T as num>(list<T> $list): T {
  return \array_product($list);
}

function sort<T>(list<T> $list, (function(T, T): int) $cmp): list<T> {
  usort($list, $cmp);
  return $list;
}

function filter<T>(list<T> $list, (function(T): bool) $f): list<T> {
  $ret = \array_filter($list, $f);
  // array_filter() preserves keys, so if some elements were removed,
  // renumber keys 0,1...N.
  if (\count($ret) < \count($list)) {
    $ret = \array_values($ret);
  }
  return $ret;
}

function map<Tin, Tout>(
  list<Tin> $list,
  (function(Tin): Tout) $f,
): list<Tout> {
  return \array_map($f, $list);
}

function reduce<Tin, Tout>(
  list<Tin> $list,
  (function(Tout, Tin): Tout) $f,
  Tout $initial,
): Tout {
  return \array_reduce($list, $f, $initial);
}

function zip<Ta, Tb>(list<Ta> $a, list<Tb> $b): list<(Ta, Tb)> {
  $r = [];
  $l = \min(\count($a), \count($b));
  for ($i = 0; $i < $l; $i++) {
    $r[] = tuple($a[$i], $b[$i]);
  }
  return $r;
}

function unzip<Ta, Tb>(list<(Ta, Tb)> $x): (list<Ta>, list<Tb>) {
  $a = [];
  $b = [];
  foreach ($x as $p) {
    $a[] = $p[0];
    $b[] = $p[1];
  }
  return tuple($a, $b);
}
