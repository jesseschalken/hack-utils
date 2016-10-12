<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
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
  function get_key($map, $key) {
    $res = $map[$key];
    if (($res === null) &&
        (!\hacklib_cast_as_boolean(key_exists($map, $key)))) {
      throw new \Exception("Key '".$key."' does not exist in map");
    }
    return $res;
  }
  function set_key($map, $key, $val) {
    $map[$key] = $val;
    return $map;
  }
  function get_key_or_null($map, $key) {
    return $map[$key] ?? new_null();
  }
  function get_key_or_default($map, $key, $default) {
    return
      \hacklib_cast_as_boolean(key_exists($map, $key))
        ? $map[$key]
        : $default;
  }
  function key_exists($map, $key) {
    return \array_key_exists($key, $map);
  }
  function fixkey($key) {
    return $key."";
  }
  function fixkeys($keys) {
    return map(
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
  function flip($map) {
    return \array_flip($map);
  }
  function flip_count($values) {
    return \array_count_values($values);
  }
  function keys($map) {
    return \array_keys($map);
  }
  function keys_strings($map) {
    return map(
      keys($map),
      function($k) {
        return "".$k;
      }
    );
  }
  function values($map) {
    return \array_values($map);
  }
  function union_keys($a, $b) {
    return \array_replace($a, $b);
  }
  function union_keys_all($maps) {
    return \call_user_func_array("array_replace", $maps);
  }
  function intersect($a, $b) {
    return \array_values(\array_intersect($a, $b));
  }
  function intersect_keys($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function diff($a, $b) {
    return \array_values(\array_diff($a, $b));
  }
  function diff_keys($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function select($map, $keys) {
    $ret = array();
    foreach ($keys as $key) {
      $ret[] = $map[$key];
    }
    return $ret;
  }
}
