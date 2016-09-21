<?php
namespace HackUtils\math {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  const PI = \M_PI;
  const E = \M_E;
  const NAN = \NAN;
  const INF = \INF;
  const INT_MIN = \PHP_INT_MIN;
  const INT_MAX = \PHP_INT_MAX;
  const INT_SIZE = \PHP_INT_SIZE;
  function min($a, $b) {
    return \min($a, $b);
  }
  function max($a, $b) {
    return \max($a, $b);
  }
  function abs($number) {
    return \abs($number);
  }
  function to_single_precision($f) {
    $unpack = unpack("f", pack("f", $f));
    return $unpack[1];
  }
  function sign($n) {
    return ((int) ($n > 0)) - ((int) ($n < 0));
  }
  function is_finite($val) {
    return
      \hacklib_cast_as_boolean(\is_int($val)) ||
      \hacklib_cast_as_boolean(\is_finite($val));
  }
  function is_infinite($val) {
    return
      \hacklib_cast_as_boolean(\is_float($val)) &&
      \hacklib_cast_as_boolean(\is_infinite($val));
  }
  function is_nan($val) {
    return
      \hacklib_cast_as_boolean(\is_float($val)) &&
      \hacklib_cast_as_boolean(\is_nan($val));
  }
  function is_negative($val) {
    return
      \hacklib_cast_as_boolean(\is_int($val))
        ? ($val < 0)
        : (($val < 0.0) || (($val === 0.0) && ("-0" === ((string) $val))));
  }
  function ceil($number) {
    return \ceil($number);
  }
  function floor($number) {
    return \floor($number);
  }
  function trunc($number) {
    return ($number < 0.0) ? ceil($number) : floor($number);
  }
  function deg2rad($number) {
    return \deg2rad($number);
  }
  function rad2deg($number) {
    return \rad2deg($number);
  }
  function pow($base, $exp) {
    return \pow($base, $exp);
  }
  function exp($arg) {
    return \exp($arg);
  }
  function expm1($arg) {
    return \expm1($arg);
  }
  function log10($arg) {
    return \log10($arg);
  }
  function log1p($number) {
    return \log1p($number);
  }
  function log($arg, $base = 0.0) {
    return \log($arg, $base);
  }
  function cos($arg) {
    return \cos($arg);
  }
  function cosh($arg) {
    return \cosh($arg);
  }
  function sin($arg) {
    return \sin($arg);
  }
  function sinh($arg) {
    return \sinh($arg);
  }
  function tan($arg) {
    return \tan($arg);
  }
  function tanh($arg) {
    return \tanh($arg);
  }
  function acos($arg) {
    return \acos($arg);
  }
  function acosh($arg) {
    return \acosh($arg);
  }
  function asin($arg) {
    return \asin($arg);
  }
  function asinh($arg) {
    return \asinh($arg);
  }
  function atan($arg) {
    return \atan($arg);
  }
  function atanh($arg) {
    return \atanh($arg);
  }
  function atan2($y, $x) {
    return \atan2($y, $x);
  }
  function hypot($x, $y) {
    return \hypot($x, $y);
  }
  function fmod($x, $y) {
    return \fmod($x, $y);
  }
  function sqrt($arg) {
    return \sqrt($arg);
  }
  function intdiv($numerator, $divisor) {
    return \intdiv($numerator, $divisor);
  }
  function sort($nums) {
    \sort($nums, \SORT_NUMERIC);
    return $nums;
  }
}
