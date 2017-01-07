<?hh // strict

namespace HackUtils;

final class StrictErrors {
  private static ?fn4<int, string, string, int, bool> $handler;
  public static function start(): StrictErrors {
    \set_error_handler(
      self::$handler !== null
        ? self::$handler
        : self::$handler = function($type, $message, $file, $line): bool {
          throw new \ErrorException($message, 0, $type, $file, $line);
        },
    );
    return new self();
  }
  private bool $bound = true;
  private function __construct() {}
  public function __destruct() {
    $this->finish();
  }
  public function finish(): void {
    if ($this->bound) {
      \restore_error_handler();
      $this->bound = false;
    }
  }
  public function finishAny<T>(T $x): T {
    $this->finish();
    return $x;
  }
  public function finishZero(mixed $x): int {
    return $this->finishAny(Exception::assertZero($x));
  }
  public function finishArray<T>(T $x): T {
    return $this->finishAny(Exception::assertArray($x));
  }
  public function finishString(mixed $x): string {
    return $this->finishAny(Exception::assertString($x));
  }
  public function finishInt(mixed $x): int {
    return $this->finishAny(Exception::assertInt($x));
  }
  public function finishTrue(mixed $x): bool {
    return $this->finishAny(Exception::assertTrue($x));
  }
  public function finishResource(mixed $x): resource {
    return $this->finishAny(Exception::assertResource($x));
  }
  public function finishBool(mixed $x): bool {
    return $this->finishAny(Exception::assertBool($x));
  }
}
