<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\map;
  final class AssocArrayRef {
    private $array;
    public function __construct($array) {
      $this->array = $array;
    }
    public function get($key) {
      return $this->array[$key];
    }
    public function getDefault($key, $default) {
      return map\get_default($this->array, $key, $default);
    }
    public function set($key, $value) {
      $this->array[$key] = $value;
    }
    public function softGet($key) {
      return map\soft_get($this->array, $key);
    }
    public function hasKey($key) {
      return map\has_key($this->array, $key);
    }
    public function delete($key) {
      unset($this->array[$key]);
    }
    public function unwrap() {
      return $this->array;
    }
    public function shuffle() {
      \shuffle($this->array);
    }
    public function sort($cmp) {
      $ret = \uasort($this->array, $cmp);
      if ($ret === false) {
        throw new \Exception("uasort() failed");
      }
    }
    public function sortKeys($cmp) {
      $ret = \uksort($this->array, $cmp);
      if ($ret === false) {
        throw new \Exception("uksort() failed");
      }
    }
    public function map($f) {
      return new self(map\map($this->array, $f));
    }
    public function filter($f) {
      return new self(map\filter($this->array, $f));
    }
    public function reduce($f, $initial) {
      return map\reduce($this->array, $f, $initial);
    }
    public function isEmpty() {
      return !\hacklib_cast_as_boolean($this->array);
    }
    public function size() {
      return \count($this->array);
    }
    public function contains($value) {
      return map\contains($this->array, $value);
    }
    public function import($array) {
      $this->array = array_replace($this->array, $array);
    }
    public function union($array) {
      $self = clone $this;
      $self->import($array);
      return $self;
    }
    public function find($value) {
      return map\find($this->array, $value);
    }
    public function slice($offset = 0, $length = null) {
      return new self(map\slice($this->array, $offset, $length));
    }
    public function toPairs() {
      return map\to_pairs($this->array);
    }
    public function values() {
      return map\values($this->array);
    }
    public function keys() {
      return map\keys($this->array);
    }
  }
}
