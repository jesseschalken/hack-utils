<?php
namespace HackUtils\map {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\vector;
  use \HackUtils\map;
  use \HackUtils\set;
  use \HackUtils as utils;
  use \HackUtils\fun2;
  use \HackUtils\fun1;
  use \HackUtils\fun0;
  use function \HackUtils\new_null;
  function keys_to_lower($array) {
    return \array_change_key_case($array, \CASE_LOWER);
  }
  function keys_to_uppper($array) {
    return \array_change_key_case($array, \CASE_UPPER);
  }
  function to_pairs($map) {
    $r = array();
    foreach ($map as $k => $v) {
      $r[] = array($k, $v);
    }
    return $r;
  }
  function from_pairs($pairs) {
    $r = array();
    foreach ($pairs as $p) {
      $r[$p[0]] = $p[1];
    }
    return $r;
  }
  function chunk($map, $size) {
    return \array_chunk($map, $size, true);
  }
  function get($map, $key) {
    $res = $map[$key];
    if (($res === null) && (!\hacklib_cast_as_boolean(has_key($map, $key)))) {
      throw new \Exception("Key '".$key."' does not exist in map");
    }
    return $res;
  }
  function set($map, $key, $val) {
    $map[$key] = $val;
    return $map;
  }
  function soft_get($map, $key) {
    return $map[$key] ?? new_null();
  }
  function get_default($map, $key, $default) {
    return
      \hacklib_cast_as_boolean(has_key($map, $key)) ? $map[$key] : $default;
  }
  function fixkey($key) {
    return $key."";
  }
  function fixkeys($keys) {
    return vector\map(
      $keys,
      function($key) {
        return $key."";
      }
    );
  }
  function column($maps, $key) {
    return \array_column($maps, $key);
  }
  function combine($keys, $values) {
    return \array_combine($keys, $values);
  }
  function splice($map, $offset, $length = null, $replacement = array()) {
    $left = slice($map, 0, $offset);
    $middle = slice($map, $offset, $length);
    $right = ($length !== null) ? slice($map, $length) : array();
    return array(\array_replace($left, $replacement, $right), $middle);
  }
  function separate($map) {
    $ks = array();
    $vs = array();
    foreach ($map as $k => $v) {
      $ks[] = $k;
      $vs[] = $v;
    }
    return array($ks, $vs);
  }
  function from_keys($keys, $value) {
    return \array_fill_keys($keys, $value);
  }
  function flip_last($map) {
    return \array_flip($map);
  }
  function flip($map) {
    $ret = of_vectors();
    foreach ($map as $k => $v) {
      $ret[$v][] = $k;
    }
    return $ret;
  }
  function has_key($map, $key) {
    return \array_key_exists($key, $map);
  }
  function keys($map) {
    return \array_keys($map);
  }
  function keys_strings($map) {
    return vector\map(
      keys($map),
      function($k) {
        return "".$k;
      }
    );
  }
  function values($map) {
    return \array_values($map);
  }
  function value_keys($map, $value) {
    return \array_keys($map, $value, true);
  }
  function union($a, $b) {
    return \array_replace($a, $b);
  }
  function union_all($maps) {
    return \call_user_func_array("array_replace", $maps);
  }
  function intersect($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function diff($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function reverse($map) {
    return \array_reverse($map, true);
  }
  function find($map, $value) {
    $ret = \array_search($map, $value, true);
    return ($ret === false) ? null : $ret;
  }
  function slice($map, $offset, $length = null) {
    return \array_slice($map, $offset, $length, true);
  }
  function size($map) {
    return \count($map);
  }
  function contains($map, $value) {
    return \in_array($value, $map, true);
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
  function sort_values($map, $cmp) {
    \uasort($map, $cmp);
    return $map;
  }
  function sort_pairs($map, $cmp) {
    return from_pairs(vector\sort(to_pairs($map), $cmp));
  }
  function filter_values($map, $f) {
    return \array_filter($map, $f);
  }
  function filter_pairs($map, $f) {
    foreach ($map as $k => $v) {
      if (!\hacklib_cast_as_boolean($f(array($k, $v)))) {
        unset($map[$k]);
      }
    }
    return $map;
  }
  function filter_keys($map, $f) {
    foreach ($map as $k => $v) {
      if (!\hacklib_cast_as_boolean($f($k))) {
        unset($map[$k]);
      }
    }
    return $map;
  }
  function get_pair($array, $offset) {
    foreach (slice($array, $offset, 1) as $k => $v) {
      return array($k, $v);
    }
    throw new \Exception(
      "Offset ".$offset." out of bounds for array of size ".size($array)
    );
  }
  function map_values($map, $f) {
    return \array_map($f, $map);
  }
  function map_keys($map, $f) {
    $ret = array();
    foreach ($map as $k => $v) {
      $ret[$f($k)] = $v;
    }
    return $ret;
  }
  function map_pairs($map, $f) {
    $res = array();
    foreach ($map as $k => $v) {
      list($k, $v) = $f(array($k, $v));
      $res[$k] = $v;
    }
    return $res;
  }
  function reduce_values($map, $f, $initial) {
    return \array_reduce($map, $f, $initial);
  }
  function reduce_keys($map, $f, $initial) {
    return vector\reduce(keys($map), $f, $initial);
  }
  function reduce_pairs($map, $f, $initial) {
    return vector\reduce(to_pairs($map), $f, $initial);
  }
  function select($map, $keys) {
    return vector\map(
      $keys,
      function($key) use ($map) {
        return $map[$key];
      }
    );
  }
  function zip($a, $b) {
    $ret = array();
    foreach ($a as $k => $v) {
      if (\hacklib_cast_as_boolean(has_key($b, $k))) {
        $ret[$k] = array($v, $b[$k]);
      }
    }
    return $ret;
  }
  function unzip($map) {
    $a = array();
    $b = array();
    foreach ($map as $k => $v) {
      $a[$k] = $v[0];
      $b[$k] = $v[1];
    }
    return array($a, $b);
  }
  function of_vectors() {
    return array();
  }
  function of_maps() {
    return array();
  }
}
