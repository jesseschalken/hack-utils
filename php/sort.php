<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function sort($array, $cmp) {
    _check_sort(\usort($array, $cmp), "usort");
    return $array;
  }
  function sort_assoc($array, $cmp) {
    _check_sort(\uasort($array, $cmp), "uasort");
    return $array;
  }
  function sort_keys($array, $cmp = null) {
    if ($cmp !== null) {
      _check_sort(\uksort($array, $cmp), "uksort");
    } else {
      _check_sort(\ksort($array, \SORT_STRING), "ksort");
    }
    return $array;
  }
  function sort_pairs($array, $cmp) {
    return from_pairs(sort(to_pairs($array), $cmp));
  }
  function sort_nums($nums, $reverse = false) {
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\rsort($nums, \SORT_NUMERIC), "rsort");
    } else {
      _check_sort(\sort($nums, \SORT_NUMERIC), "sort");
    }
    return $nums;
  }
  function sort_nums_assoc($nums, $reverse = false) {
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\arsort($nums, \SORT_NUMERIC), "arsort");
    } else {
      _check_sort(\asort($nums, \SORT_NUMERIC), "asort");
    }
    return $nums;
  }
  function sort_nums_keys($array, $reverse = false) {
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\krsort($array, \SORT_NUMERIC), "krsort");
    } else {
      _check_sort(\ksort($array, \SORT_NUMERIC), "ksort");
    }
    return $array;
  }
  function sort_strings(
    $strings,
    $ci = false,
    $natural = false,
    $reverse = false
  ) {
    $flags = _string_sort_flags($ci, $natural);
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\rsort($strings, $flags), "rsort");
    } else {
      _check_sort(\sort($strings, $flags), "sort");
    }
    return $strings;
  }
  function sort_strings_assoc(
    $strings,
    $ci = false,
    $natural = false,
    $reverse = false
  ) {
    $flags = _string_sort_flags($ci, $natural);
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\arsort($strings, $flags), "arsort");
    } else {
      _check_sort(\asort($strings, $flags), "asort");
    }
    return $strings;
  }
  function sort_strings_keys(
    $array,
    $ci = false,
    $natural = false,
    $reverse = false
  ) {
    $flags = _string_sort_flags($ci, $natural);
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\krsort($array, $flags), "krsort");
    } else {
      _check_sort(\ksort($array, $flags), "ksort");
    }
    return $array;
  }
  function unique_nums($array) {
    return \array_unique($array, \SORT_NUMERIC);
  }
  function unique_strings($array, $ci = false, $natural = false) {
    return \array_unique($array, _string_sort_flags($ci, $natural));
  }
  function _string_sort_flags($ci, $natural) {
    return
      (\hacklib_cast_as_boolean($natural) ? \SORT_NATURAL : \SORT_STRING) |
      (\hacklib_cast_as_boolean($ci) ? \SORT_FLAG_CASE : 0);
  }
  function _check_sort($ret, $func) {
    if ($ret === false) {
      throw new \Exception($func."() failed");
    }
  }
}
