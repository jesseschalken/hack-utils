<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class ArrayIterator implements \Iterator, \Countable {
    private $array;
    public function __construct($array) {
      $this->array = $array;
    }
    public function each() {
      $ret = \each($this->array);
      if ($ret === false) {
        return null;
      }
      return $ret;
    }
    public function current() {
      $ret = \current($this->array);
      if (($ret === false) && (!$this->valid())) {
        throw new \Exception(
          "Cannot get value: Array is beyond last element"
        );
      }
      return $ret;
    }
    public function key() {
      $ret = \key($this->array);
      if ($ret === null) {
        throw new \Exception("Cannot get key: Array is beyond last element");
      }
      return $ret;
    }
    public function valid() {
      return \key($this->array) !== null;
    }
    public function next() {
      \next($this->array);
    }
    public function prev() {
      \prev($this->array);
    }
    public function reset() {
      $ret = \reset($this->array);
      if (($ret === false) && (!$this->valid())) {
        return null;
      }
      return $ret;
    }
    public function end() {
      $ret = \end($this->array);
      if (($ret === false) && (!$this->valid())) {
        return null;
      }
      return $ret;
    }
    public function rewind() {
      $this->reset();
    }
    public function count() {
      return \count($this->array);
    }
    public function unwrap() {
      return $this->array;
    }
  }
}
