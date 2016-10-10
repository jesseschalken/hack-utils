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
  function chunk_assoc($map, $size) {
    return \array_chunk($map, $size, true);
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
  function splice_assoc(
    $map,
    $offset,
    $length = null,
    $replacement = array()
  ) {
    $left = slice_assoc($map, 0, $offset);
    $middle = slice_assoc($map, $offset, $length);
    $right = ($length !== null) ? slice_assoc($map, $length) : array();
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
    $ret = array();
    foreach ($map as $k => $v) {
      $ret[$v][] = $k;
    }
    return $ret;
  }
  function unflip($map) {
    $ret = array();
    foreach ($map as $k => $v) {
      foreach ($v as $v2) {
        $ret[$v2] = $k;
      }
    }
    return $ret;
  }
  function key_exists($map, $key) {
    return \array_key_exists($key, $map);
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
  function value_keys($map, $value) {
    return \array_keys($map, $value, true);
  }
  function union($a, $b) {
    return \array_replace($a, $b);
  }
  function union_all($maps) {
    return \call_user_func_array("array_replace", $maps);
  }
  function intersect_keys($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function diff_keys($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function reverse_assoc($map) {
    return \array_reverse($map, true);
  }
  function find($map, $value) {
    $ret = \array_search($map, $value, true);
    return ($ret === false) ? null : $ret;
  }
  function slice_assoc($map, $offset, $length = null) {
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
    return from_pairs(sort(to_pairs($map), $cmp));
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
    foreach (slice_assoc($array, $offset, 1) as $k => $v) {
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
    return reduce(keys($map), $f, $initial);
  }
  function reduce_pairs($map, $f, $initial) {
    return reduce(to_pairs($map), $f, $initial);
  }
  function select($map, $keys) {
    return map(
      $keys,
      function($key) use ($map) {
        return $map[$key];
      }
    );
  }
  function zip_assoc($a, $b) {
    $ret = array();
    foreach ($a as $k => $v) {
      if (\hacklib_cast_as_boolean(key_exists($b, $k))) {
        $ret[$k] = array($v, $b[$k]);
      }
    }
    return $ret;
  }
  function unzip_assoc($map) {
    $a = array();
    $b = array();
    foreach ($map as $k => $v) {
      $a[$k] = $v[0];
      $b[$k] = $v[1];
    }
    return array($a, $b);
  }
}
