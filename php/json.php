<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class TestJSONEncode extends SampleTest {
    public function evaluate($in) {
      return JSON::encode($in[0], $in[1], $in[2]);
    }
    public function getData() {
      return array(
        array(array(1, false, false), "1"),
        array(array(2, false, false), "2"),
        array(array(null, false, false), "null"),
        array(array(true, false, false), "true"),
        array(array(false, false, false), "false"),
        array(array("foo", false, false), "\"foo\""),
        array(array(0.0, false, false), "0.0"),
        array(array(0.0 * (-1.0), false, false), "-0.0"),
        array(array(1.0 / 3.0, false, false), "0.33333333333333"),
        array(array(0.000000000000000001, false, false), "1.0e-18"),
        array(array(array(), false, false), "[]"),
        array(array(array("a" => "foo"), false, false), "{\"a\":\"foo\"}"),
        array(
          array(array("a" => "foo"), false, true),
          "{\n    \"a\": \"foo\"\n}"
        ),
        array(array(array("foo"), false, false), "[\"foo\"]"),
        array(array(array("foo"), false, true), "[\n    \"foo\"\n]"),
        array(array(array("\303\237"), false, false), "[\"\303\237\"]"),
        array(
          array(array("\303\237"), true, false),
          "[\"\303\203\302\237\"]"
        )
      );
    }
  }
  class TestJSONDecode extends SampleTest {
    public function evaluate($in) {
      return JSON::decode($in[0], $in[1]);
    }
    public function getData() {
      return array(
        array(array("1", false), 1),
        array(array("2", false), 2),
        array(array("null", false), null),
        array(array("true", false), true),
        array(array("false", false), false),
        array(array("\"foo\"", false), "foo"),
        array(array("0.0", false), 0.0),
        array(array("-0.0", false), 0.0 * (-1.0)),
        array(array("0.33333333333333", false), 0.33333333333333),
        array(array("1.0e-18", false), 0.000000000000000001),
        array(array("[]", false), array()),
        array(array("{\"a\":\"foo\"}", false), array("a" => "foo")),
        array(array("{\n    \"a\": \"foo\"\n}", false), array("a" => "foo")),
        array(array("[\"foo\"]", false), array("foo")),
        array(array("[\n    \"foo\"\n]", false), array("foo")),
        array(array("[\"\303\237\"]", false), array("\303\237")),
        array(array("[\"\303\203\302\237\"]", true), array("\303\237"))
      );
    }
  }
  class TestJSONError extends Test {
    public function run() {
      self::assertException(
        function() {
          JSON::encode(new \stdClass());
        },
        "Objects are not supported. Use an associative array.",
        \JSON_ERROR_UNSUPPORTED_TYPE
      );
      self::assertException(
        function() {
          JSON::decode("this is not valid JSON");
        },
        "Syntax error",
        \JSON_ERROR_SYNTAX
      );
    }
  }
  final class JSON {
    public static function encode($value, $binary = false, $pretty = false) {
      $flags = 0;
      if (\hacklib_cast_as_boolean(\defined("JSON_PRETTY_PRINT")) &&
          \hacklib_cast_as_boolean($pretty)) {
        $flags |= \JSON_PRETTY_PRINT;
      }
      if (\hacklib_cast_as_boolean(\defined("JSON_UNESCAPED_SLASHES"))) {
        $flags |= \JSON_UNESCAPED_SLASHES;
      }
      if (\hacklib_cast_as_boolean(\defined("JSON_UNESCAPED_UNICODE"))) {
        $flags |= \JSON_UNESCAPED_UNICODE;
      }
      if (\hacklib_cast_as_boolean(\defined("JSON_PRESERVE_ZERO_FRACTION"))) {
        $flags |= \JSON_PRESERVE_ZERO_FRACTION;
      }
      if (\hacklib_cast_as_boolean($binary)) {
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
      if (\hacklib_cast_as_boolean($binary)) {
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
      if (\hacklib_cast_as_boolean(\is_object($x))) {
        throw new JSONException(
          "Objects are not supported. Use an associative array.",
          \JSON_ERROR_UNSUPPORTED_TYPE
        );
      }
      if (\hacklib_cast_as_boolean(\is_array($x))) {
        foreach ($x as $v) {
          self::checkValue($v);
        }
      }
    }
    private static function mapStrings($x, $f) {
      if (\hacklib_cast_as_boolean(\is_string($x))) {
        return $f($x);
      }
      if (\hacklib_cast_as_boolean(\is_array($x))) {
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
}
