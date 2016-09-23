<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class Deque implements \Countable, \IteratorAggregate {
    private $array;
    public function __construct($array = array()) {
      $this->array = $array;
    }
    public function unshift($x) {
      \array_unshift($this->array, $x);
    }
    public function push($x) {
      \array_push($this->array, $x);
    }
    public function pop() {
      $this->checkEmpty("pop last element");
      return \array_pop($this->array);
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
      return $this->array[$this->count() - 1];
    }
    public function count() {
      return \count($this->array);
    }
    public function isEmpty() {
      return !\hacklib_cast_as_boolean($this->array);
    }
    public function toArray() {
      return $this->array;
    }
    public function getIterator() {
      return new \ArrayIterator($this->array);
    }
    private function checkEmpty($op) {
      if (\hacklib_cast_as_boolean($this->isEmpty())) {
        throw new \Exception("Cannot ".$op.": Deque is empty");
      }
    }
  }
}
