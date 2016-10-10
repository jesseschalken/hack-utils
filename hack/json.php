<?hh // strict

namespace HackUtils\json;

use HackUtils\fn1;

function encode(
  mixed $value,
  bool $binary = false,
  bool $pretty = false,
): string {
  $flags = 0;
  if (defined('JSON_PRETTY_PRINT') && $pretty) {
    $flags |= \JSON_PRETTY_PRINT;
  }
  if (defined('JSON_UNESCAPED_SLASHES')) {
    $flags |= \JSON_UNESCAPED_SLASHES;
  }
  if (defined('JSON_UNESCAPED_UNICODE')) {
    $flags |= \JSON_UNESCAPED_UNICODE;
  }
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
  _check_value($value);
  $json = \json_encode($value, $flags);
  _check_error();
  return $json;
}

function decode(string $json, bool $binary = false): mixed {
  $value = \json_decode($json, true);
  _check_error();
  _check_value($value);
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

function _check_value(mixed $x): void {
  if (\is_object($x) || \is_resource($x)) {
    throw new Exception(
      'Type is not supported',
      \JSON_ERROR_UNSUPPORTED_TYPE,
    );
  }
  if (\is_array($x)) {
    foreach ($x as $v) {
      _check_value($v);
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
      $r[$f($k.'')] = $f($v);
    }
    return $r;
  }
  return $x;
}

function _check_error(): void {
  if (\json_last_error() !== \JSON_ERROR_NONE) {
    throw new Exception(\json_last_error_msg(), \json_last_error());
  }
}

class Exception extends \Exception {}
