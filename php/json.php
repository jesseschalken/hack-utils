<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class JSON {
    public static function encode($value, $binary = false, $pretty = false) {
      $flags = 0;
      if (\defined("JSON_PRETTY_PRINT") && $pretty) {
        $flags |= \JSON_PRETTY_PRINT;
      }
      if (\defined("JSON_UNESCAPED_SLASHES")) {
        $flags |= \JSON_UNESCAPED_SLASHES;
      }
      if (\defined("JSON_UNESCAPED_UNICODE")) {
        $flags |= \JSON_UNESCAPED_UNICODE;
      }
      if (\defined("JSON_PRESERVE_ZERO_FRACTION")) {
        $flags |= \JSON_PRESERVE_ZERO_FRACTION;
      }
      if ($binary) {
        $value = self::mapStrings(
          $value,
          function($x) {
            return \utf8_encode($x);
          }
        );
      }
      self::checkValue($value);
      $json = \json_encode($value, $flags);
      self::checkError();
      return $json;
    }
    public static function decode($json, $binary = false) {
      $value = \json_decode($json, true);
      self::checkError();
      if ($binary) {
        $value = self::mapStrings(
          $value,
          function($x) {
            return \utf8_decode($x);
          }
        );
      }
      return $value;
    }
    private static function checkValue($x) {
      if (\is_object($x)) {
        throw new JSONException(
          "Objects are not supported. Use an associative array.",
          \JSON_ERROR_UNSUPPORTED_TYPE
        );
      }
      if (\is_array($x)) {
        foreach ($x as $v) {
          self::checkValue($v);
        }
      }
    }
    private static function mapStrings($x, $f) {
      if (\is_string($x)) {
        return $f($x);
      }
      if (\is_array($x)) {
        $r = array();
        foreach ($x as $k => $v) {
          $k = self::mapStrings($k, $f);
          $v = self::mapStrings($v, $f);
          $r[$k] = $v;
        }
        return $r;
      }
      return $x;
    }
    private static function checkError() {
      if (\json_last_error() !== \JSON_ERROR_NONE) {
        throw new JSONException(\json_last_error_msg(), \json_last_error());
      }
    }
    private function __construct() {}
  }
  final class JSONException extends \Exception {}
  function json_encode($value, $binary = false, $pretty = false) {
    return JSON::encode($value, $binary, $pretty);
  }
  function json_decode($json, $binary = false) {
    return JSON::decode($json, $binary);
  }
}
