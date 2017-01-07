<?hh // strict

namespace HackUtils;

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
    throw new static('Expected '.$expected.', got '.$actual.'.');
  }
}