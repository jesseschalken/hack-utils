<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function is_leap_year($y) {
    return (($y % 4) == 0) && ((($y % 100) != 0) || (($y % 400) == 0));
  }
  function days_in_month($y, $m) {
    if (($m == 2) && is_leap_year($y)) {
      return 29;
    }
    $l = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    return $l[$m - 1];
  }
  function overflow_datetime($parts) {
    list($y, $m, $d, $h, $i, $s, $u) = $parts;
    list($s, $u) = div_mod2($s, $u, 1000000);
    list($i, $s) = div_mod2($i, $s, 60);
    list($h, $i) = div_mod2($h, $i, 60);
    list($d, $h) = div_mod2($d, $h, 24);
    list($y, $m, $d) = overflow_date($y, $m, $d);
    return array($y, $m, $d, $h, $i, $s, $u);
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
