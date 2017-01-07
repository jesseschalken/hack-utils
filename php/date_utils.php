<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class TestLeapYear extends Test {
    public function run() {
      self::assertEqual(is_leap_year(2016), true);
      self::assertEqual(is_leap_year(2015), false);
      self::assertEqual(is_leap_year(2000), true);
      self::assertEqual(is_leap_year(2400), true);
      self::assertEqual(is_leap_year(2401), false);
      self::assertEqual(is_leap_year(2404), true);
      self::assertEqual(is_leap_year(2500), false);
      self::assertEqual(is_leap_year(2504), true);
      self::assertEqual(is_leap_year(1900), false);
      self::assertEqual(is_leap_year(2100), false);
      self::assertEqual(is_leap_year(2104), true);
    }
  }
  function is_leap_year($y) {
    return (($y % 4) == 0) && ((($y % 100) != 0) || (($y % 400) == 0));
  }
  class TestDaysInMonth extends Test {
    public function run() {
      self::assertEqual(days_in_month(2016, 1), 31);
      self::assertEqual(days_in_month(2016, 2), 29);
      self::assertEqual(days_in_month(2016, 3), 31);
      self::assertEqual(days_in_month(2016, 4), 30);
      self::assertEqual(days_in_month(2016, 5), 31);
      self::assertEqual(days_in_month(2016, 6), 30);
      self::assertEqual(days_in_month(2016, 7), 31);
      self::assertEqual(days_in_month(2016, 8), 31);
      self::assertEqual(days_in_month(2016, 9), 30);
      self::assertEqual(days_in_month(2016, 10), 31);
      self::assertEqual(days_in_month(2016, 11), 30);
      self::assertEqual(days_in_month(2016, 12), 31);
      self::assertEqual(days_in_month(2015, 2), 28);
      self::assertEqual(days_in_month(2012, 2), 29);
    }
  }
  function days_in_month($y, $m) {
    if (\hacklib_equals($m, 2) &&
        \hacklib_cast_as_boolean(is_leap_year($y))) {
      return 29;
    }
    $l = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    return $l[$m - 1];
  }
  function overflow_datetime($datetime) {
    list($y, $m, $d, $h, $i, $s, $u) = $datetime;
    list($h, $i, $s, $u) = overflow_time(array($h, $i, $s, $u));
    list($d, $h) = div_mod2($d, $h, 24);
    list($y, $m, $d) = overflow_date(array($y, $m, $d));
    return array($y, $m, $d, $h, $i, $s, $u);
  }
  function overflow_time($time) {
    list($h, $i, $s, $u) = $time;
    list($s, $u) = div_mod2($s, $u, 1000000);
    list($i, $s) = div_mod2($i, $s, 60);
    list($h, $i) = div_mod2($h, $i, 60);
    return array($h, $i, $s, $u);
  }
  class TestOverflowDate extends Test {
    public function run() {
      self::assertEqual(
        overflow_date(array(2015, 1, 0)),
        array(2014, 12, 31)
      );
      self::assertEqual(
        overflow_date(array(2015, 1, 365)),
        array(2015, 12, 31)
      );
      self::assertEqual(overflow_date(array(2015, 2, 29)), array(2015, 3, 1));
      self::assertEqual(
        overflow_date(array(2016, 1, 366)),
        array(2016, 12, 31)
      );
      self::assertEqual(
        overflow_date(array(2015, 13, 366)),
        array(2016, 12, 31)
      );
      self::assertEqual(
        overflow_date(array(2016, 16, ((-31) - 28) - 31)),
        array(2016, 12, 31)
      );
      self::assertEqual(
        overflow_date(array(2016, -3, 30 + 31 + 30 + 31 + 17)),
        array(2016, 1, 17)
      );
      self::assertEqual(
        overflow_date(array(2016, -3, -8)),
        array(2015, 8, 23)
      );
    }
  }
  function overflow_date($date) {
    list($y, $m, $d) = $date;
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
  class TestValidDate extends Test {
    public function run() {
      self::assertEqual(is_valid_date(array(2016, 2, 29)), true);
      self::assertEqual(is_valid_date(array(2015, 2, 29)), false);
      self::assertEqual(is_valid_date(array(2016, 11, 23)), true);
      self::assertEqual(is_valid_date(array(2016, 11, 30)), true);
      self::assertEqual(is_valid_date(array(2016, 11, 31)), false);
      self::assertEqual(is_valid_date(array(2016, 12, 31)), true);
      self::assertEqual(is_valid_date(array(2016, 12, 32)), false);
      self::assertEqual(is_valid_date(array(2016, 13, 31)), false);
      self::assertEqual(is_valid_date(array(2016, 0, 31)), false);
      self::assertEqual(is_valid_date(array(2016, -1, 31)), false);
      self::assertEqual(is_valid_date(array(2016, 1, 30)), true);
      self::assertEqual(is_valid_date(array(2016, 1, 0)), false);
      self::assertEqual(is_valid_date(array(2016, 1, -1)), false);
      self::assertEqual(is_valid_date(array(0, 1, 1)), true);
      self::assertEqual(is_valid_date(array(INT_MAX, 1, 1)), true);
      self::assertEqual(is_valid_date(array(INT_MIN, 1, 1)), true);
    }
  }
  function is_valid_date($date) {
    list($y, $m, $d) = $date;
    return
      ($m >= 1) && ($m <= 12) && ($d >= 1) && ($d <= days_in_month($y, $m));
  }
}
