<?hh // strict

namespace HackUtils\set;

use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;

type value = arraykey;

function create(array<value> $values = []): set {
  return $values ? \array_fill_keys($values, true) : [];
}

function values(set $set): array<value> {
  return \array_keys($set);
}

function union(set $a, set $b): set {
  return \array_replace($a, $b);
}

function union_all(array<set> $sets): set {
  return \call_user_func_array('array_replace', $sets);
}

function intersect(set $a, set $b): set {
  return \array_intersect_key($a, $b);
}

function diff(set $a, set $b): set {
  return \array_diff_key($a, $b);
}

function equal(set $a, set $b): bool {
  return !\array_diff_key($a, $b) && !\array_diff_key($b, $a);
}

function reverse(set $set): set {
  return \array_reverse($set, true);
}

function size(set $set): int {
  return \count($set);
}

function contains(set $set, value $value): bool {
  return \array_key_exists($value, $set);
}
