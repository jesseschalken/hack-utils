<?hh // strict

namespace HackUtils;

function sort<T>(array<T> $list, (function(T, T): int) $cmp): array<T> {
  _check_sort(\usort($list, $cmp), 'usort');
  return $list;
}

function sort_assoc<Tk, Tv>(
  array<Tk, Tv> $map,
  (function(Tv, Tv): int) $cmp,
): array<Tk, Tv> {
  _check_sort(\uasort($map, $cmp), 'uasort');
  return $map;
}

function sort_keys<Tk, Tv>(
  array<Tk, Tv> $map,
  ?(function(Tk, Tk): int) $cmp = null,
): array<Tk, Tv> {
  if ($cmp !== null) {
    _check_sort(\uksort($map, $cmp), 'uksort');
  } else {
    _check_sort(\ksort($map, \SORT_STRING), 'ksort');
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
    _check_sort(\rsort($nums, \SORT_NUMERIC), 'rsort');
  } else {
    _check_sort(\sort($nums, \SORT_NUMERIC), 'sort');
  }
  return $nums;
}

function num_sort_assoc<Tk, Tv as num>(
  array<Tk, Tv> $nums,
  bool $reverse = false,
): array<Tk, Tv> {
  if ($reverse) {
    _check_sort(\arsort($nums, \SORT_NUMERIC), 'arsort');
  } else {
    _check_sort(\asort($nums, \SORT_NUMERIC), 'asort');
  }
  return $nums;
}

function num_sort_keys<Tk as num, Tv>(
  array<Tk, Tv> $map,
  bool $reverse = false,
): array<Tk, Tv> {
  if ($reverse) {
    _check_sort(\krsort($map, \SORT_NUMERIC), 'krsort');
  } else {
    _check_sort(\ksort($map, \SORT_NUMERIC), 'ksort');
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
    _check_sort(\rsort($strings, $flags), 'rsort');
  } else {
    _check_sort(\sort($strings, $flags), 'sort');
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
    _check_sort(\arsort($strings, $flags), 'arsort');
  } else {
    _check_sort(\asort($strings, $flags), 'asort');
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
    _check_sort(\krsort($map, $flags), 'krsort');
  } else {
    _check_sort(\ksort($map, $flags), 'ksort');
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

function _check_sort(bool $ret, string $func): void {
  if ($ret === false) {
    throw new \Exception("$func() failed");
  }
}
