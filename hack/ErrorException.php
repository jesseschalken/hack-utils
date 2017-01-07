<?hh // strict

namespace HackUtils;

final class ErrorException extends \ErrorException {
  private static ?(function(int, string, string, int): bool) $handler;

  public static function clearLast(): void {
    \error_clear_last();
  }

  public static function getLast(): ?ErrorException {
    $error = \error_get_last();
    if ($error && \strlen($error['message'])) {
      return new self(
        $error['message'],
        0,
        $error['type'],
        $error['file'],
        $error['line'],
      );
    }
    return new_null();
  }

  public static function setErrorHandler(): void {
    \set_error_handler(
      self::$handler !== null
        ? self::$handler
        : self::$handler = function($type, $message, $file, $line) {
          throw new ErrorException($message, 0, $type, $file, $line);
        },
    );
  }

  public static function restoreErrorHandler(): void {
    \restore_error_handler();
  }
}
