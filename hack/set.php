<?hh // strict

namespace HackUtils\set;

use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;

type value = arraykey;

function create(array<value> $values = []): set {
  return $values ? \array_fill_keys($values, true) : [];
}

function values(set $set): vector<value> {
  return \array_keys($set);
}

function union(set $a, set $b): set {
  return \array_replace($a, $b);
}

function intersect(set $a, set $b): set {
  return \array_intersect_key($a, $b);
}

function diff(set $a, set $b): set {
  return \array_diff_key($a, $b);
}

function equals(set $a, set $b): bool {
  return !\array_diff_key($a, $b) && !\array_diff_key($b, $a);
}

function reverse(set $set): set {
  return \array_reverse($set, true);
}

function count(set $set): int {
  return \count($set);
}
