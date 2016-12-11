<?hh // strict

namespace HackUtils;

final class JSON {
  /**
   * Convert a value to JSON. Only accepts values that can be converted to their
   * exact original by json_decode(). Those are:
   * - int
   * - float (but not INF, -INF or NAN)
   * - bool
   * - string (must be valid UTF8 unless $binary = true)
   * - null
   * - array (both sequential and associative) whose values are also valid
   *
   * Set $binary to TRUE to have strings interpereted as binary instead of UTF8.
   *
   * Throws a JSONException if the value cannot be converted to JSON.
   */
  public static function encode(
    mixed $value,
    bool $binary = false,
    bool $pretty = false,
  ): string {
    $flags = 0;

    if (\defined('JSON_PRETTY_PRINT') && $pretty) {
      $flags |= \JSON_PRETTY_PRINT;
    }

    // By default we shouldn't escape more than we need to.
    if (\defined('JSON_UNESCAPED_SLASHES')) {
      $flags |= \JSON_UNESCAPED_SLASHES;
    }
    if (\defined('JSON_UNESCAPED_UNICODE')) {
      $flags |= \JSON_UNESCAPED_UNICODE;
    }

    // No reason 0 floats should turn into 0 ints.
    if (\defined('JSON_PRESERVE_ZERO_FRACTION')) {
      $flags |= \JSON_PRESERVE_ZERO_FRACTION;
    }

    if ($binary) {
      $value = self::mapStrings(
        $value,
        function($x) {
          return \utf8_encode($x);
        },
      );
    }

    self::checkValue($value);
    $json = \json_encode($value, $flags);
    self::checkError();

    return $json;
  }

  /**
   * Parse JSON string. JSON objects are converted to associative arrays.
   * Set $binary to TRUE to produce binary strings instead of UTF8 (code points
   * >255 will be discarded).
   *
   * Throws a JSONException if the value string cannot be parsed.
   */
  public static function decode(string $json, bool $binary = false): mixed {
    $value = \json_decode($json, true);
    self::checkError();

    if ($binary) {
      $value = self::mapStrings(
        $value,
        function($x) {
          return \utf8_decode($x);
        },
      );
    }

    return $value;
  }

  private static function checkValue(mixed $x): void {
    if (\is_object($x)) {
      throw new JSONException(
        'Objects are not supported. Use an associative array.',
        \JSON_ERROR_UNSUPPORTED_TYPE,
      );
    }
    if (\is_array($x)) {
      foreach ($x as $v) {
        self::checkValue($v);
      }
    }
  }

  private static function mapStrings(
    mixed $x,
    (function(string): string) $f,
  ): mixed {
    if (\is_string($x)) {
      return $f($x);
    }
    if (\is_array($x)) {
      $r = [];
      foreach ($x as $k => $v) {
        $k = self::mapStrings($k, $f);
        $v = self::mapStrings($v, $f);
        $r[$k] = $v;
      }
      return $r;
    }
    return $x;
  }

  private static function checkError(): void {
    if (\json_last_error() !== \JSON_ERROR_NONE) {
      throw new JSONException(\json_last_error_msg(), \json_last_error());
    }
  }

  private function __construct() {}
}

final class JSONException extends \Exception {}
