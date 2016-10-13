<?hh // strict

namespace HackUtils;

function sort<T>(array<T> $array, (function(T, T): int) $cmp): array<T> {
  _check_sort(\usort($array, $cmp), 'usort');
  return $array;
}

function sort_assoc<Tk, Tv>(
  array<Tk, Tv> $array,
  (function(Tv, Tv): int) $cmp,
): array<Tk, Tv> {
  _check_sort(\uasort($array, $cmp), 'uasort');
  return $array;
}

function sort_keys<Tk, Tv>(
  array<Tk, Tv> $array,
  ?(function(Tk, Tk): int) $cmp = null,
): array<Tk, Tv> {
  if ($cmp !== null) {
    _check_sort(\uksort($array, $cmp), 'uksort');
  } else {
    _check_sort(\ksort($array, \SORT_STRING), 'ksort');
  }
  return $array;
}

function sort_pairs<Tk, Tv>(
  array<Tk, Tv> $array,
  (function((Tk, Tv), (Tk, Tv)): int) $cmp,
): array<Tk, Tv> {
  return from_pairs(sort(to_pairs($array), $cmp));
}

function sort_nums<T as num>(
  array<T> $nums,
  bool $reverse = false,
): array<T> {
  if ($reverse) {
    _check_sort(\rsort($nums, \SORT_NUMERIC), 'rsort');
  } else {
    _check_sort(\sort($nums, \SORT_NUMERIC), 'sort');
  }
  return $nums;
}

function sort_nums_assoc<Tk, Tv as num>(
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

function sort_nums_keys<Tk as num, Tv>(
  array<Tk, Tv> $array,
  bool $reverse = false,
): array<Tk, Tv> {
  if ($reverse) {
    _check_sort(\krsort($array, \SORT_NUMERIC), 'krsort');
  } else {
    _check_sort(\ksort($array, \SORT_NUMERIC), 'ksort');
  }
  return $array;
}

function sort_strings<T as arraykey>(
  array<T> $strings,
  bool $ci = false,
  bool $natural = false,
  bool $reverse = false,
): array<T> {
  $flags = _string_sort_flags($ci, $natural);
  if ($reverse) {
    _check_sort(\rsort($strings, $flags), 'rsort');
  } else {
    _check_sort(\sort($strings, $flags), 'sort');
  }
  return $strings;
}

function sort_strings_assoc<Tk, Tv as arraykey>(
  array<Tk, Tv> $strings,
  bool $ci = false,
  bool $natural = false,
  bool $reverse = false,
): array<Tk, Tv> {
  $flags = _string_sort_flags($ci, $natural);
  if ($reverse) {
    _check_sort(\arsort($strings, $flags), 'arsort');
  } else {
    _check_sort(\asort($strings, $flags), 'asort');
  }
  return $strings;
}

function sort_strings_keys<Tk as arraykey, Tv>(
  array<Tk, Tv> $array,
  bool $ci = false,
  bool $natural = false,
  bool $reverse = false,
): array<Tk, Tv> {
  $flags = _string_sort_flags($ci, $natural);
  if ($reverse) {
    _check_sort(\krsort($array, $flags), 'krsort');
  } else {
    _check_sort(\ksort($array, $flags), 'ksort');
  }
  return $array;
}

function unique_nums<Tk, Tv as num>(array<Tk, Tv> $array): array<Tk, Tv> {
  return \array_unique($array, \SORT_NUMERIC);
}

function unique_strings<Tk, Tv as arraykey>(
  array<Tk, Tv> $array,
  bool $ci = false,
  bool $natural = false,
): array<Tk, Tv> {
  return \array_unique($array, _string_sort_flags($ci, $natural));
}

function _string_sort_flags(bool $ci, bool $natural): int {
  return
    ($natural ? \SORT_NATURAL : \SORT_STRING) | ($ci ? \SORT_FLAG_CASE : 0);
}

function _check_sort(bool $ret, string $func): void {
  if ($ret === false) {
    throw new \Exception("$func() failed");
  }
}
