<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function new_null() {
    return null;
  }
  function null_throws($value, $message = "Unexpected null") {
    return ($value === null) ? throw_(new \Exception($message)) : $value;
  }
  function throw_($e) {
    throw $e;
  }
  function if_null($x, $y) {
    return ($x === null) ? $y : $x;
  }
  function fst($t) {
    return $t[0];
  }
  function snd($t) {
    return $t[1];
  }
  interface Gettable {
    public function get();
  }
  interface Settable {
    public function set($value);
  }
  final class Ref implements Gettable, Settable {
    private $value;
    public function __construct($value) {
      $this->value = $value;
    }
    public function get() {
      return $this->value;
    }
    public function set($value) {
      $this->value = $value;
    }
  }
}
