<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function zip($a, $b) {
    $r = array();
    $l = min(count($a), count($b));
    for ($i = 0; $i < $l; $i++) {
      $r[] = array($a[$i], $b[$i]);
    }
    return $r;
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
  function unzip($x) {
    $a = array();
    $b = array();
    foreach ($x as $p) {
      $a[] = $p[0];
      $b[] = $p[1];
    }
    return array($a, $b);
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
