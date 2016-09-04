<?php
namespace HackUtils\list {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\list;
  use \HackUtils\map;
  use \HackUtils\set;
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
  function count($list) {
    return \count($list);
  }
  function contains($list, $value) {
    return \in_array($value, $list, true);
  }
  function range($start, $end, $step = 1) {
    return \range($start, $end, $step);
  }
  function sum($list) {
    return \array_sum($list);
  }
  function product($list) {
    return \array_product($list);
  }
  function sort($list, $cmp) {
    usort($list, $cmp);
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
}
