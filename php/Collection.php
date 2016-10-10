<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  interface IConstCollection {
    public function toArray();
    public function get($index);
    public function length();
    public function isEmpty();
    public function slice($offset, $length = null);
    public function chunk($size);
    public function filter($f);
    public function map($f);
    public function reduce($f, $init);
    public function contains($value);
    public function containsAll($values);
    public function indexOf($value);
    public function lastIndexOf($value);
    public function equals($values);
  }
  interface ICollection extends IConstCollection {
    public function add($value);
    public function remove($value);
    public function retain($value);
    public function addAll($values);
    public function removeAll($values);
    public function retainAll($values);
    public function fromArray($values);
    public function set($index, $value);
    public function delete($index);
    public function clear();
    public function shuffle();
    public function sort($cmp);
    public function reverse();
    public function splice($offset, $length = null, $replacement = null);
  }
  interface IConstMap extends IConstCollection {
    public function exists($key);
    public function fetch($key);
    public function fetchOrNull($key);
    public function fetchOrDefault($key, $default);
    public function filterValues($f);
    public function filterKeys($f);
    public function mapValues($f);
    public function mapKeys($f);
    public function reduceValues($f, $init);
    public function reduceKeys($f, $init);
  }
  interface IMap extends ICollection, IConstMap {
    public function assign($key, $value);
    public function unassign($key);
    public function sortValues($cmp);
    public function sortKeys($cmp);
  }
  interface IConstVector extends IConstCollection {
    public function concat($values);
  }
  interface IVector extends ICollection, IConstVector {
    public function append($values);
    public function prepend($values);
    public function push($value);
    public function pop();
    public function unshift($value);
    public function shift();
  }
  final class ArrayVector implements IVector {
    private $array;
    public function __construct($array = array()) {
      $this->array = $array;
    }
    public function concat($values) {
      return new self(vector\concat($this->array, $values->toArray()));
    }
    public function equals($values) {
      return ($values === $this) || ($this->array === $values->toArray());
    }
    public function clear() {
      $this->array = array();
    }
    public function isEmpty() {
      return !\hacklib_cast_as_boolean($this->array);
    }
    public function add($value) {
      $this->array[] = $value;
    }
    public function addAll($values) {
      $this->array = vector\concat($this->array, $values->toArray());
    }
    public function chunk($size) {
      return new self(
        vector\map(
          vector\chunk($this->array, $size),
          function($chunk) {
            return new ArrayVector($chunk);
          }
        )
      );
    }
    public function contains($value) {
      return vector\contains($this->array, $value);
    }
    public function containsAll($values) {
      foreach ($values->toArray() as $value) {
        if (!\hacklib_cast_as_boolean($this->contains($value))) {
          return false;
        }
      }
      return true;
    }
    public function delete($index) {
      \array_splice($this->array, $index, 1);
    }
    public function filter($f) {
      return new self(vector\filter($this->array, $f));
    }
    public function fromArray($array) {
      $this->array = $array;
    }
    public function get($index) {
      return vector\get($this->array, $index);
    }
    public function indexOf($value) {
      return vector\index_of($this->array, $value);
    }
    public function lastIndexOf($value) {
      return vector\last_index_of($this->array, $value);
    }
    public function length() {
      return vector\length($this->array);
    }
    public function map($f) {
      return new self(vector\map($this->array, $f));
    }
    public function remove($value) {
      $this->array = vector\filter(
        $this->array,
        function($x) use ($value) {
          return $x !== $value;
        }
      );
    }
    public function removeAll($values) {
      $this->array = vector\filter(
        $this->array,
        function($x) use ($values) {
          return !\hacklib_cast_as_boolean($values->contains($x));
        }
      );
    }
    public function retain($value) {
      $this->array = vector\filter(
        $this->array,
        function($x) use ($value) {
          return $x === $value;
        }
      );
    }
    public function retainAll($values) {
      $this->array = vector\filter(
        $this->array,
        function($x) use ($values) {
          return $values->contains($x);
        }
      );
    }
    public function reverse() {
      $this->array = \array_reverse($this->array);
    }
    public function set($index, $value) {
      $length = $this->length();
      if ($index < 0) {
        $index += $length;
      }
      if (($index < 0) || ($index >= $length)) {
        throw new \Exception(
          "Index ".$index." out of bounds in vector of length ".$length
        );
      }
      $this->array[$index] = $value;
    }
    public function shuffle() {
      \shuffle($this->array);
    }
    public function slice($offset, $length = null) {
      return new self(vector\slice($this->array, $offset, $length));
    }
    public function reduce($f, $init) {
      return vector\reduce($this->array, $f, $init);
    }
    public function sort($cmp) {
      $ok = \usort($this->array, $cmp);
      if ($ok === false) {
        throw new \Exception("usort() failed");
      }
    }
    public function splice($offset, $length = null, $replacement = null) {
      return new self(
        \array_splice(
          $this->array,
          $offset,
          $length,
          \hacklib_cast_as_boolean($replacement)
            ? $replacement->toArray()
            : array()
        )
      );
    }
    public function toArray() {
      return $this->array;
    }
    public function prepend($values) {
      $this->array = vector\concat($values->toArray(), $this->array);
    }
    public function append($values) {
      $this->array = vector\concat($this->array, $values->toArray());
    }
    public function pop() {
      if (\hacklib_cast_as_boolean($this->isEmpty())) {
        throw new \Exception("Cannot pop last element: Array is empty");
      }
      return \array_pop($this->array);
    }
    public function peek() {
      return $this->get(-1);
    }
    public function push($value) {
      $this->array[] = $value;
    }
    public function shift() {
      if (\hacklib_cast_as_boolean($this->isEmpty())) {
        throw new \Exception("Cannot shift first element: Array is empty");
      }
      return \array_shift($this->array);
    }
    public function unshift($value) {
      \array_unshift($this->array, $value);
    }
  }
  abstract class _ArrayMapBase implements IMap {
    private $array = array();
    public function add($pair) {
      list($key, $value) = $pair;
      $this->assign($key, $value);
    }
    public function addAll($values) {
      foreach ($values->toArray() as $pair) {
        $this->add($pair);
      }
    }
    public function assign($key, $value) {
      $this->array[$this->makeKey($key)] = $this->makeValue($key, $value);
    }
    public function chunk($size) {
      return new ArrayVector(
        vector\map(
          map\chunk($this->array, $size),
          function($chunk) {
            return $this->makeSelf($chunk);
          }
        )
      );
    }
    public function clear() {
      $this->array = array();
    }
    public function delete($i) {
      $this->array = fst(map\splice($this->array, $i, 1));
    }
    public function equals($values) {
      return ($values === $this) || ($this->toArray() === $values->toArray());
    }
    public function toArray() {
      $ret = array();
      foreach ($this->array as $k => $v) {
        $ret[] = array($this->getKey($k, $v), $this->getValue($k, $v));
      }
      return $ret;
    }
    public function filter($f) {
      $self = $this->makeSelf(array());
      $self->fromArray(vector\filter($this->toArray(), $f));
      return $self;
    }
    public function fromArray($array) {
      $this->array = array();
      foreach ($array as $pair) {
        list($key, $value) = $pair;
        $this->array[$this->makeKey($key)] = $this->makeValue($key, $value);
      }
    }
    protected abstract function makeKey($key);
    protected abstract function makeValue($key, $value);
    protected abstract function getKey($arrayKey, $arrayValue);
    protected abstract function getValue($arrayKey, $arrayValue);
    protected abstract function makeSelf($array);
  }
}
