<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils as utils;
  use \HackUtils\str;
  use \HackUtils\vector;
  use \HackUtils\map;
  use \HackUtils\tuple;
  final class GenericMap {
    private $map = array();
    public function __construct($pairs = array()) {
      foreach ($pairs as $pair) {
        $this->set($pair[0], $pair[1]);
      }
    }
    public function set($k, $v) {
      $this->map[$this->toString($k)] = array($k, $v);
    }
    public function get($k) {
      return $this->map[$this->toString($k)][1];
    }
    public function softGet($k) {
      $pair = map\soft_get($this->map, $this->toString($k));
      return \hacklib_cast_as_boolean($pair) ? $pair[1] : utils\new_null();
    }
    public function hasKey($k) {
      return map\has_key($this->map, $this->toString($k));
    }
    public function delete($k) {
      unset($this->map[$this->toString($k)]);
    }
    public function toPairs() {
      return map\values($this->map);
    }
    private function toString($x) {
      if (\hacklib_cast_as_boolean(\is_string($x))) {
        return "s".$x;
      } else {
        if (\hacklib_cast_as_boolean(\is_bool($x))) {
          return "b".(\hacklib_cast_as_boolean($x) ? "1" : "0");
        } else {
          if (\hacklib_cast_as_boolean(\is_int($x))) {
            return "i".$x;
          } else {
            if (\hacklib_cast_as_boolean(\is_float($x))) {
              return "f".\sprintf("%.20E", $x);
            } else {
              if (\hacklib_cast_as_boolean(\is_resource($x))) {
                return "r".((int) $x);
              } else {
                if (\hacklib_cast_as_boolean(\is_object($x))) {
                  return "o".\spl_object_hash($x);
                } else {
                  if (\hacklib_cast_as_boolean(\is_null($x))) {
                    return "n";
                  } else {
                    if (\hacklib_cast_as_boolean(\is_array($x))) {
                      return "a".$this->arrayToString($x);
                    } else {
                      throw new \Exception("Unhandled type: ".\gettype($x));
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    private function arrayToString($x) {
      $r = "";
      foreach ($x as $k => $v) {
        $k = map\fixkey($k);
        $v = $this->toString($v);
        $k = str\escape($k, "=;");
        $v = str\escape($v, "=;");
        $r .= $k."=".$v.";";
      }
      return $r;
    }
  }
}
