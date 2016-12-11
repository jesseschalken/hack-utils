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
    return ($x < 0.0) || (($x == 0.0) && ("-0" === ((string) $x)));
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
  function frac($x) {
    return $x - trunc($x);
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
  final class ToIntException extends \Exception {}
  function to_int($x) {
    if (\is_int($x)) {
      return $x;
    }
    if (\is_float($x)) {
      $int = (int) $x;
      if ($int == $x) {
        return $int;
      }
      if ($x == \PHP_INT_MAX) {
        return \PHP_INT_MAX;
      }
      throw new ToIntException("Cannot convert float ".$x." to int");
    }
    unreachable();
  }
  function quot($n, $d) {
    return \intdiv($n, $d);
  }
  function rem($n, $d) {
    return $n % $d;
  }
  function div($n, $d) {
    return to_int(($n - mod($n, $d)) / $d);
  }
  function mod($n, $d) {
    $r = $n % $d;
    if ($r && (($r < 0) != ($d < 0))) {
      $r += $d;
    }
    return $r;
  }
  function div_mod($n, $d) {
    $r = mod($n, $d);
    return array(to_int(($n - $r) / $d), $r);
  }
  function quot_rem($n, $d) {
    $r = rem($n, $d);
    return array(to_int(($n - $r) / $d), $r);
  }
  function div_mod2($x, $n, $d) {
    $ret = div_mod($n, $d);
    $ret[0] += $x;
    return $ret;
  }
  function get_bit($int, $offset) {
    return (bool) ((1 << $offset) & $int);
  }
  function set_bit($int, $offset, $value) {
    if ($value) {
      $int |= 1 << $offset;
    } else {
      $int &= ~(1 << $offset);
    }
    return $int;
  }
  function sum($array) {
    return \array_sum($array);
  }
  function prod($array) {
    return \array_product($array);
  }
}
