<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function escape_chars($s, $chars) {
    if ($s === "") {
      return "";
    }
    $s = replace($s, "\\", "\\\\");
    $l = length($chars);
    for ($i = 0; $i < $l; $i++) {
      $c = $chars[$i];
      $s = replace($s, $c, "\\".$c);
    }
    return $s;
  }
  function encode_list($array) {
    $r = "";
    foreach ($array as $x) {
      $r .= escape_chars($x, ";").";";
    }
    return $r;
  }
  function decode_list($s) {
    $r = array();
    $b = "";
    $e = false;
    $l = \strlen($s);
    for ($i = 0; $i < $l; $i++) {
      $c = $s[$i];
      if (\hacklib_cast_as_boolean($e)) {
        $b .= $c;
        $e = false;
      } else {
        if ($c === "\\") {
          $e = true;
        } else {
          if ($c === ";") {
            $r[] = $b;
            $b = "";
          } else {
            $b .= $c;
          }
        }
      }
    }
    return $r;
  }
  function encode_map($array) {
    $r = "";
    foreach ($array as $k => $v) {
      $k .= "";
      $r .= escape_chars($k, "=;")."=";
      $r .= escape_chars($v, "=;").";";
    }
    return $r;
  }
  function decode_map($s) {
    $r = array();
    $k = null;
    $b = "";
    $l = \strlen($s);
    $e = false;
    for ($i = 0; $i < $l; $i++) {
      $c = $s[$i];
      if (\hacklib_cast_as_boolean($e)) {
        $b .= $c;
        $e = false;
      } else {
        if ($c === "\\") {
          $e = true;
        } else {
          if ($c === "=") {
            if ($k !== null) {
              throw new \Exception("Double key");
            }
            $k = $b;
            $b = "";
          } else {
            if ($c === ";") {
              if ($k === null) {
                throw new \Exception("Value without key");
              }
              $r[$k] = $b;
              $k = null;
              $b = "";
            } else {
              $b .= $c;
            }
          }
        }
      }
    }
    return $r;
  }
}
