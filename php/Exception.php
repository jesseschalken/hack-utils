<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class Exception extends \Exception {
    public final static function assertZero($x) {
      if ($x !== 0) {
        throw static::create(\var_export($x, true), "0");
      }
    }
    public final static function assertArray($x) {
      if (!\is_array($x)) {
        throw static::create(typeof($x), "array");
      }
      return $x;
    }
    public final static function assertString($x) {
      if (!\is_string($x)) {
        throw static::create(typeof($x), "string");
      }
      return $x;
    }
    public final static function assertInt($x) {
      if (!\is_int($x)) {
        throw static::create(typeof($x), "int");
      }
      return $x;
    }
    public final static function assertTrue($x) {
      if ($x !== true) {
        throw static::create(\var_export($x, true), "true");
      }
    }
    public final static function assertResource($x) {
      if (!\is_resource($x)) {
        throw static::create(typeof($x), "resource");
      }
      return $x;
    }
    public final static function assertBool($x) {
      if (!\is_bool($x)) {
        throw static::create(typeof($x), "bool");
      }
      return $x;
    }
    public static function create($actual, $expected) {
      throw new static("Expected ".$expected.", got ".$actual.".");
    }
  }
}
