<?hh // strict

namespace HackUtils;

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

function _check_empty(array<mixed> $a, string $op): void {
  if (!$a) {
    throw new \Exception("Cannot $op: Array is empty");
  }
}

function range(int $start, int $end, int $step = 1): array<int> {
  return \range($start, $end, $step);
}

function filter<T>(array<T> $list, (function(T): bool) $f): array<T> {
  $ret = filter_assoc($list, $f);
  // array_filter() preserves keys, so if some elements were removed,
  // renumber keys 0,1...N.
  return count($ret) != count($list) ? values($ret) : $list;
}

function filter_assoc<Tk, Tv>(
  array<Tk, Tv> $map,
  (function(Tv): bool) $f,
): array<Tk, Tv> {
  return \array_filter($map, $f);
}

function map<Tin, Tout>(
  array<Tin> $list,
  (function(Tin): Tout) $f,
): array<Tout> {
  return \array_map($f, $list);
}

function map_assoc<Tk, Tv1, Tv2>(
  array<Tk, Tv1> $map,
  (function(Tv1): Tv2) $f,
): array<Tk, Tv2> {
  return \array_map($f, $map);
}

function reduce<Tin, Tout>(
  array<arraykey, Tin> $list,
  (function(Tout, Tin): Tout) $f,
  Tout $initial,
): Tout {
  return \array_reduce($list, $f, $initial);
}

function reduce_right<Tin, Tout>(
  array<arraykey, Tin> $list,
  (function(Tout, Tin): Tout) $f,
  Tout $value,
): Tout {
  // Messy, but the easiest way of iterating through an array in reverse
  // without creating a copy.
  \end($list);
  while (!\is_null($key = \key($list))) {
    $value = $f($value, \current($list));
    \prev($list);
  }
  return $value;
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
