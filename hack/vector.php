<?hh // strict

namespace HackUtils\vector;

use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;

function is_vector(mixed $x): bool {
  if (!\is_array($x)) {
    return false;
  }
  $i = 0;
  foreach ($x as $k => $v) {
    if ($k !== $i++) {
      return false;
    }
  }
  return true;
}

function chunk<T>(vector<T> $map, int $size): vector<vector<T>> {
  return \array_chunk($map, $size, false);
}

function count_values(vector<map\key> $values): map<int> {
  return \array_count_values($values);
}

function repeat<T>(T $value, int $count): vector<T> {
  return \array_fill(0, $count, $value);
}

function concat<T>(vector<T> $a, vector<T> $b): vector<T> {
  return \array_merge($a, $b);
}

function concat_all<T>(vector<vector<T>> $vectors): vector<T> {
  return \call_user_func_array('array_merge', $vectors);
}

function pad<T>(vector<T> $list, int $size, T $value): vector<T> {
  return \array_pad($list, $size, $value);
}

function reverse<T>(vector<T> $list): vector<T> {
  return \array_reverse($list, false);
}

function find<T>(vector<T> $list, T $value): ?int {
  $ret = \array_search($list, $value, true);
  return $ret === false ? null : $ret;
}

function slice<T>(
  vector<T> $list,
  int $offset,
  ?int $length = null,
): vector<T> {
  return \array_slice($list, $offset, $length);
}

/**
 * Returns a pair of (new list, removed elements).
 */
function splice<T>(
  vector<T> $list,
  int $offset,
  ?int $length = null,
  array<T> $replacement = [],
): (vector<T>, vector<T>) {
  $ret = \array_splice($list, $offset, $length, $replacement);
  return tuple($list, $ret);
}

function unique<T as map\key>(vector<T> $list): vector<T> {
  return \array_unique($list);
}

function shuffle<T>(vector<T> $list): vector<T> {
  \shuffle($list);
  return $list;
}

function count(vector<mixed> $list): int {
  return \count($list);
}

function contains<T>(vector<T> $list, T $value): bool {
  return \in_array($value, $list, true);
}

function range(int $start, int $end, int $step = 1): vector<int> {
  return \range($start, $end, $step);
}

function sum<T as num>(vector<T> $list): T {
  return \array_sum($list);
}

function product<T as num>(vector<T> $list): T {
  return \array_product($list);
}

function sort<T>(vector<T> $list, (function(T, T): int) $cmp): vector<T> {
  usort($list, $cmp);
  return $list;
}

function filter<T>(vector<T> $list, (function(T): bool) $f): vector<T> {
  $ret = \array_filter($list, $f);
  // array_filter() preserves keys, so if some elements were removed,
  // renumber keys 0,1...N.
  if (\count($ret) < \count($list)) {
    $ret = \array_values($ret);
  }
  return $ret;
}

function map<Tin, Tout>(
  vector<Tin> $list,
  (function(Tin): Tout) $f,
): vector<Tout> {
  return \array_map($f, $list);
}

function reduce<Tin, Tout>(
  vector<Tin> $list,
  (function(Tout, Tin): Tout) $f,
  Tout $initial,
): Tout {
  return \array_reduce($list, $f, $initial);
}

function zip<Ta, Tb>(vector<Ta> $a, vector<Tb> $b): vector<(Ta, Tb)> {
  $r = [];
  $l = \min(\count($a), \count($b));
  for ($i = 0; $i < $l; $i++) {
    $r[] = tuple($a[$i], $b[$i]);
  }
  return $r;
}

function unzip<Ta, Tb>(vector<(Ta, Tb)> $x): (vector<Ta>, vector<Tb>) {
  $a = [];
  $b = [];
  foreach ($x as $p) {
    $a[] = $p[0];
    $b[] = $p[1];
  }
  return tuple($a, $b);
}
