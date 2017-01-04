<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class ErrorAssert extends \RuntimeException {
    public final static function isZero($name, $ret) {
      if ($ret !== 0) {
        throw self::create($name);
      }
    }
    public final static function isArray($name, $ret) {
      if (!\is_array($ret)) {
        throw self::create($name);
      }
      return $ret;
    }
    public final static function isString($name, $ret) {
      if (!\is_string($ret)) {
        throw self::create($name);
      }
      return $ret;
    }
    public final static function isInt($name, $ret) {
      if (!\is_int($ret)) {
        throw self::create($name);
      }
      return $ret;
    }
    public final static function isTrue($name, $ret) {
      if ($ret !== true) {
        throw self::create($name);
      }
    }
    public final static function isResource($name, $ret) {
      if (!\is_resource($ret)) {
        throw self::create($name);
      }
      return $ret;
    }
    public final static function isBool($name, $ret) {
      if (!\is_bool($ret)) {
        throw self::create($name);
      }
      return $ret;
    }
    private static function create($name) {
      $error = \error_get_last();
      $msg = $name."() failed";
      if ($error) {
        $e = new self($msg.": ".$error["message"]);
        $e->file = $error["file"];
        $e->line = $error["line"];
        return $e;
      }
      return new self($msg);
    }
  }
}
