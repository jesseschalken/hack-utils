<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function typeof($x) {
    if (\hacklib_cast_as_boolean(\is_int($x))) {
      return "int";
    }
    if (\hacklib_cast_as_boolean(\is_string($x))) {
      return "string";
    }
    if (\hacklib_cast_as_boolean(\is_float($x))) {
      return "float";
    }
    if (\hacklib_cast_as_boolean(\is_null($x))) {
      return "null";
    }
    if (\hacklib_cast_as_boolean(\is_bool($x))) {
      return "bool";
    }
    if (\hacklib_cast_as_boolean(\is_resource($x))) {
      return "resource";
    }
    if (\hacklib_cast_as_boolean(\is_vec($x))) {
      return "vec";
    }
    if (\hacklib_cast_as_boolean(\is_dict($x))) {
      return "dict";
    }
    if (\hacklib_cast_as_boolean(\is_keyset($x))) {
      return "keyset";
    }
    if (\hacklib_cast_as_boolean(\is_array($x))) {
      return "array";
    }
    if ($x instanceof \Closure) {
      return "Closure";
    }
    if (\hacklib_cast_as_boolean(\is_object($x))) {
      return \get_class($x);
    }
    unreachable();
  }
  function dump($x) {
    if (\hacklib_cast_as_boolean(\is_int($x))) {
      return (string) $x;
    }
    if (\hacklib_cast_as_boolean(\is_bool($x))) {
      return \hacklib_cast_as_boolean($x) ? "true" : "false";
    }
    if (\hacklib_cast_as_boolean(\is_resource($x))) {
      return \get_resource_type($x)." resource";
    }
    if (\hacklib_cast_as_boolean(\is_object($x))) {
      return \get_class($x);
    }
    if (\hacklib_cast_as_boolean(\is_string($x))) {
      $s = "";
      $l = min(\strlen($x), 100);
      for ($i = 0; $i < $l; $i++) {
        $c = $x[$i];
        $o = \ord($c);
        if ($c === "\r") {
          $s .= "\\r";
        } else {
          if ($c === "\013") {
            $s .= "\\v";
          } else {
            if ($c === "\\") {
              $s .= "\\\\";
            } else {
              if ($c === "\"") {
                $s .= "\\\"";
              } else {
                if ($c === "\044") {
                  $s .= "\\\044";
                } else {
                  if ($c === "\014") {
                    $s .= "\\f";
                  } else {
                    if (($o < 32) || ($o >= 127)) {
                      $s .= "\\x".pad_left(\dechex($o), 2, "0");
                    } else {
                      $s .= $c;
                    }
                  }
                }
              }
            }
          }
        }
      }
      $s = "\"".$s."\"";
      if ($l < \strlen($s)) {
        $s .= "...";
      }
      return $s;
    }
    if (\hacklib_cast_as_boolean(\is_float($x))) {
      $s = (string) $x;
      if ((find($s, ".") === null) &&
          (find($s, "e") === null) &&
          (find($s, "E") === null)) {
        $s .= ".0";
      }
      return $s;
    }
    if (\hacklib_cast_as_boolean(\is_keyset($x))) {
      return "keyset[".dump_iterable_contents($x, false)."]";
    }
    if (\hacklib_cast_as_boolean(\is_vec($x))) {
      return "vec[".dump_iterable_contents($x, false)."]";
    }
    if (\hacklib_cast_as_boolean(\is_dict($x))) {
      return "dict[".dump_iterable_contents($x, true)."]";
    }
    if (\hacklib_cast_as_boolean(\is_array($x))) {
      return "[".dump_iterable_contents($x, is_assoc($x))."]";
    }
    if ($x instanceof \HH\Map) {
      return "Map {".dump_iterable_contents($x, true)."}";
    }
    if ($x instanceof \HH\Set) {
      return "Set {".dump_iterable_contents($x, false)."}";
    }
    if ($x instanceof \HH\Vector) {
      return "Vector {".dump_iterable_contents($x, false)."}";
    }
    return typeof($x);
  }
  function dump_iterable_contents($x, $assoc) {
    $p = array();
    foreach ($x as $k => $v) {
      if (\count($p) >= 3) {
        $p[] = "...";
        break;
      }
      $s = "";
      if (\hacklib_cast_as_boolean($assoc)) {
        $s .= dump($k)." => ";
      }
      $s .= dump($k);
      $p[] = $s;
    }
    return join($p, ", ");
  }
}
