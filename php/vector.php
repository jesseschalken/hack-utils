<?php
namespace HackUtils\vector {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\vector;
  use \HackUtils\map;
  use \HackUtils\set;
  function is_vector($x) {
    if (!\hacklib_cast_as_boolean(\is_array($x))) {
      return false;
    }
    $i = 0;
    foreach ($x as $k => $v) {
      if ($k !== ($i++)) {
        return false;
      }
    }
    return true;
  }
  function chunk($map, $size) {
    return \array_chunk($map, $size, false);
  }
  function count_values($values) {
    return \array_count_values($values);
  }
  function repeat($value, $count) {
    return \array_fill(0, $count, $value);
  }
  function concat($a, $b) {
    return \array_merge($a, $b);
  }
  function concat_all($vectors) {
    return
      \hacklib_cast_as_boolean($vectors)
        ? \call_user_func_array("array_merge", $vectors)
        : array();
  }
  function pad($list, $size, $value) {
    return \array_pad($list, $size, $value);
  }
  function reverse($list) {
    return \array_reverse($list, false);
  }
  function find($list, $value) {
    $ret = \array_search($list, $value, true);
    return ($ret === false) ? null : $ret;
  }
  function slice($list, $offset, $length = null) {
    return \array_slice($list, $offset, $length);
  }
  function splice($list, $offset, $length = null, $replacement = array()) {
    $ret = \array_splice($list, $offset, $length, $replacement);
    return array($list, $ret);
  }
  function unique($list) {
    return \array_unique($list);
  }
  function shuffle($list) {
    \shuffle($list);
    return $list;
  }
  function length($list) {
    return \count($list);
  }
  function contains($list, $value) {
    return \in_array($value, $list, true);
  }
  function range($start, $end, $step = 1) {
    return \range($start, $end, $step);
  }
  function sort($list, $cmp) {
    \usort($list, $cmp);
    return $list;
  }
  function filter($list, $f) {
    $ret = \array_filter($list, $f);
    if (\count($ret) < \count($list)) {
      $ret = \array_values($ret);
    }
    return $ret;
  }
  function map($list, $f) {
    return \array_map($f, $list);
  }
  function reduce($list, $f, $initial) {
    return \array_reduce($list, $f, $initial);
  }
  function zip($a, $b) {
    $r = array();
    $l = \min(\count($a), \count($b));
    for ($i = 0; $i < $l; $i++) {
      $r[] = array($a[$i], $b[$i]);
    }
    return $r;
  }
  function unzip($x) {
    $a = array();
    $b = array();
    foreach ($x as $p) {
      $a[] = $p[0];
      $b[] = $p[1];
    }
    return array($a, $b);
  }
  function diff($a, $b) {
    return \array_values(\array_diff($a, $b));
  }
  function intersect($a, $b) {
    return \array_values(\array_intersect($a, $b));
  }
  function any($a, $f) {
    foreach ($a as $x) {
      if (\hacklib_cast_as_boolean($f($x))) {
        return true;
      }
    }
    return false;
  }
  function all($a, $f) {
    foreach ($a as $x) {
      if (!\hacklib_cast_as_boolean($f($x))) {
        return false;
      }
    }
    return true;
  }
}
