<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  const NULL_STRING = null;
  const NULL_INT = null;
  const NULL_FLOAT = null;
  const NULL_RESOURCE = null;
  const NULL_BOOL = null;
  const NULL_MIXED = null;
  class TestNewNull extends Test {
    public function run() {
      self::assertEqual(new_null(), null);
    }
  }
  function new_null() {
    return null;
  }
  class TestNullThrows extends Test {
    public function run() {
      self::assertEqual(null_throws("foo"), "foo");
      self::assertException(
        function() {
          null_throws(null);
        },
        "Unexpected null"
      );
      self::assertException(
        function() {
          null_throws(null, "foo");
        },
        "foo"
      );
    }
  }
  function null_throws($value, $message = "Unexpected null") {
    return ($value === null) ? throw_(new \Exception($message)) : $value;
  }
  class TestThrow extends Test {
    public function run() {
      self::assertException(
        function() {
          $a = throw_(new \Exception("lol", 2));
          echo ($a);
        },
        "lol",
        2
      );
    }
  }
  function throw_($e) {
    throw $e;
  }
  class TestUnreachable extends Test {
    public function run() {
      self::assertException(
        function() {
          unreachable();
        },
        "This code should be unreachable"
      );
      self::assertException(
        function() {
          unreachable("foo");
        },
        "foo"
      );
    }
  }
  function unreachable($message = "This code should be unreachable") {
    throw new \Exception($message);
  }
  class TestIfNull extends Test {
    public function run() {
      self::assertEqual(if_null("a", "b"), "a");
      self::assertEqual(if_null("a", null), "a");
      self::assertEqual(if_null(null, "b"), "b");
      self::assertEqual(if_null(null, null), null);
    }
  }
  function if_null($x, $y) {
    return ($x === null) ? $y : $x;
  }
  class TestFst extends Test {
    public function run() {
      self::assertEqual(fst(array("a", "b")), "a");
    }
  }
  function fst($t) {
    return $t[0];
  }
  class TestSnd extends Test {
    public function run() {
      self::assertEqual(snd(array("a", "b")), "b");
    }
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
  class TestRef extends Test {
    public function run() {
      $ref = new Ref("x");
      self::assertEqual($ref->get(), "x");
      $ref->set("y");
      self::assertEqual($ref->get(), "y");
    }
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
  class TestIsAssoc extends SampleTest {
    public function evaluate($in) {
      return is_assoc($in);
    }
    public function getData() {
      return array(
        array(array(), false),
        array(array("a"), false),
        array(array("a", "b"), false),
        array(array(1 => "a", 0 => "b"), true),
        array(array("c" => "a", "d" => "b"), true)
      );
    }
  }
  function is_assoc($x) {
    $i = 0;
    foreach ($x as $k => $v) {
      if ($k !== ($i++)) {
        return true;
      }
    }
    return false;
  }
  class TestConcat extends Test {
    public function run() {
      self::assertEqual(concat(array(), array()), array());
      self::assertEqual(concat(array("a"), array()), array("a"));
      self::assertEqual(concat(array(), array("a")), array("a"));
      self::assertEqual(concat(array("a"), array("b")), array("a", "b"));
      self::assertEqual(
        concat(array("a", "c"), array("b")),
        array("a", "c", "b")
      );
      self::assertEqual(
        concat(array("a", "c"), array("b", "d")),
        array("a", "c", "b", "d")
      );
    }
  }
  function concat($a, $b) {
    return \array_merge($a, $b);
  }
  class TestConcatAll extends Test {
    public function run() {
      self::assertEqual(concat_all(array()), array());
      self::assertEqual(concat_all(array(array("a", "b"))), array("a", "b"));
      self::assertEqual(concat_all(array(array("a"), array())), array("a"));
      self::assertEqual(concat_all(array(array(), array("a"))), array("a"));
      self::assertEqual(
        concat_all(array(array("a"), array("b"))),
        array("a", "b")
      );
      self::assertEqual(
        concat_all(array(array("a"), array("b"), array("c"))),
        array("a", "b", "c")
      );
      self::assertEqual(
        concat_all(array(array("a", "d"), array("b", "e"), array("c", "f"))),
        array("a", "d", "b", "e", "c", "f")
      );
    }
  }
  function concat_all($vectors) {
    return
      \hacklib_cast_as_boolean($vectors)
        ? \call_user_func_array("array_merge", $vectors)
        : array();
  }
  class TestPush extends Test {
    public function run() {
      self::assertEqual(push(array(), "x"), array("x"));
      self::assertEqual(push(array("y"), "x"), array("y", "x"));
      self::assertEqual(push(array("y", "z"), "x"), array("y", "z", "x"));
    }
  }
  function push($v, $x) {
    \array_push($v, $x);
    return $v;
  }
  class TestPop extends Test {
    public function run() {
      self::assertEqual(pop(array(0)), array(array(), 0));
      self::assertEqual(pop(array("a", "b")), array(array("a"), "b"));
      self::assertException(
        function() {
          pop(array());
        },
        "Cannot pop last element: Array is empty"
      );
    }
  }
  function pop($v) {
    if (!\hacklib_cast_as_boolean($v)) {
      throw new Exception("Cannot pop last element: Array is empty");
    }
    $x = \array_pop($v);
    return array($v, $x);
  }
  class TestUnshift extends Test {
    public function run() {
      self::assertEqual(unshift("x", array()), array("x"));
      self::assertEqual(unshift("x", array("y")), array("x", "y"));
      self::assertEqual(unshift("x", array("y", "z")), array("x", "y", "z"));
    }
  }
  function unshift($x, $v) {
    \array_unshift($v, $x);
    return $v;
  }
  class TestShift extends Test {
    public function run() {
      self::assertEqual(shift(array(0)), array(0, array()));
      self::assertEqual(shift(array("a", "b")), array("a", array("b")));
      self::assertException(
        function() {
          shift(array());
        },
        "Cannot shift first element: Array is empty"
      );
    }
  }
  function shift($v) {
    if (!\hacklib_cast_as_boolean($v)) {
      throw new Exception("Cannot shift first element: Array is empty");
    }
    $x = \array_shift($v);
    return array($x, $v);
  }
  class TestRange extends Test {
    public function run() {
      self::assertEqual(range(0, 5), array(0, 1, 2, 3, 4, 5));
      self::assertEqual(range(-3, 3), array(-3, -2, -1, 0, 1, 2, 3));
      self::assertEqual(range(3, -3), array(3, 2, 1, 0, -1, -2, -3));
      self::assertEqual(range(3, -3, 2), array(3, 1, -1, -3));
      self::assertEqual(range(3, -3, -3), array(3, 0, -3));
      self::assertEqual(range(3, -3, 4), array(3, -1));
    }
  }
  function range($start, $end, $step = 1) {
    return \range($start, $end, $step);
  }
  class TestFilter extends Test {
    public function run() {
      self::assertEqual(
        filter(
          array(6, 7, 8, 9, 10, 11, 12),
          function($x) {
            return (bool) ($x & 1);
          }
        ),
        array(7, 9, 11)
      );
      self::assertEqual(
        filter(
          array(6, 7, 8, 9, 10, 11, 12),
          function($x) {
            return !((bool) ($x & 1));
          }
        ),
        array(6, 8, 10, 12)
      );
    }
  }
  function filter($array, $f) {
    $ret = filter_assoc($array, $f);
    return
      \hacklib_not_equals(count($ret), count($array)) ? values($ret) : $array;
  }
  class TestFilterAssoc extends Test {
    public function run() {
      self::assertEqual(
        filter_assoc(
          array("a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5),
          function($x) {
            return (bool) ($x & 1);
          }
        ),
        array("a" => 1, "c" => 3, "e" => 5)
      );
    }
  }
  function filter_assoc($array, $f) {
    return \array_filter($array, $f);
  }
  class TestMap extends Test {
    public function run() {
      self::assertEqual(
        map(
          array(1, 2, 3, 4, 5),
          function($x) {
            return $x * $x * $x;
          }
        ),
        array(1, 8, 27, 64, 125)
      );
      self::assertEqual(
        map(
          range(1, 5),
          function($x) {
            return $x * 2;
          }
        ),
        array(2, 4, 6, 8, 10)
      );
    }
  }
  function map($array, $f) {
    return \array_map($f, $array);
  }
  class TestMapAssoc extends Test {
    public function run() {
      self::assertEqual(
        map_assoc(
          array("stringkey" => "value"),
          function($x) {
            Test::assertEqual($x, "value");
            return "value2";
          }
        ),
        array("stringkey" => "value2")
      );
    }
  }
  function map_assoc($array, $f) {
    return \array_map($f, $array);
  }
  class TestMapKeys extends Test {
    public function run() {
      self::assertEqual(
        map_keys(
          array(9 => "a", -8 => "c"),
          function($x) {
            return $x * 2;
          }
        ),
        array(18 => "a", -16 => "c")
      );
    }
  }
  function map_keys($array, $f) {
    $ret = array();
    foreach ($array as $k => $v) {
      $ret[$f($k)] = $v;
    }
    return $ret;
  }
  class TestConcatMap extends Test {
    public function run() {
      self::assertEqual(
        concat_map(
          array(1, 5),
          function($x) {
            return array($x + 1, $x + 2);
          }
        ),
        array(2, 3, 6, 7)
      );
    }
  }
  function concat_map($array, $f) {
    $ret = array();
    foreach ($array as $x) {
      foreach ($f($x) as $x2) {
        $ret[] = $x2;
      }
    }
    return $ret;
  }
  class TestReduce extends Test {
    public function run() {
      $sum = function($x, $y) {
        return $x + $y;
      };
      $product = function($x, $y) {
        return $x * $y;
      };
      $push = function($x, $y) {
        return push($x, $y);
      };
      $unshift = function($x, $y) {
        return unshift($y, $x);
      };
      self::assertEqual(reduce(array(1, 2, 3, 4, 5), $sum, 0), 15);
      self::assertEqual(reduce(array(1, 2, 3, 4, 5), $sum, -5), 10);
      self::assertEqual(reduce(array(1, 2, 3, 4, 5), $product, 10), 1200);
      self::assertEqual(
        reduce(array(1, 2, 3, 4, 5), $push, array()),
        array(1, 2, 3, 4, 5)
      );
      self::assertEqual(
        reduce(array(1, 2, 3, 4, 5), $unshift, array()),
        array(5, 4, 3, 2, 1)
      );
    }
  }
  function reduce($array, $f, $initial) {
    return \array_reduce($array, $f, $initial);
  }
  class TestReduceRight extends Test {
    public function run() {
      $push = function($x, $y) {
        return push($x, $y);
      };
      $unshift = function($x, $y) {
        return unshift($y, $x);
      };
      self::assertEqual(
        reduce_right(array(1, 2, 3, 4, 5), $push, array()),
        array(5, 4, 3, 2, 1)
      );
      self::assertEqual(
        reduce_right(array(1, 2, 3, 4, 5), $unshift, array()),
        array(1, 2, 3, 4, 5)
      );
    }
  }
  function reduce_right($array, $f, $value) {
    $iter = new ArrayIterator($array);
    for (
      $iter->end();
      \hacklib_cast_as_boolean($iter->valid());
      $iter->prev()
    ) {
      $value = $f($value, $iter->current());
    }
    return $value;
  }
  class TestGroupBy extends Test {
    public function run() {
      self::assertEqual(
        group_by(
          array(
            "a" => 12,
            "asdf" => 4,
            "etr" => 3,
            "" => 24,
            "efw" => 23,
            "x" => 23,
            "23" => 3423,
            "sd" => 54,
            "ergerg" => 53,
            "+(" => 43445,
            "]123" => 45
          ),
          function($x) {
            return quot($x, 10);
          }
        ),
        array(
          1 => array(12),
          0 => array(4, 3),
          2 => array(24, 23, 23),
          342 => array(3423),
          5 => array(54, 53),
          4344 => array(43445),
          4 => array(45)
        )
      );
    }
  }
  function group_by($a, $f) {
    $res = array();
    foreach ($a as $v) {
      $res[$f($v)][] = $v;
    }
    return $res;
  }
  class TestAnyAll extends Test {
    public function run() {
      $count = new Ref(0);
      $list = array(9, 4, 1, 3, 4, 345, 2342, 3434, 34);
      $moreThan100 = function($x) use ($count) {
        $count->set($count->get() + 1);
        return $x > 100;
      };
      $lessThan0 = function($x) use ($count) {
        $count->set($count->get() + 1);
        return $x < 0;
      };
      $moreThan0 = function($x) use ($count) {
        $count->set($count->get() + 1);
        return $x > 0;
      };
      $count->set(0);
      self::assertEqual(any($list, $moreThan100), true);
      self::assertEqual($count->get(), 6);
      $count->set(0);
      self::assertEqual(all($list, $moreThan100), false);
      self::assertEqual($count->get(), 1);
      $count->set(0);
      self::assertEqual(any($list, $lessThan0), false);
      self::assertEqual($count->get(), 9);
      $count->set(0);
      self::assertEqual(all($list, $lessThan0), false);
      self::assertEqual($count->get(), 1);
      $count->set(0);
      self::assertEqual(any($list, $moreThan0), true);
      self::assertEqual($count->get(), 1);
      $count->set(0);
      self::assertEqual(all($list, $moreThan0), true);
      self::assertEqual($count->get(), 9);
    }
  }
  function any($a, $f) {
    foreach ($a as $x) {
      if (\hacklib_cast_as_boolean($f($x))) {
        return true;
      }
    }
    return false;
  }
  function all($a, $f) {
    foreach ($a as $x) {
      if (!\hacklib_cast_as_boolean($f($x))) {
        return false;
      }
    }
    return true;
  }
  class TestKeysToLower extends Test {
    public function run() {
      self::assertEqual(
        keys_to_lower(
          array(
            "fer" => 4,
            "SADFf" => 9,
            ":}{ADSjj}" => 6,
            "foo BAR baz" => 97674,
            "KEK" => 1
          )
        ),
        array(
          "fer" => 4,
          "sadff" => 9,
          ":}{adsjj}" => 6,
          "foo bar baz" => 97674,
          "kek" => 1
        )
      );
    }
  }
  function keys_to_lower($array) {
    return \array_change_key_case($array, \CASE_LOWER);
  }
  class TestKeysToUpper extends Test {
    public function run() {
      self::assertEqual(
        keys_to_uppper(
          array(
            "fer" => 4,
            "SADFf" => 9,
            ":}{ADSjj}" => 6,
            "foo BAR baz" => 97674,
            "KEK" => 1
          )
        ),
        array(
          "FER" => 4,
          "SADFF" => 9,
          ":}{ADSJJ}" => 6,
          "FOO BAR BAZ" => 97674,
          "KEK" => 1
        )
      );
    }
  }
  function keys_to_uppper($array) {
    return \array_change_key_case($array, \CASE_UPPER);
  }
  class TestToPairs extends Test {
    public function run() {
      self::assertEqual(
        to_pairs(
          array(
            "fer" => 4,
            "SADFf" => 9,
            ":}{ADSjj}" => 6,
            "foo BAR baz" => 97674,
            "KEK" => 1
          )
        ),
        array(
          array("fer", 4),
          array("SADFf", 9),
          array(":}{ADSjj}", 6),
          array("foo BAR baz", 97674),
          array("KEK", 1)
        )
      );
    }
  }
  function to_pairs($array) {
    $r = array();
    foreach ($array as $k => $v) {
      $r[] = array($k, $v);
    }
    return $r;
  }
  class TestFromPairs extends Test {
    public function run() {
      self::assertEqual(
        from_pairs(
          array(
            array("fer", 4),
            array("SADFf", 9),
            array(":}{ADSjj}", 6),
            array("foo BAR baz", 97674),
            array("KEK", 1),
            array("fer", 9)
          )
        ),
        array(
          "fer" => 9,
          "SADFf" => 9,
          ":}{ADSjj}" => 6,
          "foo BAR baz" => 97674,
          "KEK" => 1
        )
      );
    }
  }
  function from_pairs($pairs) {
    $r = array();
    foreach ($pairs as $p) {
      $r[$p[0]] = $p[1];
    }
    return $r;
  }
  class TestGet extends Test {
    public function run() {
      self::assertEqual(get(array("a" => 5), "a"), 5);
      self::assertEqual(get(array("c" => 1, "a" => 5), "a"), 5);
      self::assertEqual(get(array("c" => 1, "a" => null), "a"), null);
      $this->testErrors();
    }
    private function testErrors() {
      $errors = CaptureErrors::start();
      self::assertException(
        function() {
          get(array(), "key");
        },
        "Key 'key' does not exist in array"
      );
      $errors->finish();
      $errors = $errors->getErrors();
      self::assertEqual(\count($errors), 1);
      self::assertEqual($errors[0]->getMessage(), "Undefined index: key");
      self::assertEqual($errors[0]->getCode(), 0);
      self::assertEqual($errors[0]->getSeverity(), \E_NOTICE);
    }
  }
  function get($array, $key) {
    $res = $array[$key];
    if (($res === null) &&
        (!\hacklib_cast_as_boolean(key_exists($array, $key)))) {
      throw new Exception("Key '".$key."' does not exist in array");
    }
    return $res;
  }
  class TestGetPair extends Test {
    public function run() {
      $array = array("a" => "b", "c" => "d", "e" => "f");
      self::assertEqual(get_pair($array, 0), array("a", "b"));
      self::assertEqual(get_pair($array, 1), array("c", "d"));
      self::assertEqual(get_pair($array, 2), array("e", "f"));
      self::assertEqual(get_pair($array, -1), array("e", "f"));
      self::assertEqual(get_pair($array, -2), array("c", "d"));
      self::assertEqual(get_pair($array, -3), array("a", "b"));
      self::assertException(
        function() use ($array) {
          get_pair($array, 3);
        },
        "Offset 3 is out of bounds in array of size 3"
      );
      self::assertException(
        function() use ($array) {
          get_pair($array, -4);
        },
        "Offset -4 is out of bounds in array of size 3"
      );
    }
  }
  function get_pair($array, $offset) {
    $count = size($array);
    if (($offset < $count) && ($offset >= (-$count))) {
      foreach (slice_assoc($array, $offset, 1) as $k => $v) {
        return array($k, $v);
      }
    }
    throw new Exception(
      "Offset ".$offset." is out of bounds in array of size ".$count
    );
  }
  class TestSet extends Test {
    public function run() {
      $array = array("a" => "b", "c" => "d");
      self::assertEqual(set($array, "a", 9), array("a" => 9, "c" => "d"));
      self::assertEqual(
        set($array, "e", 9),
        array("a" => "b", "c" => "d", "e" => 9)
      );
    }
  }
  function set($array, $key, $val) {
    $array[$key] = $val;
    return $array;
  }
  class TestGetOrNull extends Test {
    public function run() {
      $array = array("a" => "b");
      self::assertEqual(get_or_null($array, "a"), "b");
      self::assertEqual(get_or_null($array, "c"), null);
    }
  }
  function get_or_null($array, $key) {
    return _idx_isset($array, $key, null);
  }
  class TestGetOrDefault extends Test {
    public function run() {
      $array = array("a" => "b", "c" => null);
      self::assertEqual(get_or_default($array, "a", 1), "b");
      self::assertEqual(get_or_default($array, "c", 1), null);
      self::assertEqual(get_or_default($array, "e", 1), 1);
    }
  }
  function get_or_default($array, $key, $default) {
    return _idx($array, $key, $default);
  }
  class TestGetIssetDefault extends Test {
    public function run() {
      $array = array("a" => "b", "c" => null);
      self::assertEqual(get_isset_default($array, "a", 1), "b");
      self::assertEqual(get_isset_default($array, "c", 1), 1);
      self::assertEqual(get_isset_default($array, "e", 1), 1);
    }
  }
  function get_isset_default($array, $key, $default) {
    return _idx_isset($array, $key, $default);
  }
  class TestKeyExists extends Test {
    public function run() {
      $array = array("a" => "b", "c" => null);
      self::assertEqual(key_exists($array, "a"), true);
      self::assertEqual(key_exists($array, "c"), true);
      self::assertEqual(key_exists($array, "e"), false);
    }
  }
  function key_exists($array, $key) {
    return \array_key_exists($key, $array);
  }
  class TestKeyIsset extends Test {
    public function run() {
      $array = array("a" => "b", "c" => null);
      self::assertEqual(key_isset($array, "a"), true);
      self::assertEqual(key_isset($array, "c"), false);
      self::assertEqual(key_isset($array, "e"), false);
    }
  }
  function key_isset($array, $key) {
    return get_or_null($array, $key) !== null;
  }
  class TestGetOffset extends Test {
    public function run() {
      $array = array("a", "b", "c", null);
      self::assertEqual(get_offset($array, 0), "a");
      self::assertEqual(get_offset($array, 1), "b");
      self::assertEqual(get_offset($array, 2), "c");
      self::assertEqual(get_offset($array, 3), null);
      self::assertEqual(get_offset($array, -4), "a");
      self::assertEqual(get_offset($array, -3), "b");
      self::assertEqual(get_offset($array, -2), "c");
      self::assertEqual(get_offset($array, -1), null);
      self::assertException(
        function() use ($array) {
          get_offset($array, 8);
        },
        "Index 8 out of bounds in array of length 4"
      );
    }
  }
  function get_offset($v, $i) {
    $l = \count($v);
    if ($i < 0) {
      $i += $l;
    }
    if (($i < 0) || ($i >= $l)) {
      throw new Exception(
        "Index ".$i." out of bounds in array of length ".$l
      );
    }
    return $v[$i];
  }
  class TestSetOffset extends Test {
    public function run() {
      $array = array("a", "c", null);
      self::assertEqual(
        set_offset($array, 0, "kek"),
        array("kek", "c", null)
      );
      self::assertEqual(
        set_offset($array, 1, "kek"),
        array("a", "kek", null)
      );
      self::assertEqual(set_offset($array, 2, "kek"), array("a", "c", "kek"));
      self::assertEqual(
        set_offset($array, -3, "kek"),
        array("kek", "c", null)
      );
      self::assertEqual(
        set_offset($array, -2, "kek"),
        array("a", "kek", null)
      );
      self::assertEqual(
        set_offset($array, -1, "kek"),
        array("a", "c", "kek")
      );
      self::assertException(
        function() use ($array) {
          set_offset($array, 8, "kek");
        },
        "Index 8 out of bounds in array of length 3"
      );
    }
  }
  function set_offset($v, $i, $x) {
    $l = \count($v);
    if ($i < 0) {
      $i += $l;
    }
    if (($i < 0) || ($i >= $l)) {
      throw new Exception(
        "Index ".$i." out of bounds in array of length ".$l
      );
    }
    $v[$i] = $x;
    return $v;
  }
  function column($arrays, $key) {
    return \array_column($arrays, $key);
  }
  function combine($keys, $values) {
    return \array_combine($keys, $values);
  }
  function separate($array) {
    $ks = array();
    $vs = array();
    foreach ($array as $k => $v) {
      $ks[] = $k;
      $vs[] = $v;
    }
    return array($ks, $vs);
  }
  function from_keys($keys, $value) {
    return \array_fill_keys($keys, $value);
  }
  function unique($values) {
    return values(combine($values, $values));
  }
  function flip($array) {
    return \array_flip($array);
  }
  function flip_count($values) {
    return \array_count_values($values);
  }
  function flip_all($array) {
    $ret = array();
    foreach ($array as $k => $v) {
      $ret[$v][] = $k;
    }
    return $ret;
  }
  function keys($array) {
    return \array_keys($array);
  }
  function keys_strings($array) {
    return map(
      keys($array),
      function($k) {
        return (string) $k;
      }
    );
  }
  function values($array) {
    return \array_values($array);
  }
  function union_keys($a, $b) {
    return \array_replace($a, $b);
  }
  function union_keys_all($arrays) {
    return
      \hacklib_cast_as_boolean($arrays)
        ? \call_user_func_array("array_replace", $arrays)
        : array();
  }
  function intersect($a, $b) {
    return \array_intersect($a, $b);
  }
  function intersect_assoc($a, $b) {
    return \array_intersect_assoc($a, $b);
  }
  function intersect_keys($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function diff($a, $b) {
    return \array_diff($a, $b);
  }
  function diff_assoc($a, $b) {
    return \array_diff_assoc($a, $b);
  }
  function diff_keys($a, $b) {
    return \array_intersect_key($a, $b);
  }
  function select($array, $keys) {
    return map(
      $keys,
      function($key) use ($array) {
        return $array[$key];
      }
    );
  }
  function select_or_null($array, $keys) {
    return map(
      $keys,
      function($key) use ($array) {
        return get_or_null($array, $key);
      }
    );
  }
  function zip($a, $b) {
    $r = array();
    $l = min(count($a), count($b));
    for ($i = 0; $i < $l; $i++) {
      $r[] = array($a[$i], $b[$i]);
    }
    return $r;
  }
  function zip_assoc($a, $b) {
    $ret = array();
    foreach ($a as $k => $v) {
      if (\hacklib_cast_as_boolean(key_exists($b, $k))) {
        $ret[$k] = array($v, $b[$k]);
      }
    }
    return $ret;
  }
  function unzip($x) {
    $a = array();
    $b = array();
    foreach ($x as $p) {
      $a[] = $p[0];
      $b[] = $p[1];
    }
    return array($a, $b);
  }
  function unzip_assoc($array) {
    $a = array();
    $b = array();
    foreach ($array as $k => $v) {
      $a[$k] = $v[0];
      $b[$k] = $v[1];
    }
    return array($a, $b);
  }
  function new_array() {
    return array();
  }
  function new_assoc() {
    return array();
  }
  function transpose($arrays) {
    $ret = new_array();
    foreach ($arrays as $array) {
      $i = 0;
      foreach ($array as $v) {
        $ret[$i++][] = $v;
      }
    }
    return $ret;
  }
  function transpose_assoc($arrays) {
    $ret = array();
    foreach ($arrays as $k1 => $array) {
      foreach ($array as $k2 => $v) {
        $ret[$k2][$k1] = $v;
      }
    }
    return $ret;
  }
  function transpose_num_assoc($arrays) {
    $ret = array();
    foreach ($arrays as $array) {
      foreach ($array as $k => $v) {
        $ret[$k][] = $v;
      }
    }
    return $ret;
  }
  function transpose_assoc_num($arrays) {
    $ret = new_array();
    foreach ($arrays as $k => $array) {
      $i = 0;
      foreach ($array as $v) {
        $ret[$i++][$k] = $v;
      }
    }
    return $ret;
  }
  function shuffle($array) {
    \shuffle($array);
    return $array;
  }
  class TestStringShuffle extends Test {
    public function run() {
      self::assertEqual(length(shuffle_string("abc")), 3);
    }
  }
  function shuffle_string($string) {
    return \str_shuffle($string);
  }
  class TestReverseString extends Test {
    public function run() {
      self::assertEqual(reverse_string("abc"), "cba");
      self::assertEqual(reverse_string(""), "");
    }
  }
  function reverse($array) {
    return \array_reverse($array, false);
  }
  function reverse_assoc($array) {
    return \array_reverse($array, true);
  }
  function reverse_string($string) {
    return \strrev($string);
  }
  function chunk($array, $size) {
    if ($size < 1) {
      throw new Exception("Chunk size must be >= 1");
    }
    return \array_chunk($array, $size, false);
  }
  function chunk_assoc($array, $size) {
    if ($size < 1) {
      throw new Exception("Chunk size must be >= 1");
    }
    return \array_chunk($array, $size, true);
  }
  class TestStringChunk extends Test {
    public function run() {
      self::assertEqual(chunk_string("abc", 1), array("a", "b", "c"));
      self::assertEqual(chunk_string("abc", 2), array("ab", "c"));
      self::assertEqual(chunk_string("abc", 3), array("abc"));
    }
  }
  function chunk_string($string, $size) {
    if ($size < 1) {
      throw new \Exception("Chunk size must be >= 1");
    }
    return Exception::assertArray(\str_split($string, $size));
  }
  class TestStringRepeat extends Test {
    public function run() {
      self::assertEqual(repeat_string("123", 3), "123123123");
    }
  }
  function repeat($value, $count) {
    if (!\hacklib_cast_as_boolean($count)) {
      return array();
    }
    return \array_fill(0, $count, $value);
  }
  function repeat_string($string, $count) {
    return \str_repeat($string, $count);
  }
  class TestStringSlice extends Test {
    public function run() {
      self::assertEqual(slice("abc", 1, 1), "b");
      self::assertEqual(slice("abc", -1, 1), "c");
      self::assertEqual(slice("abc", 1, -1), "b");
      self::assertEqual(slice("abc", 1), "bc");
      self::assertEqual(slice("abc", -1), "c");
    }
  }
  function slice($string, $offset, $length = NULL_INT) {
    $ret = \substr($string, $offset, if_null($length, 0x7FFFFFFF));
    return ($ret === false) ? "" : $ret;
  }
  function slice_array($array, $offset, $length = NULL_INT) {
    return \array_slice($array, $offset, $length);
  }
  function slice_assoc($array, $offset, $length = NULL_INT) {
    return \array_slice($array, $offset, $length, true);
  }
  class TestStringSplice extends Test {
    public function run() {
      self::assertEqual(splice("abc", 1, 1), "ac");
      self::assertEqual(splice("abc", 1, 1, "lol"), "alolc");
    }
  }
  function splice($string, $offset, $length = NULL_INT, $replacement = "") {
    return \substr_replace(
      $string,
      $replacement,
      $offset,
      if_null($length, 0x7FFFFFFF)
    );
  }
  function splice_array(
    $array,
    $offset,
    $length = NULL_INT,
    $replacement = array()
  ) {
    $removed = \array_splice($array, $offset, $length, $replacement);
    return array($array, $removed);
  }
  class TestStringSearch extends Test {
    public function run() {
      self::assertEqual(find("a", "a"), 0);
      self::assertEqual(find("a", "a", 1), NULL_INT);
      self::assertEqual(find("a", "a", -1), 0);
      self::assertEqual(find("abc", "a"), 0);
      self::assertEqual(find("abc", "b"), 1);
      self::assertEqual(find("abc", "c"), 2);
      self::assertEqual(find("abc", "a", -2), NULL_INT);
      self::assertEqual(find("abc", "b", -2), 1);
      self::assertEqual(find("abc", "c", -2), 2);
      self::assertEqual(find("abbb", "bb"), 1);
      self::assertEqual(find("abbb", "bb", 2), 2);
      self::assertEqual(find_last("a", "a"), 0);
      self::assertEqual(find_last("a", "a", 1), NULL_INT);
      self::assertEqual(find_last("a", "a", -1), 0);
      self::assertEqual(find_last("aba", "a"), 2);
      self::assertEqual(find_last("aba", "b"), 1);
      self::assertEqual(find_last("aba", "c"), NULL_INT);
      self::assertEqual(find_last("aba", "a", -2), 0);
      self::assertEqual(find_last("aba", "b", -2), 1);
      self::assertEqual(find_last("aba", "c", -2), NULL_INT);
      self::assertEqual(find_last("abbb", "bb"), 2);
      self::assertEqual(find_last("abbb", "bb", 2), 2);
    }
  }
  function find($haystack, $needle, $offset = 0, $caseInsensitive = false) {
    if ((\PHP_VERSION_ID < 70100) && ($offset < 0)) {
      $offset += length($haystack);
    }
    $ret =
      \hacklib_cast_as_boolean($caseInsensitive)
        ? \stripos($haystack, $needle, $offset)
        : \strpos($haystack, $needle, $offset);
    return ($ret === false) ? null : $ret;
  }
  function find_last(
    $haystack,
    $needle,
    $offset = 0,
    $caseInsensitive = false
  ) {
    $ret =
      \hacklib_cast_as_boolean($caseInsensitive)
        ? \strripos($haystack, $needle, $offset)
        : \strrpos($haystack, $needle, $offset);
    return ($ret === false) ? null : $ret;
  }
  function find_count($haystack, $needle, $offset = 0) {
    if ((\PHP_VERSION_ID < 70100) && ($offset < 0)) {
      $offset += length($haystack);
    }
    return \substr_count($haystack, $needle, $offset);
  }
  function contains($haystack, $needle, $offset = 0) {
    return find($haystack, $needle, $offset) !== null;
  }
  function length($string) {
    return \strlen($string);
  }
  function count($array) {
    return \count($array);
  }
  function size($array) {
    return \count($array);
  }
  function find_key($array, $value) {
    $ret = \array_search($value, $array, true);
    return ($ret === false) ? new_null() : $ret;
  }
  function find_keys($array, $value) {
    return \array_keys($array, $value, true);
  }
  function find_last_key($array, $value) {
    $iter = new ArrayIterator($array);
    for (
      $iter->end();
      \hacklib_cast_as_boolean($iter->valid());
      $iter->prev()
    ) {
      if ($iter->current() === $value) {
        return $iter->key();
      }
    }
    return null;
  }
  function in($value, $array) {
    return \in_array($value, $array, true);
  }
  class TestToHex extends Test {
    public function run() {
      self::assertEqual(to_hex("\000\377 "), "00ff20");
    }
  }
  function to_hex($string) {
    return \bin2hex($string);
  }
  class TestFromHex extends Test {
    public function run() {
      self::assertEqual(from_hex("00ff20"), "\000\377 ");
      self::assertEqual(from_hex("00Ff20"), "\000\377 ");
    }
  }
  function from_hex($string) {
    return Exception::assertString(\hex2bin($string));
  }
  class TestToLower extends Test {
    public function run() {
      self::assertEqual(to_lower("ABC.1.2.3"), "abc.1.2.3");
    }
  }
  function to_lower($string) {
    return \strtolower($string);
  }
  class TestToUpper extends Test {
    public function run() {
      self::assertEqual(to_upper("abc.1.2.3"), "ABC.1.2.3");
    }
  }
  function to_upper($string) {
    return \strtoupper($string);
  }
  const SPACE_CHARS = " \t\r\n\013\014";
  const TRIM_CHARS = " \t\r\n\013\000";
  function trim($string, $chars = TRIM_CHARS) {
    return \trim($string, $chars);
  }
  function trim_left($string, $chars = TRIM_CHARS) {
    return \ltrim($string, $chars);
  }
  function trim_right($string, $chars = TRIM_CHARS) {
    return \rtrim($string, $chars);
  }
  function decode_utf8($s) {
    return \utf8_decode($s);
  }
  function encode_utf8($s) {
    return \utf8_encode($s);
  }
  function is_utf8($s) {
    return (bool) \hacklib_cast_as_boolean(\preg_match("//u", $s));
  }
  function add_slashes($s) {
    return \addslashes($s);
  }
  function strip_slashes($s) {
    return \stripslashes($s);
  }
  class TestStringSplit extends Test {
    public function run() {
      self::assertEqual(split(""), array());
      self::assertEqual(split("a"), array("a"));
      self::assertEqual(split("abc"), array("a", "b", "c"));
      self::assertEqual(split("", "", 1), array());
      self::assertEqual(split("a", "", 1), array("a"));
      self::assertEqual(split("abc", "", 1), array("abc"));
      self::assertEqual(split("abc", "", 2), array("a", "bc"));
      self::assertEqual(split("abc", "", 3), array("a", "b", "c"));
      self::assertEqual(split("", "b"), array(""));
      self::assertEqual(split("abc", "b"), array("a", "c"));
      self::assertEqual(split("abc", "b", 1), array("abc"));
      self::assertEqual(split("abc", "b", 2), array("a", "c"));
    }
  }
  function split($string, $delimiter = "", $limit = NULL_INT) {
    $limit = if_null($limit, 0x7FFFFFFF);
    if ($limit < 1) {
      throw new Exception("Limit must be >= 1, ".$limit." given");
    }
    if ($delimiter === "") {
      $length = length($string);
      if (\hacklib_equals($length, 0)) {
        return array();
      }
      if (\hacklib_equals($limit, 1)) {
        return array($string);
      }
      if ($length > $limit) {
        return push(
          \str_split(slice($string, 0, $limit - 1)),
          slice($string, $limit - 1)
        );
      }
      return \str_split($string);
    }
    return \explode($delimiter, $string, $limit);
  }
  function split_lines($string) {
    $lines = split($string, "\n");
    foreach ($lines as $i => $line) {
      if (slice($line, -1) === "\r") {
        $lines[$i] = slice($line, 0, -1);
      }
    }
    if (\hacklib_cast_as_boolean($lines) && (get_offset($lines, -1) === "")) {
      $lines = slice_array($lines, 0, -1);
    }
    return $lines;
  }
  class TestStringSplitAt extends Test {
    public function run() {
      self::assertEqual(split_at("abc", -4), array("", "abc"));
      self::assertEqual(split_at("abc", -3), array("", "abc"));
      self::assertEqual(split_at("abc", -2), array("a", "bc"));
      self::assertEqual(split_at("abc", -1), array("ab", "c"));
      self::assertEqual(split_at("abc", 0), array("", "abc"));
      self::assertEqual(split_at("abc", 1), array("a", "bc"));
      self::assertEqual(split_at("abc", 2), array("ab", "c"));
      self::assertEqual(split_at("abc", 3), array("abc", ""));
      self::assertEqual(split_at("abc", 4), array("abc", ""));
    }
  }
  function split_at($string, $offset) {
    return array(slice($string, 0, $offset), slice($string, $offset));
  }
  function split_array_at($array, $offset) {
    return array(
      slice_array($array, 0, $offset),
      slice_array($array, $offset)
    );
  }
  class TestStringJoin extends Test {
    public function run() {
      self::assertEqual(join(array()), "");
      self::assertEqual(join(array("abc")), "abc");
      self::assertEqual(join(array("a", "bc")), "abc");
      self::assertEqual(join(array(), ","), "");
      self::assertEqual(join(array("abc"), ","), "abc");
      self::assertEqual(join(array("a", "bc"), ","), "a,bc");
    }
  }
  function join($strings, $delimiter = "") {
    return \implode($delimiter, $strings);
  }
  function join_lines($lines, $nl = "\n") {
    return \hacklib_cast_as_boolean($lines) ? (join($lines, $nl).$nl) : "";
  }
  class TestStringReplace extends Test {
    public function run() {
      self::assertEqual(replace_count("abc", "b", "lol"), array("alolc", 1));
      self::assertEqual(replace_count("abc", "B", "lol"), array("abc", 0));
      self::assertEqual(
        replace_count("abc", "B", "lol", true),
        array("alolc", 1)
      );
    }
  }
  function replace($subject, $search, $replace, $caseInsensitive = false) {
    return Exception::assertString(
      \hacklib_cast_as_boolean($caseInsensitive)
        ? \str_ireplace($search, $replace, $subject)
        : \str_replace($search, $replace, $subject)
    );
  }
  function replace_count(
    $subject,
    $search,
    $replace,
    $caseInsensitive = false
  ) {
    $count = 0;
    $result = Exception::assertString(
      \hacklib_cast_as_boolean($caseInsensitive)
        ? \str_ireplace($search, $replace, $subject, $count)
        : \str_replace($search, $replace, $subject, $count)
    );
    return array($result, $count);
  }
  class TestStringPad extends Test {
    public function run() {
      self::assertEqual(pad("abc", 3), "abc");
      self::assertEqual(pad("abc", 4), "abc ");
      self::assertEqual(pad("abc", 5), " abc ");
      self::assertEqual(pad("abc", 6), " abc  ");
      self::assertEqual(pad("1", 3, "ab"), "a1a");
      self::assertEqual(pad("1", 4, "ab"), "a1ab");
      self::assertEqual(pad_left("abc", 3), "abc");
      self::assertEqual(pad_left("abc", 4), " abc");
      self::assertEqual(pad_left("abc", 5), "  abc");
      self::assertEqual(pad_left("abc", 6), "   abc");
      self::assertEqual(pad_left("1", 3, "ab"), "ab1");
      self::assertEqual(pad_left("1", 4, "ab"), "aba1");
      self::assertEqual(pad_right("abc", 3), "abc");
      self::assertEqual(pad_right("abc", 4), "abc ");
      self::assertEqual(pad_right("abc", 5), "abc  ");
      self::assertEqual(pad_right("abc", 6), "abc   ");
      self::assertEqual(pad_right("1", 3, "ab"), "1ab");
      self::assertEqual(pad_right("1", 4, "ab"), "1aba");
    }
  }
  function pad($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_BOTH);
  }
  function pad_array($array, $size, $value) {
    return \array_pad($array, $size, $value);
  }
  function pad_left($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_LEFT);
  }
  function pad_right($string, $length, $pad = " ") {
    return \str_pad($string, $length, $pad, \STR_PAD_RIGHT);
  }
  class TestStringSetLength extends Test {
    public function run() {
      self::assertEqual(set_length("ab", -3), "");
      self::assertEqual(set_length("ab", -2), "");
      self::assertEqual(set_length("ab", -1), "a");
      self::assertEqual(set_length("ab", 0), "");
      self::assertEqual(set_length("ab", 1), "a");
      self::assertEqual(set_length("ab", 2), "ab");
      self::assertEqual(set_length("ab", 3), "ab ");
      self::assertEqual(set_length("ab", 4), "ab  ");
      self::assertEqual(set_length("ab", 3, "12"), "ab1");
      self::assertEqual(set_length("ab", 4, "12"), "ab12");
      self::assertEqual(set_length("ab", 5, "12"), "ab121");
      self::assertEqual(set_length("ab", 6, "12"), "ab1212");
    }
  }
  function set_length($string, $length, $pad = " ") {
    $string = slice($string, 0, $length);
    $string = pad_right($string, $length, $pad);
    return $string;
  }
  class TestStringCharCode extends Test {
    public function run() {
      self::assertEqual(from_char_code(128), "\200");
      self::assertEqual(from_char_code(0), "\000");
      self::assertEqual(from_char_code(255), "\377");
      self::assertEqual(char_code_at("a"), 97);
      self::assertEqual(char_code_at("a99"), 97);
    }
  }
  function from_char_code($ascii) {
    if (($ascii < 0) || ($ascii >= 256)) {
      throw new Exception(
        "ASCII character code must be >= 0 and < 256: ".$ascii
      );
    }
    return \chr($ascii);
  }
  function char_at($s, $i = 0) {
    $l = \strlen($s);
    if ($i < 0) {
      $i += $l;
    }
    if (($i < 0) || ($i >= $l)) {
      throw new Exception(
        "String offset ".$i." out of bounds in string of length ".$l
      );
    }
    return $s[$i];
  }
  function char_code_at($string, $offset = 0) {
    return \ord(char_at($string, $offset));
  }
  class TestStringCompare extends Test {
    public function run() {
      self::assertEqual(str_cmp("a", "a"), 0);
      self::assertEqual(str_cmp("a", "A"), 1);
      self::assertEqual(str_cmp("", ""), 0);
      self::assertEqual(str_cmp("", "a"), -1);
      self::assertEqual(str_cmp("a", ""), 1);
      self::assertEqual(str_cmp("a", "a", true), 0);
      self::assertEqual(str_cmp("a", "A", true), 0);
      self::assertEqual(str_cmp("", "", true), 0);
      self::assertEqual(str_cmp("", "a", true), -1);
      self::assertEqual(str_cmp("a", "", true), 1);
      self::assertEqual(str_eq("a", "a"), true);
      self::assertEqual(str_eq("a", "A"), false);
      self::assertEqual(str_eq("", ""), true);
      self::assertEqual(str_eq("", "a"), false);
      self::assertEqual(str_eq("a", ""), false);
      self::assertEqual(str_eq("a", "a", true), true);
      self::assertEqual(str_eq("a", "A", true), true);
      self::assertEqual(str_eq("", "", true), true);
      self::assertEqual(str_eq("", "a", true), false);
      self::assertEqual(str_eq("a", "", true), false);
    }
  }
  function str_cmp($a, $b, $caseInsensitive = false, $natural = false) {
    $ret =
      \hacklib_cast_as_boolean($caseInsensitive)
        ? (\hacklib_cast_as_boolean($natural)
             ? \strnatcasecmp($a, $b)
             : \strcasecmp($a, $b))
        : (\hacklib_cast_as_boolean($natural)
             ? \strnatcmp($a, $b)
             : \strcmp($a, $b));
    return sign($ret);
  }
  function str_eq($a, $b, $caseInsensitive = false, $natural = false) {
    return \hacklib_equals(str_cmp($a, $b, $caseInsensitive, $natural), 0);
  }
  class TestStringStartsWith extends Test {
    public function run() {
      self::assertEqual(starts_with("abbb", "ab"), true);
      self::assertEqual(starts_with("abbb", "bb"), false);
      self::assertEqual(starts_with("abbb", ""), true);
      self::assertEqual(starts_with("", ""), true);
      self::assertEqual(starts_with("", "a"), false);
    }
  }
  function starts_with($string, $prefix) {
    return slice($string, 0, length($prefix)) === $prefix;
  }
  class TestStringEndsWith extends Test {
    public function run() {
      self::assertEqual(ends_with("abbb", "bb"), true);
      self::assertEqual(ends_with("abbb", "ba"), false);
      self::assertEqual(ends_with("abbb", ""), true);
      self::assertEqual(ends_with("", ""), true);
      self::assertEqual(ends_with("", "a"), false);
    }
  }
  function ends_with($string, $suffix) {
    $length = length($suffix);
    return
      \hacklib_cast_as_boolean($length)
        ? (slice($string, -$length) === $suffix)
        : true;
  }
}
