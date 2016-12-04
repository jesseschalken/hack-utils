<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function typeof($x) {
    if (\is_int($x)) {
      return "int";
    }
    if (\is_string($x)) {
      return "string";
    }
    if (\is_float($x)) {
      return "float";
    }
    if (\is_null($x)) {
      return "null";
    }
    if (\is_bool($x)) {
      return "bool";
    }
    if (\is_resource($x)) {
      return "resource";
    }
    if (\is_array($x)) {
      return "array";
    }
    if (\is_object($x)) {
      return \get_class($x);
    }
    unreachable();
  }
}
