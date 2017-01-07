<?hh // strict

namespace HackUtils;

class TestException extends Test {
  public function run(): void {
    Exception::assertEqual(1, 1);
    Exception::assertZero(0);
    Exception::assertArray([]);
    Exception::assertString('1');
    Exception::assertInt(1);
    Exception::assertTrue(true);
    Exception::assertResource(\fopen('php://memory', 'w+b'));
    Exception::assertBool(false);

    self::assertException(
      function() {
        Exception::assertEqual(1, 0);
      },
      'Expected 0, got 1',
    );
    self::assertException(
      function() {
        Exception::assertZero('hello');
      },
      'Expected 0, got "hello"',
    );
    self::assertException(
      function() {
        Exception::assertArray(1.4);
      },
      'Expected array, got float',
    );
    self::assertException(
      function() {
        Exception::assertString(1);
      },
      'Expected string, got int',
    );
    self::assertException(
      function() {
        Exception::assertInt(1.0);
      },
      'Expected int, got float',
    );
    self::assertException(
      function() {
        Exception::assertTrue(1.0);
      },
      'Expected true, got 1.0',
    );
    self::assertException(
      function() {
        Exception::assertResource('1.0');
      },
      'Expected resource, got string',
    );
    self::assertException(
      function() {
        Exception::assertBool(new \stdClass());
      },
      'Expected bool, got stdClass',
    );
  }
}

<<__ConsistentConstruct>>
class Exception extends \Exception {
  public final static function assertEqual<T>(mixed $a, T $b): T {
    if ($a !== $b)
      throw static::create(dump($a), dump($b));
    return $b;
  }

  public final static function assertZero(mixed $x): int {
    return self::assertEqual($x, 0);
  }

  public final static function assertArray<T>(T $x): T {
    if (!\is_array($x))
      throw static::create(typeof($x), 'array');
    return $x;
  }

  public final static function assertString(mixed $x): string {
    if (!\is_string($x))
      throw static::create(typeof($x), 'string');
    return $x;
  }

  public final static function assertInt(mixed $x): int {
    if (!\is_int($x))
      throw static::create(typeof($x), 'int');
    return $x;
  }

  public final static function assertTrue(mixed $x): bool {
    return self::assertEqual($x, true);
  }

  public final static function assertResource(mixed $x): resource {
    if (!\is_resource($x))
      throw static::create(typeof($x), 'resource');
    return $x;
  }

  public final static function assertBool(mixed $x): bool {
    if (!\is_bool($x))
      throw static::create(typeof($x), 'bool');
    return $x;
  }

  public final static function create(
    string $actual,
    string $expected,
  ): \Exception {
    throw new static('Expected '.$expected.', got '.$actual);
  }
}
