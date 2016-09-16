<?php
namespace HackUtils\map {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\vector;
  use \HackUtils\map;
  use \HackUtils\set;
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
  function fixkey($key) {
    return (string) $key;
  }
  function fixkeys($keys) {
    return vector\map(
      $keys,
      function($key) {
        return (string) $key;
      }
    );
  }
  function column($maps, $key) {
    return \array_column($maps, $key);
  }
  function combine($keys, $values) {
    return \array_combine($keys, $values);
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
  function fill_keys($keys, $value) {
    return \array_fill_keys($keys, $value);
  }
  function flip($map) {
    return \array_flip($map);
  }
  function has_key($map, $key) {
    return \array_key_exists($key, $map);
  }
  function keys($map) {
    return \array_keys($map);
  }
  function values($map) {
    return \array_values($map);
  }
  function value_keys($map, $value) {
    return \array_keys($map, $value, true);
  }
  function merge($a, $b) {
    return \array_replace($a, $b);
  }
  function merge_all($maps) {
    return \call_user_func_array("array_replace", $maps);
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
  function count($map) {
    return \count($map);
  }
  function contains($map, $value) {
    return \in_array($value, $map, true);
  }
  function sort_keys($map, $cmp) {
    \uksort($map, $cmp);
    return $map;
  }
  function sort($map, $cmp) {
    \uasort($map, $cmp);
    return $map;
  }
  function filter($map, $f) {
    return \array_filter($map, $f);
  }
  function map($map, $f) {
    return \array_map($f, $map);
  }
  function reduce($map, $f, $initial) {
    return \array_reduce($map, $f, $initial);
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
}
