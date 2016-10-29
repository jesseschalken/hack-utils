<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function json_encode($value, $binary = false, $pretty = false) {
    $flags = 0;
    if (defined("JSON_PRETTY_PRINT") && $pretty) {
      $flags |= \JSON_PRETTY_PRINT;
    }
    if (defined("JSON_UNESCAPED_SLASHES")) {
      $flags |= \JSON_UNESCAPED_SLASHES;
    }
    if (defined("JSON_UNESCAPED_UNICODE")) {
      $flags |= \JSON_UNESCAPED_UNICODE;
    }
    if (defined("JSON_PRESERVE_ZERO_FRACTION")) {
      $flags |= \JSON_PRESERVE_ZERO_FRACTION;
    }
    if ($binary) {
      $value = _map_strings(
        $value,
        function($x) {
          return \utf8_encode($x);
        }
      );
    }
    _json_check_value($value);
    $json = \json_encode($value, $flags);
    _json_check_error();
    return $json;
  }
  function json_decode($json, $binary = false) {
    $value = \json_decode($json, true);
    _json_check_error();
    _json_check_value($value);
    if ($binary) {
      $value = _map_strings(
        $value,
        function($x) {
          return \utf8_decode($x);
        }
      );
    }
    return $value;
  }
  function _json_check_value($x) {
    if (\is_object($x) || \is_resource($x)) {
      throw new JSONException(
        "Objects are not supported. Use an associative array.",
        \JSON_ERROR_UNSUPPORTED_TYPE
      );
    }
    if (\is_array($x)) {
      foreach ($x as $v) {
        _json_check_value($v);
      }
    }
  }
  function _map_strings($x, $f) {
    if (\is_string($x)) {
      return $f($x);
    }
    if (\is_array($x)) {
      $r = array();
      foreach ($x as $k => $v) {
        $k = _map_strings($k, $f);
        $v = _map_strings($v, $f);
        $r[$k] = $v;
      }
      return $r;
    }
    return $x;
  }
  function _json_check_error() {
    if (\json_last_error() !== \JSON_ERROR_NONE) {
      throw new JSONException(\json_last_error_msg(), \json_last_error());
    }
  }
  class JSONException extends \Exception {}
}
