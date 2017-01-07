<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class _Tests {
    public static function Testgets() {
      return array(
        TestTests::class_(),
        TestToHex::class_(),
        TestFromHex::class_(),
        TestStringShuffle::class_(),
        TestReverseString::class_(),
        TestToLower::class_(),
        TestToUpper::class_(),
        TestStringSplit::class_(),
        TestStringChunk::class_(),
        TestStringJoin::class_(),
        TestStringReplace::class_(),
        TestStringSplice::class_(),
        TestStringSlice::class_(),
        TestStringPad::class_(),
        TestStringRepeat::class_(),
        TestStringCharCode::class_(),
        TestStringCompare::class_(),
        TestStringSearch::class_(),
        TestStringEndsWith::class_(),
        TestStringStartsWith::class_(),
        TestFloatRounding::class_(),
        TestStringSetLength::class_(),
        TestStringSplitAt::class_(),
        TestLeapYear::class_(),
        TestDaysInMonth::class_(),
        TestOverflowDate::class_(),
        TestValidDate::class_(),
        TestQuotRemDivMod::class_(),
        TestConcatMap::class_(),
        TestFrac::class_(),
        TestTypeof::class_(),
        TestFileSystem::class_(),
        TestArrayIterator::class_(),
        TestDateTime::class_()
      );
    }
    public static function main() {
      foreach (self::Testgets() as $test) {
        echo ("  ".$test::name()."\n");
        $test::runStatic();
      }
      echo ("done\n");
    }
  }
  abstract class Test {
    public final static function runStatic() {
      $self = new static();
      $self->run();
    }
    public final static function class_() {
      return \get_called_class();
    }
    public final static function assertEqual($actual, $expected) {
      if (!\hacklib_cast_as_boolean(self::isEqual($actual, $expected))) {
        throw new \Exception(
          "Expected ".dump($expected).", got ".dump($actual)
        );
      }
    }
    public final static function isEqual($a, $b) {
      if (\hacklib_cast_as_boolean(\is_float($a)) &&
          \hacklib_cast_as_boolean(\is_float($b))) {
        if (\hacklib_cast_as_boolean(\is_nan($a)) &&
            \hacklib_cast_as_boolean(\is_nan($b))) {
          return true;
        }
        if (\hacklib_not_equals(signbit($a), signbit($b))) {
          return false;
        }
      }
      if (\hacklib_cast_as_boolean(\is_array($a)) &&
          \hacklib_cast_as_boolean(\is_array($b))) {
        if (\count($a) !== \count($b)) {
          return false;
        }
        $iterA = new ArrayIterator($a);
        $iterB = new ArrayIterator($b);
        for (
          $iterA->reset(), $iterB->reset();
          \hacklib_cast_as_boolean($iterA->valid()) ||
          \hacklib_cast_as_boolean($iterB->valid());
          $iterA->next(), $iterB->next()
        ) {
          if ((!\hacklib_cast_as_boolean(
                 self::isEqual($iterA->key(), $iterB->key())
               )) ||
              (!\hacklib_cast_as_boolean(
                 self::isEqual($iterA->current(), $iterB->current())
               ))) {
            return false;
          }
        }
        return true;
      }
      return $a === $b;
    }
    public final static function getException($f) {
      try {
        $f();
      } catch (\Exception $e) {
        return $e;
      }
      throw new \Exception("Code was supposed to throw but didnt");
    }
    public static function name() {
      return self::class_();
    }
    public static function description() {
      return "";
    }
    public abstract function run();
  }
  class TestTests extends Test {
    public function run() {
      self::assertEqual(
        self::getException(
          function() {
            Test::assertEqual(array(), array(1));
          }
        )->getMessage(),
        "Expected [1], got []"
      );
      self::assertEqual(
        self::getException(
          function() {
            Test::assertEqual(array(2), array(1));
          }
        )->getMessage(),
        "Expected [1], got [2]"
      );
      self::assertEqual(
        self::getException(
          function() {
            Test::assertEqual(array(0.0), array(0.0 * (-1.0)));
          }
        )->getMessage(),
        "Expected [-0.0], got [0.0]"
      );
      self::assertEqual(
        self::getException(
          function() {
            Test::assertEqual(0.0, 0.0 * (-1.0));
          }
        )->getMessage(),
        "Expected -0.0, got 0.0"
      );
      self::assertEqual(
        self::getException(
          function() {
            Test::getException(function() {});
          }
        )->getMessage(),
        "Code was supposed to throw but didnt"
      );
    }
  }
  class TestToHex extends Test {
    public function run() {
      self::assertEqual(to_hex("\000\377 "), "00ff20");
    }
  }
  class TestFromHex extends Test {
    public function run() {
      self::assertEqual(from_hex("00ff20"), "\000\377 ");
      self::assertEqual(from_hex("00Ff20"), "\000\377 ");
    }
  }
  class TestStringShuffle extends Test {
    public function run() {
      self::assertEqual(length(shuffle_string("abc")), 3);
    }
  }
  class TestReverseString extends Test {
    public function run() {
      self::assertEqual(reverse_string("abc"), "cba");
      self::assertEqual(reverse_string(""), "");
    }
  }
  class TestToLower extends Test {
    public function run() {
      self::assertEqual(to_lower("ABC.1.2.3"), "abc.1.2.3");
    }
  }
  class TestToUpper extends Test {
    public function run() {
      self::assertEqual(to_upper("abc.1.2.3"), "ABC.1.2.3");
    }
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
  class TestStringChunk extends Test {
    public function run() {
      self::assertEqual(chunk_string("abc", 1), array("a", "b", "c"));
      self::assertEqual(chunk_string("abc", 2), array("ab", "c"));
      self::assertEqual(chunk_string("abc", 3), array("abc"));
    }
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
  class TestStringSplice extends Test {
    public function run() {
      self::assertEqual(splice("abc", 1, 1), "ac");
      self::assertEqual(splice("abc", 1, 1, "lol"), "alolc");
    }
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
  class TestStringRepeat extends Test {
    public function run() {
      self::assertEqual(repeat_string("123", 3), "123123123");
    }
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
  class TestStringEndsWith extends Test {
    public function run() {
      self::assertEqual(ends_with("abbb", "bb"), true);
      self::assertEqual(ends_with("abbb", "ba"), false);
      self::assertEqual(ends_with("abbb", ""), true);
      self::assertEqual(ends_with("", ""), true);
      self::assertEqual(ends_with("", "a"), false);
    }
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
  class TestFloatRounding extends Test {
    public function run() {
      self::assertEqual(round_half_down(0.5), 0.0);
      self::assertEqual(round_half_down(1.5), 1.0);
      self::assertEqual(round_half_down(-0.5), -1.0);
      self::assertEqual(round_half_down(-1.5), -2.0);
      self::assertEqual(round_half_down(INF), INF);
      self::assertEqual(round_half_down(-INF), -INF);
      self::assertEqual(round_half_down(NAN), NAN);
      self::assertEqual(round_half_up(0.5), 1.0);
      self::assertEqual(round_half_up(1.5), 2.0);
      self::assertEqual(round_half_up(-0.5), 0.0);
      self::assertEqual(round_half_up(-1.5), -1.0);
      self::assertEqual(round_half_up(INF), INF);
      self::assertEqual(round_half_up(-INF), -INF);
      self::assertEqual(round_half_up(NAN), NAN);
      self::assertEqual(round_half_to_inf(0.5), 1.0);
      self::assertEqual(round_half_to_inf(1.5), 2.0);
      self::assertEqual(round_half_to_inf(-0.5), -1.0);
      self::assertEqual(round_half_to_inf(-1.5), -2.0);
      self::assertEqual(round_half_to_inf(INF), INF);
      self::assertEqual(round_half_to_inf(-INF), -INF);
      self::assertEqual(round_half_to_inf(NAN), NAN);
      self::assertEqual(round_half_to_zero(0.5), 0.0);
      self::assertEqual(round_half_to_zero(1.5), 1.0);
      self::assertEqual(round_half_to_zero(-0.5), 0.0);
      self::assertEqual(round_half_to_zero(-1.5), -1.0);
      self::assertEqual(round_half_to_zero(INF), INF);
      self::assertEqual(round_half_to_zero(-INF), -INF);
      self::assertEqual(round_half_to_zero(NAN), NAN);
      self::assertEqual(round_half_to_even(0.5), 0.0);
      self::assertEqual(round_half_to_even(1.5), 2.0);
      self::assertEqual(round_half_to_even(-0.5), 0.0);
      self::assertEqual(round_half_to_even(-1.5), -2.0);
      self::assertEqual(round_half_to_even(INF), INF);
      self::assertEqual(round_half_to_even(-INF), -INF);
      self::assertEqual(round_half_to_even(NAN), NAN);
      self::assertEqual(round_half_to_odd(0.5), 1.0);
      self::assertEqual(round_half_to_odd(1.5), 1.0);
      self::assertEqual(round_half_to_odd(-0.5), -1.0);
      self::assertEqual(round_half_to_odd(-1.5), -1.0);
      self::assertEqual(round_half_to_odd(INF), INF);
      self::assertEqual(round_half_to_odd(-INF), -INF);
      self::assertEqual(round_half_to_odd(NAN), NAN);
    }
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
  class TestLeapYear extends Test {
    public function run() {
      self::assertEqual(is_leap_year(2016), true);
      self::assertEqual(is_leap_year(2015), false);
      self::assertEqual(is_leap_year(2000), true);
      self::assertEqual(is_leap_year(2400), true);
      self::assertEqual(is_leap_year(2401), false);
      self::assertEqual(is_leap_year(2404), true);
      self::assertEqual(is_leap_year(2500), false);
      self::assertEqual(is_leap_year(2504), true);
      self::assertEqual(is_leap_year(1900), false);
      self::assertEqual(is_leap_year(2100), false);
      self::assertEqual(is_leap_year(2104), true);
    }
  }
  class TestDaysInMonth extends Test {
    public function run() {
      self::assertEqual(days_in_month(2016, 1), 31);
      self::assertEqual(days_in_month(2016, 2), 29);
      self::assertEqual(days_in_month(2016, 3), 31);
      self::assertEqual(days_in_month(2016, 4), 30);
      self::assertEqual(days_in_month(2016, 5), 31);
      self::assertEqual(days_in_month(2016, 6), 30);
      self::assertEqual(days_in_month(2016, 7), 31);
      self::assertEqual(days_in_month(2016, 8), 31);
      self::assertEqual(days_in_month(2016, 9), 30);
      self::assertEqual(days_in_month(2016, 10), 31);
      self::assertEqual(days_in_month(2016, 11), 30);
      self::assertEqual(days_in_month(2016, 12), 31);
      self::assertEqual(days_in_month(2015, 2), 28);
      self::assertEqual(days_in_month(2012, 2), 29);
    }
  }
  class TestOverflowDate extends Test {
    public function run() {
      self::assertEqual(
        overflow_date(array(2015, 1, 0)),
        array(2014, 12, 31)
      );
      self::assertEqual(
        overflow_date(array(2015, 1, 365)),
        array(2015, 12, 31)
      );
      self::assertEqual(overflow_date(array(2015, 2, 29)), array(2015, 3, 1));
      self::assertEqual(
        overflow_date(array(2016, 1, 366)),
        array(2016, 12, 31)
      );
      self::assertEqual(
        overflow_date(array(2015, 13, 366)),
        array(2016, 12, 31)
      );
      self::assertEqual(
        overflow_date(array(2016, 16, ((-31) - 28) - 31)),
        array(2016, 12, 31)
      );
      self::assertEqual(
        overflow_date(array(2016, -3, 30 + 31 + 30 + 31 + 17)),
        array(2016, 1, 17)
      );
      self::assertEqual(
        overflow_date(array(2016, -3, -8)),
        array(2015, 8, 23)
      );
    }
  }
  class TestValidDate extends Test {
    public function run() {
      self::assertEqual(is_valid_date(array(2016, 2, 29)), true);
      self::assertEqual(is_valid_date(array(2015, 2, 29)), false);
      self::assertEqual(is_valid_date(array(2016, 11, 23)), true);
      self::assertEqual(is_valid_date(array(2016, 11, 30)), true);
      self::assertEqual(is_valid_date(array(2016, 11, 31)), false);
      self::assertEqual(is_valid_date(array(2016, 12, 31)), true);
      self::assertEqual(is_valid_date(array(2016, 12, 32)), false);
      self::assertEqual(is_valid_date(array(2016, 13, 31)), false);
      self::assertEqual(is_valid_date(array(2016, 0, 31)), false);
      self::assertEqual(is_valid_date(array(2016, -1, 31)), false);
      self::assertEqual(is_valid_date(array(2016, 1, 30)), true);
      self::assertEqual(is_valid_date(array(2016, 1, 0)), false);
      self::assertEqual(is_valid_date(array(2016, 1, -1)), false);
      self::assertEqual(is_valid_date(array(0, 1, 1)), true);
      self::assertEqual(is_valid_date(array(INT_MAX, 1, 1)), true);
      self::assertEqual(is_valid_date(array(INT_MIN, 1, 1)), true);
    }
  }
  class TestQuotRemDivMod extends Test {
    public function run() {
      self::assertEqual(quot(-20, 3), -6);
      self::assertEqual(rem(-20, 3), -2);
      self::assertEqual(div(-20, 3), -7);
      self::assertEqual(mod(-20, 3), 1);
      self::assertEqual(mod(2, 3), 2);
      self::assertEqual(rem(2, 3), 2);
      self::assertEqual(mod(10, 5), 0);
      self::assertEqual(rem(10, 5), 0);
      self::assertEqual(mod(1, -1), 0);
      self::assertEqual(rem(1, -1), 0);
      self::assertEqual(mod(2, -3), -1);
      self::assertEqual(rem(2, -3), 2);
      self::assertEqual(mod(5, 3), 2);
      self::assertEqual(rem(5, 3), 2);
      self::assertEqual(mod(5, -3), -1);
      self::assertEqual(rem(5, -3), 2);
      self::assertEqual(mod(-5, 3), 1);
      self::assertEqual(rem(-5, 3), -2);
      self::assertEqual(mod(-5, -3), -2);
      self::assertEqual(rem(-5, -3), -2);
      self::assertEqual(div_mod(-20, 3), array(-7, 1));
      self::assertEqual(div_mod(-20, -3), array(6, -2));
      self::assertEqual(quot_rem(-20, 3), array(-6, -2));
      self::assertEqual(quot_rem(-20, -3), array(6, -2));
    }
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
  class TestFrac extends Test {
    public function run() {
      self::assertEqual(frac(0.1), 0.1);
      self::assertEqual(frac(0.9), 0.9);
      self::assertEqual(frac(0.5), 0.5);
      self::assertEqual(frac(0.0), 0.0);
      self::assertEqual(frac(5.0), 5.0 - 5.0);
      self::assertEqual(frac(5.1), 5.1 - 5.0);
      self::assertEqual(frac(5.9), 5.9 - 5.0);
      self::assertEqual(frac(5.5), 5.5 - 5.0);
      self::assertEqual(frac(-0.1), -0.1);
      self::assertEqual(frac(-0.9), -0.9);
      self::assertEqual(frac(-0.5), -0.5);
      self::assertEqual(frac(-0.0), 0.0);
      self::assertEqual(frac(-5.0), (-5.0) + 5.0);
      self::assertEqual(frac(-5.1), (-5.1) + 5.0);
      self::assertEqual(frac(-5.9), (-5.9) + 5.0);
      self::assertEqual(frac(-5.5), (-5.5) + 5.0);
    }
  }
  class TestTypeof extends Test {
    public function run() {
      self::assertEqual(typeof(NULL_INT), "null");
      self::assertEqual(typeof(true), "bool");
      self::assertEqual(typeof(false), "bool");
      self::assertEqual(typeof(0.0), "float");
      self::assertEqual(typeof(PI), "float");
      self::assertEqual(typeof(0), "int");
      self::assertEqual(typeof(129837), "int");
      self::assertEqual(typeof(array()), "array");
      self::assertEqual(typeof(array(array())), "array");
      self::assertEqual(typeof(array(1)), "array");
      self::assertEqual(typeof(new \stdClass()), "stdClass");
      self::assertEqual(typeof(function() {}), "Closure");
      self::assertEqual(typeof(\fopen("php://memory", "rb")), "resource");
    }
  }
  class TestDateTime extends Test {
    public function run() {
      $utc = TimeZone::UTC();
      $melb = TimeZone::create("Australia/Melbourne");
      self::assertEqual($utc->getName(), "UTC");
      self::assertEqual($melb->getName(), "Australia/Melbourne");
      $dt = DateTime::fromParts(array(2017, 1, 3, 22, 20, 8, 15648), $melb);
      self::assertEqual($dt->getYear(), 2017);
      self::assertEqual($dt->getMonth(), 1);
      self::assertEqual($dt->getDay(), 3);
      self::assertEqual($dt->getHour(), 22);
      self::assertEqual($dt->getMinute(), 20);
      self::assertEqual($dt->getSecond(), 8);
      self::assertEqual($dt->getMicrosecond(), 15648);
      self::assertEqual($dt->getTimestamp(), 1483442408);
      self::assertEqual($dt->getMicrotimestamp(), 1483442408015648);
      self::assertEqual($dt->getUTCOffset(), 39600);
      self::assertEqual($dt->getTimezone()->getName(), "Australia/Melbourne");
      $format = "Y-m-d H:i:s.uP";
      self::assertEqual(
        $dt->format($format),
        "2017-01-03 22:20:08.015648+11:00"
      );
      self::assertEqual(
        $dt->withYear(826)->format($format),
        "0826-01-03 22:20:08.015648+10:00"
      );
      self::assertEqual(
        $dt->withMonth(15)->format($format),
        "2018-03-03 22:20:08.015648+11:00"
      );
      self::assertEqual(
        $dt->withDay(15)->format($format),
        "2017-01-15 22:20:08.015648+11:00"
      );
      self::assertEqual(
        $dt->withHour(-5)->format($format),
        "2017-01-02 19:20:08.015648+11:00"
      );
      self::assertEqual(
        $dt->withMinute(-5)->format($format),
        "2017-01-03 21:55:08.015648+11:00"
      );
      self::assertEqual(
        $dt->withSecond(-5)->format($format),
        "2017-01-03 22:19:55.015648+11:00"
      );
      self::assertEqual(
        $dt->withMicrosecond(-5)->format($format),
        "2017-01-03 22:20:07.999995+11:00"
      );
      self::assertEqual(
        $dt->withTimezone($utc)->format($format),
        "2017-01-03 11:20:08.015648+00:00"
      );
      self::assertEqual(
        $dt->withTimestamp(10 - 36000, 8623467)->format($format),
        "1970-01-01 00:00:18.623467+10:00"
      );
      self::assertEqual(
        $dt->withISODate(1984, -25, -8)->format($format),
        "1983-06-25 22:20:08.015648+10:00"
      );
      self::assertEqual(
        DateTime::fromMicrotimestamp(
          $dt->getMicrotimestamp(),
          $dt->getTimezone()
        )->format($format),
        "2017-01-03 22:20:08.015648+11:00"
      );
      $dt2 = $dt->withDate(2017, 1, 1);
      self::assertEqual($dt2->getISOYear(), 2016);
      self::assertEqual($dt2->getISOWeek(), 52);
      self::assertEqual($dt2->getISOWeekday(), 7);
      $dt2 = $dt->withTimezone($utc);
      self::assertEqual(
        $dt2->getParts(),
        array(2017, 1, 3, 11, 20, 8, 15648)
      );
      self::assertEqual($dt2->getPart(DateTime::PART_HOUR), 11);
      self::assertEqual(
        self::getException(
          function() use ($dt2) {
            $dt2->getPart(543);
          }
        )->getMessage(),
        "Invalid date/time part: 543"
      );
      self::assertEqual($dt2->getUTCOffset(), 0);
      $nowNoUsec = DateTime::now($melb);
      $count = 0;
      do {
        $nowWithUsec = DateTime::now($melb, true);
        $count++;
        if ($count > 10) {
          throw new \Exception("Cant get current time with micrseconds :(");
        }
      } while (!\hacklib_cast_as_boolean($nowWithUsec->getMicrosecond()));
      self::assertEqual($nowNoUsec->getMicrosecond(), 0);
      self::assertEqual(
        $nowWithUsec->withMicrosecond(0)->format($format),
        $nowNoUsec->format($format)
      );
      self::assertEqual(
        $nowNoUsec->withMicrosecond(0)->format($format),
        $nowNoUsec->format($format)
      );
      self::assertEqual(
        DateTime::fuzzyParse("first sat of July 2015", $melb)
          ->format($format),
        "2015-07-04 00:00:00.000000+10:00"
      );
      self::assertEqual(
        DateTime::fromTimestamp((-5) - 36000, $melb, -5)->format($format),
        "1969-12-31 23:59:54.999995+10:00"
      );
      self::assertEqual(
        self::getException(
          function() use ($utc) {
            DateTime::parse("Y-m-d H:i:s", "", $utc);
          }
        )->getMessage(),
        "Could not parse date \"\" in format \"Y-m-d H:i:s\": Data missing at offset 0"
      );
      self::assertEqual(
        self::getException(
          function() use ($utc) {
            DateTime::fuzzyParse("99999999999999999", $utc);
          }
        )->getMessage(),
        "DateTimeImmutable::__construct(): Failed to parse time string (99999999999999999) at position 16 (9): Unexpected character"
      );
    }
  }
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
  class TestFileSystem extends Test {
    public function run() {
      $fs = new LocalFileSystem();
      $path = "/tmp/hufs-test-".\mt_rand();
      self::testFilesystem($fs, $path);
      $fs = new FileSystemStreamWrapper($fs);
      self::testFilesystem($fs, $path);
    }
    private static function testFilesystem($fs, $base) {
      self::assertEqual($fs->trystat($base), NULL_INT);
      $fs->mkdir($base);
      self::assertEqual($fs->stat($base)->modeSymbolic(), "drwxr-xr-x");
      $file = $fs->join($base, "foo");
      $fs->writeFile($file, "contents");
      self::assertEqual($fs->readFile($file), "contents");
      $open = $fs->open($file, "rb");
      self::assertEqual($open->eof(), false);
      self::assertEqual($open->tell(), 0);
      self::assertEqual($open->read(4), "cont");
      self::assertEqual($open->eof(), false);
      self::assertEqual($open->tell(), 4);
      $open->seek(2);
      self::assertEqual($open->tell(), 2);
      $open->seek(2, \SEEK_CUR);
      self::assertEqual($open->tell(), 4);
      self::assertEqual($open->eof(), false);
      self::assertEqual($open->read(100), "ents");
      self::assertEqual($open->read(100), "");
      self::assertEqual($open->eof(), true);
      self::assertEqual($open->getSize(), 8);
      self::assertEqual($open->stat()->modeSymbolic(), "-rw-r--r--");
      self::assertEqual($open->getContents(), "");
      self::assertEqual($open->__toString(), "contents");
      self::assertEqual($open->getContents(), "");
      $open->rewind();
      self::assertEqual($open->getContents(), "contents");
      self::assertEqual($open->tell(), 8);
      self::assertEqual($open->isReadable(), true);
      self::assertEqual($open->isWritable(), false);
      self::assertEqual($open->isSeekable(), true);
      $open->close();
      $open = $fs->open($file, "wb+");
      self::assertEqual($open->tell(), 0);
      self::assertEqual($open->eof(), false);
      self::assertEqual($open->getSize(), 0);
      self::assertEqual($open->getContents(), "");
      self::assertEqual($open->__toString(), "");
      self::assertEqual($open->write("hello"), 5);
      self::assertEqual($open->tell(), 5);
      self::assertEqual($open->eof(), true);
      self::assertEqual($open->getContents(), "");
      self::assertEqual($open->__toString(), "hello");
      $open->rewind();
      self::assertEqual($open->getContents(), "hello");
      self::assertEqual($open->getContents(), "");
      $open->seek(2);
      self::assertEqual($open->tell(), 2);
      self::assertEqual($open->write("__"), 2);
      self::assertEqual($open->tell(), 4);
      self::assertEqual($open->getContents(), "o");
      self::assertEqual($open->tell(), 5);
      self::assertEqual($open->__toString(), "he__o");
      self::assertEqual($open->tell(), 5);
      self::assertEqual($open->eof(), true);
      if ($fs instanceof SymlinkFileSystemInterface) {
        $fs->symlink($file."2", $file);
        self::assertEqual($fs->stat($file)->modeSymbolic(), "-rw-r--r--");
        self::assertEqual($fs->stat($file."2")->modeSymbolic(), "-rw-r--r--");
        self::assertEqual($fs->lstat($file)->modeSymbolic(), "-rw-r--r--");
        self::assertEqual(
          $fs->lstat($file."2")->modeSymbolic(),
          "lrwxrwxrwx"
        );
      }
      $fs->unlink($file);
      $fs->rmdirRec($base);
    }
  }
}
