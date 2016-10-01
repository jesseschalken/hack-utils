<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\vector;
  use \HackUtils\map;
  use \HackUtils as utils;
  final class ArrayRef {
    private $array;
    public function __construct($array = array()) {
      $this->array = $array;
    }
    public function get($i) {
      $this->checkBounds($i);
      return $this->array[$i];
    }
    public function set($i, $v) {
      $this->checkBounds($i);
      $this->array[$i] = $v;
    }
    public function append($array) {
      $this->array = vector\concat($this->array, $array);
    }
    public function prepend($array) {
      $this->array = vector\concat($array, $this->array);
    }
    public function concat($array) {
      $self = clone $this;
      $self->append($array);
      return $self;
    }
    public function indexOf($value) {
      return vector\index_of($this->array, $value);
    }
    public function keys() {
      return vector\keys($this->array);
    }
    public function contains($value) {
      return vector\contains($this->array, $value);
    }
    public function unshift($x) {
      return \array_unshift($this->array, $x);
    }
    public function push($x) {
      return \array_push($this->array, $x);
    }
    public function pop() {
      $this->checkEmpty("pop last element");
      return \array_pop($this->array);
    }
    public function peek() {
      return $this->last();
    }
    public function shift() {
      $this->checkEmpty("pop first element");
      return \array_shift($this->array);
    }
    public function first() {
      $this->checkEmpty("get first element");
      return $this->array[0];
    }
    public function last() {
      $this->checkEmpty("get last element");
      return $this->array[$this->length() - 1];
    }
    public function length() {
      return vector\length($this->array);
    }
    public function isEmpty() {
      return !\hacklib_cast_as_boolean($this->array);
    }
    public function unwrap() {
      return $this->array;
    }
    public function sort($cmp) {
      $ret = \usort($this->array, $cmp);
      if ($ret === false) {
        throw new \Exception("usort() failed");
      }
    }
    public function shuffle() {
      $ret = \shuffle($this->array);
      if ($ret === false) {
        throw new \Exception("shuffle() failed");
      }
    }
    public function map($f) {
      return new self(vector\map($this->array, $f));
    }
    public function filter($f) {
      return new self(vector\filter($this->array, $f));
    }
    public function reduce($f, $initial) {
      return vector\reduce($this->array, $f, $initial);
    }
    public function reduceRight($f, $initial) {
      return vector\reduce_right($this->array, $f, $initial);
    }
    public function reverse() {
      $this->array = vector\reverse($this->array);
    }
    public function slice($offset, $length = null) {
      return new self(vector\slice($this->array, $offset, $length));
    }
    public function splice($offset, $length = null, $replacement = array()) {
      return \array_splice($this->array, $offset, $length, $replacement);
    }
    private function checkEmpty($op) {
      if (\hacklib_cast_as_boolean($this->isEmpty())) {
        throw new \Exception("Cannot ".$op.": Array is empty");
      }
    }
    private function checkBounds($index) {
      $length = $this->length();
      if (($index < 0) || ($index >= $length)) {
        throw new \Exception(
          "Array index ".
          $index.
          " out of bounds in array with length ".
          $length
        );
      }
    }
  }
}
