<?php
namespace HackUtils\tuple {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\list;
  use \HackUtils\key;
  use \HackUtils\map;
  use \HackUtils\set;
  function fst($t) {
    return $t[0];
  }
  function snd($t) {
    return $t[1];
  }
  function zip($a, $b) {
    $r = array();
    $l = min(count($a), count($b));
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
}
