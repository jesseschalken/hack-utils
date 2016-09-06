<?php
namespace HackUtils\tuple {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\vector;
  use \HackUtils\key;
  use \HackUtils\map;
  use \HackUtils\set;
  function fst($t) {
    return $t[0];
  }
  function snd($t) {
    return $t[1];
  }
}
