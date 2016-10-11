<?hh // strict

namespace HackUtils;

const float PI = \M_PI;
const float E = \M_E;
const float NAN = \NAN;
const float INF = \INF;

const int INT_MIN = \PHP_INT_MIN;
const int INT_MAX = \PHP_INT_MAX;
const int INT_SIZE = \PHP_INT_SIZE;

function min<T as num>(T $a, T $b): T {
  return \min($a, $b);
}

function max<T as num>(T $a, T $b): T {
  return \max($a, $b);
}

function abs<T as num>(T $number): T {
  return \abs($number);
}

function to_single_precision(float $f): float {
  $unpack = unpack('f', pack('f', $f));
  return $unpack[1];
}

function sign(num $n): int {
  return (int) ($n > 0) - (int) ($n < 0);
}

function is_finite(num $val): bool {
  return \is_int($val) || \is_finite($val);
}

function is_infinite(num $val): bool {
  return \is_float($val) && \is_infinite($val);
}

function is_nan(num $val): bool {
  return \is_float($val) && \is_nan($val);
}

/**
 * Returns true for the float -0.0.
 */
function is_negative(num $val): bool {
  return
    \is_int($val)
      ? ($val < 0)
      : ($val < 0.0 || ($val === 0.0 && '-0' === (string) $val));
}

function ceil(float $number): float {
  return \ceil($number);
}

function floor(float $number): float {
  return \floor($number);
}

function trunc(float $number): float {
  return $number < 0.0 ? ceil($number) : floor($number);
}

// function round(
//   double $val,
//   int $precision = 0,
//   int $mode = PHP_ROUND_HALF_UP,
// ): php_math_round;

function deg2rad(float $number): float {
  return \deg2rad($number);
}

function rad2deg(float $number): float {
  return \rad2deg($number);
}

function pow(float $base, float $exp): float {
  return \pow($base, $exp);
}

function exp(float $arg): float {
  return \exp($arg);
}

function expm1(float $arg): float {
  return \expm1($arg);
}

function log10(float $arg): float {
  return \log10($arg);
}

function log1p(float $number): float {
  return \log1p($number);
}

function log(float $arg, float $base = 0.0): float {
  return \log($arg, $base);
}

function cos(float $arg): float {
  return \cos($arg);
}

function cosh(float $arg): float {
  return \cosh($arg);
}

function sin(float $arg): float {
  return \sin($arg);
}

function sinh(float $arg): float {
  return \sinh($arg);
}

function tan(float $arg): float {
  return \tan($arg);
}

function tanh(float $arg): float {
  return \tanh($arg);
}

function acos(float $arg): float {
  return \acos($arg);
}

function acosh(float $arg): float {
  return \acosh($arg);
}

function asin(float $arg): float {
  return \asin($arg);
}

function asinh(float $arg): float {
  return \asinh($arg);
}

function atan(float $arg): float {
  return \atan($arg);
}

function atanh(float $arg): float {
  return \atanh($arg);
}

function atan2(float $y, float $x): float {
  return \atan2($y, $x);
}

function hypot(float $x, float $y): float {
  return \hypot($x, $y);
}

function fmod(float $x, float $y): float {
  return \fmod($x, $y);
}

function sqrt(float $arg): float {
  return \sqrt($arg);
}

function cmp<T as num>(T $x, T $y): int {
  return sign($x - $y);
}

function intdiv(int $numerator, int $divisor): int {
  return \intdiv($numerator, $divisor);
}

function intpow(int $base, int $exp): int {
  if ($exp < 0) {
    throw new \Exception('Exponent must not be < 0');
  }
  return \pow($base, $exp);
}

function sort_numeric<T as num>(
  array<T> $nums,
  bool $reverse = false,
): array<T> {
  if ($reverse) {
    \rsort($nums, \SORT_NUMERIC);
  } else {
    \sort($nums, \SORT_NUMERIC);
  }
  return $nums;
}

function sort_values_numeric<Tk, Tv as num>(
  array<Tk, Tv> $nums,
  bool $reverse = false,
): array<Tk, Tv> {
  if ($reverse) {
    \arsort($nums, \SORT_NUMERIC);
  } else {
    \asort($nums, \SORT_NUMERIC);
  }
  return $nums;
}

function sort_keys_numeric<Tk as num, Tv>(
  array<Tk, Tv> $map,
  bool $reverse = false,
): array<Tk, Tv> {
  if ($reverse) {
    \krsort($map, \SORT_NUMERIC);
  } else {
    \ksort($map, \SORT_NUMERIC);
  }
  return $map;
}

function sum<T as num>(array<T> $list): T {
  return \array_sum($list);
}

function product<T as num>(array<T> $list): T {
  return \array_product($list);
}
