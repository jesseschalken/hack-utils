<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class TestArrayIterator extends Test {
    public function run() {
      $a = new ArrayIterator(array("a" => 1, "b" => 2));
      self::assertEqual($a->count(), 2);
      self::assertEqual($a->unwrap(), array("a" => 1, "b" => 2));
      self::assertEqual($a->valid(), true);
      self::assertEqual($a->key(), "a");
      self::assertEqual($a->current(), 1);
      self::assertEqual($a->valid(), true);
      self::assertEqual($a->each(), array("a", 1));
      self::assertEqual($a->prev(), 1);
      self::assertEqual($a->valid(), true);
      self::assertEqual($a->each(), array("a", 1));
      self::assertEqual($a->valid(), true);
      self::assertEqual($a->each(), array("b", 2));
      self::assertEqual($a->valid(), false);
      self::assertEqual($a->each(), NULL_INT);
      self::assertEqual($a->prev(), NULL_INT);
      self::assertEqual($a->valid(), false);
      self::assertEqual($a->each(), NULL_INT);
      self::assertEqual($a->reset(), 1);
      self::assertEqual($a->valid(), true);
      self::assertEqual($a->each(), array("a", 1));
      self::assertEqual($a->end(), 2);
      self::assertEqual($a->valid(), true);
      self::assertEqual($a->each(), array("b", 2));
      self::assertEqual($a->valid(), false);
      self::assertEqual($a->each(), NULL_INT);
      self::assertEqual(
        self::getException(
          function() use ($a) {
            $a->current();
          }
        )->getMessage(),
        "Cannot get value: Array is beyond last element"
      );
      self::assertEqual(
        self::getException(
          function() use ($a) {
            $a->key();
          }
        )->getMessage(),
        "Cannot get key: Array is beyond last element"
      );
      $a = new ArrayIterator(array());
      self::assertEqual($a->count(), 0);
      self::assertEqual($a->unwrap(), array());
      self::assertEqual($a->reset(), NULL_INT);
      self::assertEqual($a->end(), NULL_INT);
      $a = new ArrayIterator(array("foot", "bike", "car", "plane"));
      self::assertEqual($a->current(), "foot");
      self::assertEqual($a->next(), "bike");
      self::assertEqual($a->next(), "car");
      self::assertEqual($a->prev(), "bike");
      self::assertEqual($a->end(), "plane");
    }
  }
  final class ArrayIterator {
    private $array;
    public function __construct($array) {
      $this->array = $array;
    }
    public function each() {
      $ret = \each($this->array);
      if ($ret === false) {
        return null;
      }
      return array($ret[0], $ret[1]);
    }
    public function current() {
      $ret = \current($this->array);
      if (($ret === false) && (!\hacklib_cast_as_boolean($this->valid()))) {
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
      $ret = \next($this->array);
      if (($ret === false) && (!\hacklib_cast_as_boolean($this->valid()))) {
        return null;
      }
      return $ret;
    }
    public function prev() {
      $ret = \prev($this->array);
      if (($ret === false) && (!\hacklib_cast_as_boolean($this->valid()))) {
        return null;
      }
      return $ret;
    }
    public function reset() {
      $ret = \reset($this->array);
      if (($ret === false) && (!\hacklib_cast_as_boolean($this->valid()))) {
        return null;
      }
      return $ret;
    }
    public function end() {
      $ret = \end($this->array);
      if (($ret === false) && (!\hacklib_cast_as_boolean($this->valid()))) {
        return null;
      }
      return $ret;
    }
    public function count() {
      return \count($this->array);
    }
    public function unwrap() {
      return $this->array;
    }
  }
}
