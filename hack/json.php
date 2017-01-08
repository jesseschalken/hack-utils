<?hh // strict

namespace HackUtils;

class TestJSONEncode extends SampleTest<(mixed, bool, bool), string> {
  public function evaluate((mixed, bool, bool) $in): string {
    return JSON::encode($in[0], $in[1], $in[2]);
  }
  public function getData(): array<((mixed, bool, bool), string)> {
    return [
      tuple(tuple(1, false, false), '1'),
      tuple(tuple(2, false, false), '2'),
      tuple(tuple(null, false, false), 'null'),
      tuple(tuple(true, false, false), 'true'),
      tuple(tuple(false, false, false), 'false'),
      tuple(tuple("foo", false, false), '"foo"'),
      tuple(tuple(0.0, false, false), '0.0'),
      tuple(tuple(0.0 * -1.0, false, false), '-0.0'),
      tuple(tuple(1.0 / 3.0, false, false), '0.33333333333333'),
      tuple(tuple(0.000000000000000001, false, false), '1.0e-18'),
      tuple(tuple([], false, false), '[]'),
      tuple(tuple(['a' => 'foo'], false, false), '{"a":"foo"}'),
      tuple(tuple(['a' => 'foo'], false, true), "{\n    \"a\": \"foo\"\n}"),
      tuple(tuple(['foo'], false, false), '["foo"]'),
      tuple(tuple(['foo'], false, true), "[\n    \"foo\"\n]"),
      tuple(tuple(["ß"], false, false), '["ß"]'),
      tuple(tuple(["ß"], true, false), "[\"\xc3\x83\xc2\x9f\"]"),
    ];
  }
}

class TestJSONDecode extends SampleTest<(string, bool), mixed> {
  public function evaluate((string, bool) $in): mixed {
    return JSON::decode($in[0], $in[1]);
  }
  public function getData(): array<((string, bool), mixed)> {
    return [
      tuple(tuple('1', false), 1),
      tuple(tuple('2', false), 2),
      tuple(tuple('null', false), null),
      tuple(tuple('true', false), true),
      tuple(tuple('false', false), false),
      tuple(tuple('"foo"', false), "foo"),
      tuple(tuple('0.0', false), 0.0),
      tuple(tuple('-0.0', false), 0.0 * -1.0),
      tuple(tuple('0.33333333333333', false), 0.33333333333333),
      tuple(tuple('1.0e-18', false), 0.000000000000000001),
      tuple(tuple('[]', false), []),
      tuple(tuple('{"a":"foo"}', false), ['a' => 'foo']),
      tuple(tuple("{\n    \"a\": \"foo\"\n}", false), ['a' => 'foo']),
      tuple(tuple('["foo"]', false), ['foo']),
      tuple(tuple("[\n    \"foo\"\n]", false), ['foo']),
      tuple(tuple('["ß"]', false), ["ß"]),
      tuple(tuple("[\"\xc3\x83\xc2\x9f\"]", true), ["ß"]),
    ];
  }
}

class TestJSONError extends Test {
  public function run(): void {
    self::assertException(
      function() {
        JSON::encode(new \stdClass());
      },
      'Objects are not supported. Use an associative array.',
      \JSON_ERROR_UNSUPPORTED_TYPE,
    );
    self::assertException(
      function() {
        JSON::decode('this is not valid JSON');
      },
      'Syntax error',
      \JSON_ERROR_SYNTAX,
    );
  }
}

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
