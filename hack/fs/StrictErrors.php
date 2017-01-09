<?hh // strict

namespace HackUtils;

<<__ConsistentConstruct>>
abstract class Errors {
  public static function start(): this {
    return new static();
  }
  private bool $bound = false;
  public function __construct() {
    $self = $this;
    $func = function($type, $message, $file, $line) use ($self) {
      $e = new \ErrorException($message, 0, $type, $file, $line);
      $self->handleError($e);
    };
    \set_error_handler($func);
    $this->bound = true;
  }
  public function __destruct() {
    $this->finish();
  }
  public abstract function handleError(\ErrorException $e): void;
  public final function finish(): void {
    if ($this->bound) {
      \restore_error_handler();
      $this->bound = false;
    }
  }
  public final function finishAny<T>(T $x): T {
    $this->finish();
    return $x;
  }
  public final function finishZero(mixed $x): int {
    return $this->finishAny(Exception::assertZero($x));
  }
  public final function finishArray<T>(T $x): T {
    return $this->finishAny(Exception::assertArray($x));
  }
  public final function finishString(mixed $x): string {
    return $this->finishAny(Exception::assertString($x));
  }
  public final function finishInt(mixed $x): int {
    return $this->finishAny(Exception::assertInt($x));
  }
  public final function finishTrue(mixed $x): bool {
    return $this->finishAny(Exception::assertTrue($x));
  }
  public final function finishResource(mixed $x): resource {
    return $this->finishAny(Exception::assertResource($x));
  }
  public final function finishBool(mixed $x): bool {
    return $this->finishAny(Exception::assertBool($x));
  }
}

final class StrictErrors extends Errors {
  public function handleError(\ErrorException $e): void {
    throw $e;
  }
}

final class IgnoreErrors extends Errors {
  public function handleError(\ErrorException $e): void {}
}

final class CaptureErrors extends Errors {
  private array<\ErrorException> $errors = [];
  public function handleError(\ErrorException $e): void {
    $this->errors[] = $e;
  }
  public function getErrors(): array<\ErrorException> {
    return $this->errors;
  }
}
