<?hh // strict

namespace HackUtils\vector;

use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;
use HackUtils as utils;

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

function chunk<T>(array<T> $map, int $size): array<array<T>> {
  return \array_chunk($map, $size, false);
}

function count_values<T as arraykey>(array<T> $values): array<T, int> {
  return \array_count_values($values);
}

function repeat<T>(T $value, int $count): array<T> {
  return \array_fill(0, $count, $value);
}

function concat<T>(array<T> $a, array<T> $b): array<T> {
  return \array_merge($a, $b);
}

function concat_all<T>(array<array<T>> $vectors): array<T> {
  return $vectors ? \call_user_func_array('array_merge', $vectors) : [];
}

function pad<T>(array<T> $list, int $size, T $value): array<T> {
  return \array_pad($list, $size, $value);
}

function reverse<T>(array<T> $list): array<T> {
  return \array_reverse($list, false);
}

function index_of<T>(array<T> $list, T $value): ?int {
  $ret = \array_search($list, $value, true);
  return $ret === false ? null : $ret;
}

function last_index_of<T>(array<T> $list, T $value): ?int {
  $ret = utils\new_null();
  foreach ($list as $k => $v) {
    if ($v === $value) {
      $ret = $k;
    }
  }
  return $ret;
}

function slice<T>(array<T> $list, int $offset, ?int $length = null): array<T> {
  return \array_slice($list, $offset, $length);
}

function get<T>(array<T> $v, int $i): T {
  $l = \count($v);
  if ($i < 0) {
    $i += $l;
  }
  if ($i < 0 || $i >= $l) {
    throw new \Exception("Index $i out of bounds in vector of length $l");
  }
  return $v[$i];
}

function set<T>(array<T> $v, int $i, T $x): array<T> {
  $l = length($v);
  // Setting the last index + 1 ($i == length) is allowed, since it would
  // still leave the vector packed with keys 0,1,2..N.
  if ($i < 0 || $i > $l) {
    throw new \Exception(
      "Cannot set index $i: Index out of bounds in array with length $l",
    );
  }
  $v[$i] = $x;
  return $v;
}

function push<T>(array<T> $v, T $x): array<T> {
  \array_push($v, $x);
  return $v;
}

function pop<T>(array<T> $v): (array<T>, T) {
  _check_empty($v, 'remove last element');
  $x = \array_pop($v);
  return tuple($v, $x);
}

function unshift<T>(T $x, array<T> $v): array<T> {
  \array_unshift($v, $x);
  return $v;
}

function shift<T>(array<T> $v): (T, array<T>) {
  _check_empty($v, 'remove first element');
  $x = \array_shift($v);
  return tuple($x, $v);
}

function first<T>(array<T> $a): T {
  _check_empty($a, 'get first element');
  return $a[0];
}

function last<T>(array<T> $a): T {
  _check_empty($a, 'get last element');
  return $a[length($a) - 1];
}

function _check_empty(array<mixed> $a, string $op): void {
  if (!$a) {
    throw new \Exception("Cannot $op: Array is empty");
  }
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

function unique<T as arraykey>(array<T> $list): array<T> {
  return \array_unique($list);
}

function shuffle<T>(array<T> $list): array<T> {
  \shuffle($list);
  return $list;
}

function length(array<mixed> $list): int {
  return \count($list);
}

function contains<T>(array<T> $list, T $value): bool {
  return \in_array($value, $list, true);
}

function range(int $start, int $end, int $step = 1): array<int> {
  return \range($start, $end, $step);
}

function sort<T>(array<T> $list, (function(T, T): int) $cmp): array<T> {
  \usort($list, $cmp);
  return $list;
}

function filter<T>(array<T> $list, (function(T): bool) $f): array<T> {
  $ret = \array_filter($list, $f);
  // array_filter() preserves keys, so if some elements were removed,
  // renumber keys 0,1...N.
  if (\count($ret) < \count($list)) {
    $ret = \array_values($ret);
  }
  return $ret;
}

function map<Tin, Tout>(
  array<Tin> $list,
  (function(Tin): Tout) $f,
): array<Tout> {
  return \array_map($f, $list);
}

function reduce<Tin, Tout>(
  array<Tin> $list,
  (function(Tout, Tin): Tout) $f,
  Tout $initial,
): Tout {
  return \array_reduce($list, $f, $initial);
}

function reduce_right<Tin, Tout>(
  array<Tin> $list,
  (function(Tout, Tin): Tout) $f,
  Tout $value,
): Tout {
  for ($i = (\count($list) - 1); $i >= 0; $i--) {
    $value = $f($value, $list[$i]);
  }
  return $value;
}

function keys(array<mixed> $array): array<int> {
  return \array_keys($array);
}

function zip<Ta, Tb>(array<Ta> $a, array<Tb> $b): array<(Ta, Tb)> {
  $r = [];
  $l = \min(\count($a), \count($b));
  for ($i = 0; $i < $l; $i++) {
    $r[] = tuple($a[$i], $b[$i]);
  }
  return $r;
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

function diff<T as arraykey>(array<T> $a, array<T> $b): array<T> {
  return \array_values(\array_diff($a, $b));
}

function intersect<T as arraykey>(array<T> $a, array<T> $b): array<T> {
  return \array_values(\array_intersect($a, $b));
}

function any<T>(array<T> $a, (function(T): bool) $f): bool {
  foreach ($a as $x) {
    if ($f($x)) {
      return true;
    }
  }
  return false;
}

function all<T>(array<T> $a, (function(T): bool) $f): bool {
  foreach ($a as $x) {
    if (!$f($x)) {
      return false;
    }
  }
  return true;
}

function of_vectors<T>(): array<array<T>> {
  return [];
}

function of_maps<Tk, Tv>(): array<array<Tk, Tv>> {
  return [];
}

function group_by<Tk, Tv>(
  array<Tv> $a,
  (function(Tv): Tk) $f,
): array<Tk, array<Tv>> {
  $res = map\of_vectors();
  foreach ($a as $v) {
    $res[$f($v)][] = $v;
  }
  return $res;
}
