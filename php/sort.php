<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class Sorter {
    public abstract function sort($array);
    public abstract function sortValues($array);
    public abstract function sortKeys($array);
  }
  abstract class _BuiltinSorter extends Sorter {
    private $reverse = false;
    private $flags;
    protected function __construct($flags) {
      $this->flags = $flags;
    }
    protected function _set($flag, $val) {
      if (\hacklib_cast_as_boolean($val)) {
        $this->flags |= $flag;
      } else {
        $this->flags &= ~$flag;
      }
      return $this;
    }
    public function setReverse($reverse = true) {
      $this->reverse = $reverse;
      return $this;
    }
    public function sort($array) {
      if (\hacklib_cast_as_boolean($this->reverse)) {
        Exception::assertTrue(\rsort($array, $this->flags));
      } else {
        Exception::assertTrue(\sort($array, $this->flags));
      }
      return $array;
    }
    public function sortValues($array) {
      if (\hacklib_cast_as_boolean($this->reverse)) {
        Exception::assertTrue(\arsort($array, $this->flags));
      } else {
        Exception::assertTrue(\asort($array, $this->flags));
      }
      return $array;
    }
    public function sortKeys($array) {
      if (\hacklib_cast_as_boolean($this->reverse)) {
        Exception::assertTrue(\krsort($array, $this->flags));
      } else {
        Exception::assertTrue(\ksort($array, $this->flags));
      }
      return $array;
    }
  }
  final class CallbackSorter extends Sorter {
    private $cmp;
    public function __construct($cmp) {
      $this->cmp = $cmp;
    }
    public function sort($array) {
      Exception::assertTrue(\usort($array, $this->cmp));
      return $array;
    }
    public function sortValues($array) {
      Exception::assertTrue(\uasort($array, $this->cmp));
      return $array;
    }
    public function sortKeys($array) {
      Exception::assertTrue(\uksort($array, $this->cmp));
      return $array;
    }
  }
  final class NumSorter extends _BuiltinSorter {
    public function __construct() {
      parent::__construct(\SORT_NUMERIC);
    }
  }
  final class StringSorter extends _BuiltinSorter {
    public function __construct() {
      parent::__construct(\SORT_STRING);
    }
    public function setNatural($nat = true) {
      $this->_set(\SORT_NATURAL & (~\SORT_STRING), $nat);
      return $this;
    }
    public function setCaseInsensitive($ci = true) {
      $this->_set(\SORT_FLAG_CASE, $ci);
      return $this;
    }
  }
  final class LocaleStringSorter extends _BuiltinSorter {
    public function __construct() {
      parent::__construct(\SORT_LOCALE_STRING);
    }
  }
  final class MixedSorter extends _BuiltinSorter {
    public function __construct() {
      parent::__construct(\SORT_REGULAR);
    }
  }
}
