<?hh // strict

namespace HackUtils\set;

use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;

type value = arraykey;

function create<T as value>(array<T> $values = []): array<T, mixed> {
  return $values ? \array_fill_keys($values, true) : [];
}

function values<T as value>(array<T, mixed> $set): array<value> {
  return \array_keys($set);
}

function union<T as value>(
  array<T, mixed> $a,
  array<T, mixed> $b,
): array<T, mixed> {
  return \array_replace($a, $b);
}

function union_all<T as value>(array<array<T, mixed>> $sets): array<T, mixed> {
  return \call_user_func_array('array_replace', $sets);
}

function intersect<T as value>(
  array<T, mixed> $a,
  array<T, mixed> $b,
): array<T, mixed> {
  return \array_intersect_key($a, $b);
}

function diff<T as value>(
  array<T, mixed> $a,
  array<T, mixed> $b,
): array<T, mixed> {
  return \array_diff_key($a, $b);
}

function equal<T as value>(array<T, mixed> $a, array<T, mixed> $b): bool {
  return !\array_diff_key($a, $b) && !\array_diff_key($b, $a);
}

function reverse<T as value>(array<T, mixed> $set): array<T, mixed> {
  return \array_reverse($set, true);
}

function size<T as value>(array<T, mixed> $set): int {
  return \count($set);
}

function contains<T as value>(array<T, mixed> $set, T $value): bool {
  return \array_key_exists($value, $set);
}
