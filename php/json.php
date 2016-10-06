<?php
namespace HackUtils\json {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\fun1;
  function encode($value, $binary = false, $pretty = false) {
    $flags = 0;
    if (\hacklib_cast_as_boolean(defined("JSON_PRETTY_PRINT")) &&
        \hacklib_cast_as_boolean($pretty)) {
      $flags |= \JSON_PRETTY_PRINT;
    }
    if (\hacklib_cast_as_boolean(defined("JSON_UNESCAPED_SLASHES"))) {
      $flags |= \JSON_UNESCAPED_SLASHES;
    }
    if (\hacklib_cast_as_boolean(defined("JSON_UNESCAPED_UNICODE"))) {
      $flags |= \JSON_UNESCAPED_UNICODE;
    }
    if (\hacklib_cast_as_boolean(defined("JSON_PRESERVE_ZERO_FRACTION"))) {
      $flags |= \JSON_PRESERVE_ZERO_FRACTION;
    }
    if (\hacklib_cast_as_boolean($binary)) {
      $value = _map_strings(
        $value,
        function($x) {
          return \utf8_encode($x);
        }
      );
    }
    _check_value($value);
    $json = \json_encode($value, $flags);
    _check_error();
    return $json;
  }
  function decode($json, $binary = false) {
    $value = \json_decode($json, true);
    _check_error();
    _check_value($value);
    if (\hacklib_cast_as_boolean($binary)) {
      $value = _map_strings(
        $value,
        function($x) {
          return \utf8_decode($x);
        }
      );
    }
    return $value;
  }
  function _check_value($x) {
    if (\hacklib_cast_as_boolean(\is_object($x)) ||
        \hacklib_cast_as_boolean(\is_resource($x))) {
      throw new Exception(
        "Type is not supported",
        \JSON_ERROR_UNSUPPORTED_TYPE
      );
    }
    if (\hacklib_cast_as_boolean(\is_array($x))) {
      foreach ($x as $v) {
        _check_value($v);
      }
    }
  }
  function _map_strings($x, $f) {
    if (\hacklib_cast_as_boolean(\is_string($x))) {
      return $f($x);
    }
    if (\hacklib_cast_as_boolean(\is_array($x))) {
      $r = array();
      foreach ($x as $k => $v) {
        $r[$f($k."")] = $f($v);
      }
      return $r;
    }
    return $x;
  }
  function _check_error() {
    if (\json_last_error() !== \JSON_ERROR_NONE) {
      throw new Exception(\json_last_error_msg(), \json_last_error());
    }
  }
  class Exception extends \Exception {}
}
