<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function is_leap_year($y) {
    return (($y % 4) == 0) && ((($y % 100) != 0) || (($y % 400) == 0));
  }
  final class _DaysInMonth {
    private static
      $months = array(31, -1, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    public static function get($y, $m) {
      if (($m < 1) || ($m > 12)) {
        throw new \Exception("Invalid month: ".$m);
      }
      return ($m == 2) ? (is_leap_year($y) ? 29 : 28) : self::$months[$m - 1];
    }
  }
  function days_in_month($y, $m) {
    return _DaysInMonth::get($y, $m);
  }
  function overflow_date($y, $m, $d) {
    $m--;
    $d--;
    list($y, $m) = div_mod2($y, $m, 12);
    while ($d < 0) {
      list($y, $m) = div_mod2($y, $m - 1, 12);
      $d += days_in_month($y, $m + 1);
    }
    while ($d >= ($t = days_in_month($y, $m + 1))) {
      $d -= $t;
      list($y, $m) = div_mod2($y, $m + 1, 12);
    }
    $m++;
    $d++;
    return array($y, $m, $d);
  }
  function is_valid_date($y, $m, $d) {
    return
      ($m >= 1) && ($m <= 12) && ($d >= 1) && ($d <= days_in_month($y, $m));
  }
}
