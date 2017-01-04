<?hh // strict

namespace HackUtils;

class ErrorAssert extends \RuntimeException {
  public final static function isZero(string $name, mixed $ret): void {
    if ($ret !== 0)
      throw self::create($name);
  }

  public final static function isArray<T>(string $name, T $ret): T {
    if (!\is_array($ret))
      throw self::create($name);
    return $ret;
  }

  public final static function isString(string $name, mixed $ret): string {
    if (!\is_string($ret))
      throw self::create($name);
    return $ret;
  }

  public final static function isInt(string $name, mixed $ret): int {
    if (!\is_int($ret))
      throw self::create($name);
    return $ret;
  }

  public final static function isTrue(string $name, mixed $ret): void {
    if ($ret !== true)
      throw self::create($name);
  }

  public final static function isResource(string $name, mixed $ret): resource {
    if (!\is_resource($ret))
      throw self::create($name);
    return $ret;
  }

  public final static function isBool(string $name, mixed $ret): bool {
    if (!\is_bool($ret))
      throw self::create($name);
    return $ret;
  }

  private static function create(string $name): ErrorAssert {
    $error = \error_get_last();
    $msg = $name.'() failed';
    if ($error) {
      $e = new self($msg.': '.$error['message']);
      $e->file = $error['file'];
      $e->line = $error['line'];
      return $e;
    }
    return new self($msg);
  }
}
