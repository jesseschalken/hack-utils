<?php
namespace HackUtils {
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
  function abs($x) {
    return \abs($x);
  }
  function to_single_precision($x) {
    $unpack = unpack("f", pack("f", $x));
    return $unpack[1];
  }
  function sign($x) {
    return ((int) ($x > 0)) - ((int) ($x < 0));
  }
  function is_finite($x) {
    return \is_int($x) || \is_finite($x);
  }
  function is_infinite($x) {
    return \is_float($x) && \is_infinite($x);
  }
  function is_nan($x) {
    return \is_float($x) && \is_nan($x);
  }
  function signbit($x) {
    return ($x < 0) || ((!$x) && \is_float($x) && ("-0" === ((string) $x)));
  }
  function ceil($x) {
    return \ceil($x);
  }
  function floor($x) {
    return \floor($x);
  }
  function trunc($x) {
    return ($x < 0.0) ? ceil($x) : floor($x);
  }
  function round_half_up($x) {
    return floor($x + 0.5);
  }
  function round_half_down($x) {
    return ceil($x - 0.5);
  }
  function round_half_to_zero($x) {
    return ($x > 0.0) ? round_half_down($x) : round_half_up($x);
  }
  function round_half_to_inf($x) {
    return ($x > 0.0) ? round_half_up($x) : round_half_down($x);
  }
  function round_half_to_even($x) {
    $r = round_half_up($x);
    if (($r - $x) == 0.5) {
      $r = round_half_up($x / 2.0) * 2.0;
    }
    return $r;
  }
  function round_half_to_odd($x) {
    $r = round_half_up($x);
    if (($r - $x) == 0.5) {
      $r = (round_half_up(($x - 1.0) / 2.0) * 2.0) + 1.0;
    }
    return $r;
  }
  function deg2rad($x) {
    return \deg2rad($x);
  }
  function rad2deg($x) {
    return \rad2deg($x);
  }
  function pow($base, $exp) {
    return \pow($base, $exp);
  }
  function exp($x) {
    return \exp($x);
  }
  function expm1($x) {
    return \expm1($x);
  }
  function log10($x) {
    return \log10($x);
  }
  function log1p($x) {
    return \log1p($x);
  }
  function log($x, $base = E) {
    return \log($x, $base);
  }
  function cos($x) {
    return \cos($x);
  }
  function cosh($x) {
    return \cosh($x);
  }
  function sin($x) {
    return \sin($x);
  }
  function sinh($x) {
    return \sinh($x);
  }
  function tan($x) {
    return \tan($x);
  }
  function tanh($x) {
    return \tanh($x);
  }
  function acos($x) {
    return \acos($x);
  }
  function acosh($x) {
    return \acosh($x);
  }
  function asin($x) {
    return \asin($x);
  }
  function asinh($x) {
    return \asinh($x);
  }
  function atan($x) {
    return \atan($x);
  }
  function atanh($x) {
    return \atanh($x);
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
  function sqrt($x) {
    return \sqrt($x);
  }
  function cmp($x, $y) {
    return sign($x - $y);
  }
  function intdiv($numerator, $divisor) {
    return \intdiv($numerator, $divisor);
  }
  function intpow($base, $exp) {
    if ($exp < 0) {
      throw new \Exception("Exponent must not be < 0");
    }
    return \pow($base, $exp);
  }
  function sum($array) {
    return \array_sum($array);
  }
  function product($array) {
    return \array_product($array);
  }
}
