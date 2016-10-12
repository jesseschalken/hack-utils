<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function is_vector($x) {
    $i = 0;
    foreach ($x as $k => $v) {
      if ($k !== ($i++)) {
        return false;
      }
    }
    return true;
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
  function get_offset($v, $i) {
    $l = \count($v);
    if ($i < 0) {
      $i += $l;
    }
    if (($i < 0) || ($i >= $l)) {
      throw new \Exception(
        "Index ".$i." out of bounds in array of length ".$l
      );
    }
    return $v[$i];
  }
  function set_offset($v, $i, $x) {
    $l = \count($v);
    if ($i < 0) {
      $i += $l;
    }
    if (($i < 0) || ($i >= $l)) {
      throw new \Exception(
        "Index ".$i." out of bounds in array of length ".$l
      );
    }
    $v[$i] = $x;
    return $v;
  }
  function push($v, $x) {
    \array_push($v, $x);
    return $v;
  }
  function pop($v) {
    _check_empty($v, "remove last element");
    $x = \array_pop($v);
    return array($v, $x);
  }
  function unshift($x, $v) {
    \array_unshift($v, $x);
    return $v;
  }
  function shift($v) {
    _check_empty($v, "remove first element");
    $x = \array_shift($v);
    return array($x, $v);
  }
  function _check_empty($a, $op) {
    if (!\hacklib_cast_as_boolean($a)) {
      throw new \Exception("Cannot ".$op.": Array is empty");
    }
  }
  function range($start, $end, $step = 1) {
    return \range($start, $end, $step);
  }
  function filter($list, $f) {
    $ret = filter_assoc($list, $f);
    return
      \hacklib_not_equals(count($ret), count($list)) ? values($ret) : $list;
  }
  function filter_assoc($map, $f) {
    return \array_filter($map, $f);
  }
  function map($list, $f) {
    return \array_map($f, $list);
  }
  function map_assoc($map, $f) {
    return \array_map($f, $map);
  }
  function reduce($list, $f, $initial) {
    return \array_reduce($list, $f, $initial);
  }
  function reduce_right($list, $f, $value) {
    \end($list);
    while (!\hacklib_cast_as_boolean(\is_null($key = \key($list)))) {
      $value = $f($value, \current($list));
      \prev($list);
    }
    return $value;
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
  function group_by($a, $f) {
    $res = array();
    foreach ($a as $v) {
      $res[$f($v)][] = $v;
    }
    return $res;
  }
}
