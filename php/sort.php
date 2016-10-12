<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function sort($list, $cmp) {
    _check_sort(\usort($list, $cmp), "usort");
    return $list;
  }
  function sort_assoc($map, $cmp) {
    _check_sort(\uasort($map, $cmp), "uasort");
    return $map;
  }
  function sort_keys($map, $cmp = null) {
    if ($cmp !== null) {
      _check_sort(\uksort($map, $cmp), "uksort");
    } else {
      _check_sort(\ksort($map, \SORT_STRING), "ksort");
    }
    return $map;
  }
  function sort_pairs($map, $cmp) {
    return from_pairs(sort(to_pairs($map), $cmp));
  }
  function num_sort($nums, $reverse = false) {
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\rsort($nums, \SORT_NUMERIC), "rsort");
    } else {
      _check_sort(\sort($nums, \SORT_NUMERIC), "sort");
    }
    return $nums;
  }
  function num_sort_assoc($nums, $reverse = false) {
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\arsort($nums, \SORT_NUMERIC), "arsort");
    } else {
      _check_sort(\asort($nums, \SORT_NUMERIC), "asort");
    }
    return $nums;
  }
  function num_sort_keys($map, $reverse = false) {
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\krsort($map, \SORT_NUMERIC), "krsort");
    } else {
      _check_sort(\ksort($map, \SORT_NUMERIC), "ksort");
    }
    return $map;
  }
  function num_unique($map) {
    return \array_unique($map, \SORT_NUMERIC);
  }
  function str_sort(
    $strings,
    $ci = false,
    $natural = false,
    $reverse = false
  ) {
    $flags = _str_sort_flags($ci, $natural);
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\rsort($strings, $flags), "rsort");
    } else {
      _check_sort(\sort($strings, $flags), "sort");
    }
    return $strings;
  }
  function str_sort_assoc(
    $strings,
    $ci = false,
    $natural = false,
    $reverse = false
  ) {
    $flags = _str_sort_flags($ci, $natural);
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\arsort($strings, $flags), "arsort");
    } else {
      _check_sort(\asort($strings, $flags), "asort");
    }
    return $strings;
  }
  function str_sort_keys(
    $map,
    $ci = false,
    $natural = false,
    $reverse = false
  ) {
    $flags = _str_sort_flags($ci, $natural);
    if (\hacklib_cast_as_boolean($reverse)) {
      _check_sort(\krsort($map, $flags), "krsort");
    } else {
      _check_sort(\ksort($map, $flags), "ksort");
    }
    return $map;
  }
  function str_unique($map, $ci = false, $natural = false) {
    return \array_unique($map, _str_sort_flags($ci, $natural));
  }
  function _str_sort_flags($ci, $natural) {
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
