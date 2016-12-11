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
      if ($val) {
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
      if ($this->reverse) {
        _check_return(\rsort($array, $this->flags), "rsort");
      } else {
        _check_return(\sort($array, $this->flags), "sort");
      }
      return $array;
    }
    public function sortValues($array) {
      if ($this->reverse) {
        _check_return(\arsort($array, $this->flags), "arsort");
      } else {
        _check_return(\asort($array, $this->flags), "asort");
      }
      return $array;
    }
    public function sortKeys($array) {
      if ($this->reverse) {
        _check_return(\krsort($array, $this->flags), "krsort");
      } else {
        _check_return(\ksort($array, $this->flags), "ksort");
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
      _check_return(\usort($array, $this->cmp), "usort");
      return $array;
    }
    public function sortValues($array) {
      _check_return(\uasort($array, $this->cmp), "uasort");
      return $array;
    }
    public function sortKeys($array) {
      _check_return(\uksort($array, $this->cmp), "uksort");
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
  function _check_return($ret, $func) {
    if ($ret === false) {
      throw new \Exception($func."() failed");
    }
  }
}
