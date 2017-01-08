<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class _Tests {
    public static function getTests() {
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
        TestDateTime::class_(),
        TestException::class_(),
        PCRE\Test::class_(),
        TestCtype::class_(),
        TestOverflowDateTime::class_()
      );
    }
    public static function main() {
      foreach (self::getTests() as $test) {
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
    public final static function assertException(
      $f,
      $message = "",
      $code = 0
    ) {
      $e = self::getException($f);
      self::assertEqual($e->getMessage(), $message);
      self::assertEqual($e->getCode(), $code);
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
  abstract class SampleTest extends Test {
    public final function run() {
      foreach ($this->getData() as $v) {
        list($in, $out) = $v;
        self::assertEqual($this->evaluate($in), $out);
      }
    }
    public abstract function getData();
    public abstract function evaluate($in);
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
}
