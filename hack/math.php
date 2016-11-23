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

function abs<T as num>(T $x): T {
  return \abs($x);
}

function to_single_precision(float $x): float {
  $unpack = unpack('f', pack('f', $x));
  return $unpack[1];
}

function sign(num $x): int {
  return (int) ($x > 0) - (int) ($x < 0);
}

function is_finite(num $x): bool {
  return \is_int($x) || \is_finite($x);
}

function is_infinite(num $x): bool {
  return \is_float($x) && \is_infinite($x);
}

function is_nan(num $x): bool {
  return \is_float($x) && \is_nan($x);
}

/**
 * Returns true for the float -0.0.
 * Does not work on HHVM due to https://github.com/facebook/hhvm/issues/7425
 */
function signbit(float $x): bool {
  return $x < 0.0 || ($x == 0.0 && '-0' === (string) $x);
}

function ceil(float $x): float {
  return \ceil($x);
}

function floor(float $x): float {
  return \floor($x);
}

function trunc(float $x): float {
  return $x < 0.0 ? ceil($x) : floor($x);
}

function round_half_up(float $x): float {
  return floor($x + 0.5);
}

function round_half_down(float $x): float {
  return ceil($x - 0.5);
}

function round_half_to_zero(float $x): float {
  return $x > 0.0 ? round_half_down($x) : round_half_up($x);
}

function round_half_to_inf(float $x): float {
  return $x > 0.0 ? round_half_up($x) : round_half_down($x);
}

function round_half_to_even(float $x): float {
  $r = round_half_up($x);
  // If it was a tie break
  if (($r - $x) == 0.5) {
    // Round to the nearest even number
    $r = round_half_up($x / 2.0) * 2.0;
  }
  return $r;
}

function round_half_to_odd(float $x): float {
  $r = round_half_up($x);
  // If it was a tie break
  if (($r - $x) == 0.5) {
    // Round to the nearest odd number
    $r = round_half_up(($x - 1.0) / 2.0) * 2.0 + 1.0;
  }
  return $r;
}

function deg2rad(float $x): float {
  return \deg2rad($x);
}

function rad2deg(float $x): float {
  return \rad2deg($x);
}

function pow(float $base, float $exp): float {
  return \pow($base, $exp);
}

function exp(float $x): float {
  return \exp($x);
}

function expm1(float $x): float {
  return \expm1($x);
}

function log10(float $x): float {
  return \log10($x);
}

function log1p(float $x): float {
  return \log1p($x);
}

function log(float $x, float $base = E): float {
  return \log($x, $base);
}

function cos(float $x): float {
  return \cos($x);
}

function cosh(float $x): float {
  return \cosh($x);
}

function sin(float $x): float {
  return \sin($x);
}

function sinh(float $x): float {
  return \sinh($x);
}

function tan(float $x): float {
  return \tan($x);
}

function tanh(float $x): float {
  return \tanh($x);
}

function acos(float $x): float {
  return \acos($x);
}

function acosh(float $x): float {
  return \acosh($x);
}

function asin(float $x): float {
  return \asin($x);
}

function asinh(float $x): float {
  return \asinh($x);
}

function atan(float $x): float {
  return \atan($x);
}

function atanh(float $x): float {
  return \atanh($x);
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

function sqrt(float $x): float {
  return \sqrt($x);
}

function cmp(num $x, num $y): int {
  return sign($x - $y);
}

final class ToIntException extends \Exception {}

/**
 * Converts intish floats to ints. Useful for numeric operations that may
 * cause ints to overflow into floats.
 */
function to_int(num $x): int {
  if (\is_int($x))
    return $x;

  if (\is_float($x)) {
    $int = (int) $x;
    if ($int == $x)
      return $int;
    // For some reason (int)(float)PHP_INT_MAX == PHP_INT_MIN, so we have to
    // check that case explicitly.
    if ($x == \PHP_INT_MAX)
      return \PHP_INT_MAX;
    throw new ToIntException("Cannot convert float $x to int");
  }

  unreachable();
}

function quot(int $x, int $y): int {
  return \intdiv($x, $y);
}

function rem(int $x, int $y): int {
  return $x % $y;
}

function div(int $x, int $y): int {
  return to_int(($x - mod($x, $y)) / $y);
}

function mod(int $x, int $y): int {
  $r = $x % $y;
  if ($r && ($r < 0) != ($y < 0))
    $r += $y;
  return $r;
}

function div_mod(int $x, int $y): (int, int) {
  $r = mod($x, $y);
  return tuple(to_int(($x - $r) / $y), $r);
}

function quot_rem(int $x, int $y): (int, int) {
  $r = rem($x, $y);
  return tuple(to_int(($x - $r) / $y), $r);
}

function get_bit(int $int, int $offset): bool {
  return (bool) ((1 << $offset) & $int);
}

function set_bit(int $int, int $offset, bool $value): int {
  return $value ? $int | (1 << $offset) : $int & ~(1 << $offset);
}

function sum<T as num>(array<T> $array): T {
  return \array_sum($array);
}

function prod<T as num>(array<T> $array): T {
  return \array_product($array);
}
