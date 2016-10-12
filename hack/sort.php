<?hh // strict

namespace HackUtils;

function sort<T>(array<T> $list, (function(T, T): int) $cmp): array<T> {
  \usort($list, $cmp);
  return $list;
}

function sort_assoc<Tk, Tv>(
  array<Tk, Tv> $map,
  (function(Tv, Tv): int) $cmp,
): array<Tk, Tv> {
  \uasort($map, $cmp);
  return $map;
}

function sort_keys<Tk, Tv>(
  array<Tk, Tv> $map,
  ?(function(Tk, Tk): int) $cmp = null,
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

function sort_pairs<Tk, Tv>(
  array<Tk, Tv> $map,
  (function((Tk, Tv), (Tk, Tv)): int) $cmp,
): array<Tk, Tv> {
  return from_pairs(sort(to_pairs($map), $cmp));
}

function num_sort<T as num>(array<T> $nums, bool $reverse = false): array<T> {
  if ($reverse) {
    \rsort($nums, \SORT_NUMERIC);
  } else {
    \sort($nums, \SORT_NUMERIC);
  }
  return $nums;
}

function num_sort_assoc<Tk, Tv as num>(
  array<Tk, Tv> $nums,
  bool $reverse = false,
): array<Tk, Tv> {
  if ($reverse) {
    \arsort($nums, \SORT_NUMERIC);
  } else {
    \asort($nums, \SORT_NUMERIC);
  }
  return $nums;
}

function num_sort_keys<Tk as num, Tv>(
  array<Tk, Tv> $map,
  bool $reverse = false,
): array<Tk, Tv> {
  if ($reverse) {
    \krsort($map, \SORT_NUMERIC);
  } else {
    \ksort($map, \SORT_NUMERIC);
  }
  return $map;
}

function num_unique<Tk, Tv as num>(array<Tk, Tv> $map): array<Tk, Tv> {
  return \array_unique($map, \SORT_NUMERIC);
}

function str_sort<T as arraykey>(
  array<T> $strings,
  bool $ci = false,
  bool $natural = false,
  bool $reverse = false,
): array<T> {
  $flags = _str_sort_flags($ci, $natural);
  if ($reverse) {
    \rsort($strings, $flags);
  } else {
    \sort($strings, $flags);
  }
  return $strings;
}

function str_sort_assoc<Tk, Tv as arraykey>(
  array<Tk, Tv> $strings,
  bool $ci = false,
  bool $natural = false,
  bool $reverse = false,
): array<Tk, Tv> {
  $flags = _str_sort_flags($ci, $natural);
  if ($reverse) {
    \arsort($strings, $flags);
  } else {
    \asort($strings, $flags);
  }
  return $strings;
}

function str_sort_keys<Tk as arraykey, Tv>(
  array<Tk, Tv> $map,
  bool $ci = false,
  bool $natural = false,
  bool $reverse = false,
): array<Tk, Tv> {
  $flags = _str_sort_flags($ci, $natural);
  if ($reverse) {
    \krsort($map, $flags);
  } else {
    \ksort($map, $flags);
  }
  return $map;
}

function str_unique<Tk, Tv as arraykey>(
  array<Tk, Tv> $map,
  bool $ci = false,
  bool $natural = false,
): array<Tk, Tv> {
  return \array_unique($map, _str_sort_flags($ci, $natural));
}

function _str_sort_flags(bool $ci, bool $natural): int {
  return
    ($natural ? \SORT_NATURAL : \SORT_STRING) | ($ci ? \SORT_FLAG_CASE : 0);
}
