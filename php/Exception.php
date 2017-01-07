<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class Exception extends \Exception {
    public final static function assertEqual($a, $b) {
      if ($a !== $b) {
        throw static::create(dump($a), dump($b));
      }
      return $b;
    }
    public final static function assertZero($x) {
      return self::assertEqual($x, 0);
    }
    public final static function assertArray($x) {
      if (!\hacklib_cast_as_boolean(\is_array($x))) {
        throw static::create(typeof($x), "array");
      }
      return $x;
    }
    public final static function assertString($x) {
      if (!\hacklib_cast_as_boolean(\is_string($x))) {
        throw static::create(typeof($x), "string");
      }
      return $x;
    }
    public final static function assertInt($x) {
      if (!\hacklib_cast_as_boolean(\is_int($x))) {
        throw static::create(typeof($x), "int");
      }
      return $x;
    }
    public final static function assertTrue($x) {
      return self::assertEqual($x, true);
    }
    public final static function assertResource($x) {
      if (!\hacklib_cast_as_boolean(\is_resource($x))) {
        throw static::create(typeof($x), "resource");
      }
      return $x;
    }
    public final static function assertBool($x) {
      if (!\hacklib_cast_as_boolean(\is_bool($x))) {
        throw static::create(typeof($x), "bool");
      }
      return $x;
    }
    public final static function create($actual, $expected) {
      throw new static("Expected ".$expected.", got ".$actual.".");
    }
  }
}
