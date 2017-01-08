<?hh // strict

namespace HackUtils;

final class _Tests {
  public static function getTests(): array<classname<Test>> {
    return [
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
      TestOverflowDateTime::class_(),
      TestJSONEncode::class_(),
      TestJSONDecode::class_(),
      TestJSONError::class_(),
    ];
  }

  public static function main(): void {
    foreach (self::getTests() as $test) {
      print '  '.$test::name()."\n";
      $test::runStatic();
    }
    print "done\n";
  }
}

<<__ConsistentConstruct>>
abstract class Test {
  public final static function runStatic(): void {
    $self = new static();
    $self->run();
  }

  public final static function class_(): classname<this> {
    return \get_called_class();
  }

  public final static function assertEqual(
    mixed $actual,
    mixed $expected,
  ): void {
    if (!self::isEqual($actual, $expected)) {
      throw new \Exception(
        "Expected ".dump($expected).", got ".dump($actual),
      );
    }
  }

  public final static function isEqual(mixed $a, mixed $b): bool {
    if (\is_float($a) && \is_float($b)) {
      // Consider NAN as equal to itself (PHP doesn't)
      if (\is_nan($a) && \is_nan($b))
        return true;
      // Don't consider -0.0 and +0.0 as equal (PHP does)
      if (signbit($a) != signbit($b))
        return false;
    }

    if (\is_array($a) && \is_array($b)) {
      if (\count($a) !== \count($b))
        return false;
      // Iterate over both arrays in parallel
      $iterA = new ArrayIterator($a);
      $iterB = new ArrayIterator($b);
      for (
        $iterA->reset(), $iterB->reset();
        $iterA->valid() || $iterB->valid();
        $iterA->next(), $iterB->next()
      ) {
        if (!self::isEqual($iterA->key(), $iterB->key()) ||
            !self::isEqual($iterA->current(), $iterB->current())) {
          return false;
        }
      }
      return true;
    }

    return $a === $b;
  }

  public final static function assertException(
    fn0<void> $f,
    string $message = '',
    int $code = 0,
  ): void {
    $e = self::getException($f);
    self::assertEqual($e->getMessage(), $message);
    self::assertEqual($e->getCode(), $code);
  }

  public final static function getException(fn0<void> $f): \Exception {
    try {
      $f();
    } catch (\Exception $e) {
      return $e;
    }
    throw new \Exception('Code was supposed to throw but didnt');
  }

  public static function name(): string {
    return self::class_();
  }

  public static function description(): string {
    return '';
  }

  public abstract function run(): void;
}

abstract class SampleTest<Tin, Tout> extends Test {
  public final function run(): void {
    foreach ($this->getData() as $v) {
      list($in, $out) = $v;
      self::assertEqual($this->evaluate($in), $out);
    }
  }

  public abstract function getData(): array<(Tin, Tout)>;
  public abstract function evaluate(Tin $in): Tout;
}

class TestTests extends Test {
  public function run(): void {
    self::assertEqual(
      self::getException(
        function() {
          Test::assertEqual([], [1]);
        },
      )->getMessage(),
      "Expected [1], got []",
    );

    self::assertEqual(
      self::getException(
        function() {
          Test::assertEqual([2], [1]);
        },
      )->getMessage(),
      "Expected [1], got [2]",
    );

    self::assertEqual(
      self::getException(
        function() {
          Test::assertEqual([0.0], [0.0 * -1.0]);
        },
      )->getMessage(),
      "Expected [-0.0], got [0.0]",
    );

    self::assertEqual(
      self::getException(
        function() {
          Test::assertEqual(0.0, 0.0 * -1.0);
        },
      )->getMessage(),
      'Expected -0.0, got 0.0',
    );

    self::assertEqual(
      self::getException(
        function() {
          Test::getException(function() {});
        },
      )->getMessage(),
      'Code was supposed to throw but didnt',
    );
  }
}
