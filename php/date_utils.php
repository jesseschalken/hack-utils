<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class TestLeapYear extends SampleTest {
    public function evaluate($in) {
      return is_leap_year($in);
    }
    public function getData() {
      return array(
        array(2016, true),
        array(2015, false),
        array(2000, true),
        array(2400, true),
        array(2401, false),
        array(2404, true),
        array(2500, false),
        array(2504, true),
        array(1900, false),
        array(2100, false),
        array(2104, true)
      );
    }
  }
  function is_leap_year($y) {
    return (($y % 4) == 0) && ((($y % 100) != 0) || (($y % 400) == 0));
  }
  class TestDaysInMonth extends SampleTest {
    public function evaluate($in) {
      return days_in_month($in[0], $in[1]);
    }
    public function getData() {
      return array(
        array(array(2016, 1), 31),
        array(array(2016, 2), 29),
        array(array(2016, 3), 31),
        array(array(2016, 4), 30),
        array(array(2016, 5), 31),
        array(array(2016, 6), 30),
        array(array(2016, 7), 31),
        array(array(2016, 8), 31),
        array(array(2016, 9), 30),
        array(array(2016, 10), 31),
        array(array(2016, 11), 30),
        array(array(2016, 12), 31),
        array(array(2015, 2), 28),
        array(array(2012, 2), 29)
      );
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
  class TestOverflowDateTime extends SampleTest {
    public function evaluate($in) {
      return overflow_datetime($in);
    }
    public function getData() {
      return array(
        array(
          array(2017, 1, 8, 17, 14, 6, 957416),
          array(2017, 1, 8, 17, 14, 6, 957416)
        ),
        array(
          array(2017, 1, 8, 17, 14, 6, 1957416),
          array(2017, 1, 8, 17, 14, 7, 957416)
        ),
        array(
          array(2017, 1, 8, 17, 14, 66, 957416),
          array(2017, 1, 8, 17, 15, 6, 957416)
        ),
        array(
          array(2017, 1, 8, 17, 74, 6, 957416),
          array(2017, 1, 8, 18, 14, 6, 957416)
        ),
        array(
          array(2017, 1, 8, 49, 14, 6, 957416),
          array(2017, 1, 10, 1, 14, 6, 957416)
        ),
        array(
          array(2017, 1, 40, 17, 14, 6, 957416),
          array(2017, 2, 9, 17, 14, 6, 957416)
        ),
        array(
          array(2017, 0, 8, 17, 14, 6, 957416),
          array(2016, 12, 8, 17, 14, 6, 957416)
        ),
        array(
          array(234, 234, -235, 1234, -2354, 123, -1274682),
          array(252, 11, 26, 18, 48, 1, 725318)
        )
      );
    }
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
  class TestOverflowDate extends SampleTest {
    public function evaluate($in) {
      return overflow_date($in);
    }
    public function getData() {
      return array(
        array(array(2015, 1, 0), array(2014, 12, 31)),
        array(array(2015, 1, 365), array(2015, 12, 31)),
        array(array(2015, 2, 29), array(2015, 3, 1)),
        array(array(2016, 1, 366), array(2016, 12, 31)),
        array(array(2015, 13, 366), array(2016, 12, 31)),
        array(array(2016, 16, ((-31) - 28) - 31), array(2016, 12, 31)),
        array(array(2016, -3, 30 + 31 + 30 + 31 + 17), array(2016, 1, 17)),
        array(array(2016, -3, -8), array(2015, 8, 23))
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
  class TestValidDate extends SampleTest {
    public function evaluate($in) {
      return is_valid_date($in);
    }
    public function getData() {
      return array(
        array(array(2016, 2, 29), true),
        array(array(2015, 2, 29), false),
        array(array(2016, 11, 23), true),
        array(array(2016, 11, 30), true),
        array(array(2016, 11, 31), false),
        array(array(2016, 12, 31), true),
        array(array(2016, 12, 32), false),
        array(array(2016, 13, 31), false),
        array(array(2016, 0, 31), false),
        array(array(2016, -1, 31), false),
        array(array(2016, 1, 30), true),
        array(array(2016, 1, 0), false),
        array(array(2016, 1, -1), false),
        array(array(0, 1, 1), true),
        array(array(INT_MAX, 1, 1), true),
        array(array(INT_MIN, 1, 1), true)
      );
    }
  }
  function is_valid_date($date) {
    list($y, $m, $d) = $date;
    return
      ($m >= 1) && ($m <= 12) && ($d >= 1) && ($d <= days_in_month($y, $m));
  }
}
