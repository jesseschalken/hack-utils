<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function sort($list, $cmp) {
    \usort($list, $cmp);
    return $list;
  }
  function sort_assoc($map, $cmp) {
    \uasort($map, $cmp);
    return $map;
  }
  function sort_keys($map, $cmp = null) {
    if ($cmp !== null) {
      $ret = \uksort($map, $cmp);
    } else {
      $ret = \ksort($map, \SORT_STRING);
    }
    if ($ret === false) {
      throw new \Exception(
        (\hacklib_cast_as_boolean($cmp) ? "ksort" : "uksort")."() failed"
      );
    }
    return $map;
  }
  function sort_pairs($map, $cmp) {
    return from_pairs(sort(to_pairs($map), $cmp));
  }
  function num_sort($nums, $reverse = false) {
    if (\hacklib_cast_as_boolean($reverse)) {
      \rsort($nums, \SORT_NUMERIC);
    } else {
      \sort($nums, \SORT_NUMERIC);
    }
    return $nums;
  }
  function num_sort_assoc($nums, $reverse = false) {
    if (\hacklib_cast_as_boolean($reverse)) {
      \arsort($nums, \SORT_NUMERIC);
    } else {
      \asort($nums, \SORT_NUMERIC);
    }
    return $nums;
  }
  function num_sort_keys($map, $reverse = false) {
    if (\hacklib_cast_as_boolean($reverse)) {
      \krsort($map, \SORT_NUMERIC);
    } else {
      \ksort($map, \SORT_NUMERIC);
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
      \rsort($strings, $flags);
    } else {
      \sort($strings, $flags);
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
      \arsort($strings, $flags);
    } else {
      \asort($strings, $flags);
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
      \krsort($map, $flags);
    } else {
      \ksort($map, $flags);
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
}
