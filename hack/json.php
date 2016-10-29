<?hh // strict

namespace HackUtils;

/**
 * Convert a value to JSON. Only accepts values that can be converted to their
 * exact original by json_decode(). So no resources, objects or INF, -INF or
 * NAN.
 *
 * Set $binary to TRUE to have strings interpereted as binary instead of UTF8.
 *
 * Throws a JSONException if the value cannot be converted to JSON.
 */
function json_encode(
  mixed $value,
  bool $binary = false,
  bool $pretty = false,
): string {
  $flags = 0;

  if (defined('JSON_PRETTY_PRINT') && $pretty) {
    $flags |= \JSON_PRETTY_PRINT;
  }

  // By default we shouldn't escape more than we need to.
  if (defined('JSON_UNESCAPED_SLASHES')) {
    $flags |= \JSON_UNESCAPED_SLASHES;
  }
  if (defined('JSON_UNESCAPED_UNICODE')) {
    $flags |= \JSON_UNESCAPED_UNICODE;
  }

  // No reason 0 floats should turn into 0 ints.
  if (defined('JSON_PRESERVE_ZERO_FRACTION')) {
    $flags |= \JSON_PRESERVE_ZERO_FRACTION;
  }

  if ($binary) {
    $value = _map_strings(
      $value,
      function($x) {
        return \utf8_encode($x);
      },
    );
  }

  _json_check_value($value);
  $json = \json_encode($value, $flags);
  _json_check_error();

  return $json;
}

/**
 * Parse JSON string. JSON objects are converted to associative arrays.
 * Set $binary to TRUE to produce binary strings instead of UTF8 (code points
 * >255 will be discarded).
 *
 * Throws a JSONException if the value string cannot be parsed.
 */
function json_decode(string $json, bool $binary = false): mixed {
  $value = \json_decode($json, true);
  _json_check_error();
  _json_check_value($value);

  if ($binary) {
    $value = _map_strings(
      $value,
      function($x) {
        return \utf8_decode($x);
      },
    );
  }

  return $value;
}

function _json_check_value(mixed $x): void {
  if (\is_object($x) || \is_resource($x)) {
    throw new JSONException(
      'Objects are not supported. Use an associative array.',
      \JSON_ERROR_UNSUPPORTED_TYPE,
    );
  }
  if (\is_array($x)) {
    foreach ($x as $v) {
      _json_check_value($v);
    }
  }
}

function _map_strings(mixed $x, (function(string): string) $f): mixed {
  if (\is_string($x)) {
    return $f($x);
  }
  if (\is_array($x)) {
    $r = [];
    foreach ($x as $k => $v) {
      $k = _map_strings($k, $f);
      $v = _map_strings($v, $f);
      $r[$k] = $v;
    }
    return $r;
  }
  return $x;
}

function _json_check_error(): void {
  if (\json_last_error() !== \JSON_ERROR_NONE) {
    throw new JSONException(\json_last_error_msg(), \json_last_error());
  }
}

class JSONException extends \Exception {}
