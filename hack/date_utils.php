<?hh // strict

namespace HackUtils;

class TestLeapYear extends Test {
  public function run(): void {
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

function is_leap_year(int $y): bool {
  return ($y % 4 == 0) && (($y % 100 != 0) || ($y % 400 == 0));
}

class TestDaysInMonth extends Test {
  public function run(): void {
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

function days_in_month(int $y, int $m): int {
  if ($m == 2 && is_leap_year($y))
    return 29;
  $l = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
  return $l[$m - 1];
}

type date_parts = (int, int, int);
type time_parts = (int, int, int, int);
type datetime_parts = (int, int, int, int, int, int, int);

function overflow_datetime(datetime_parts $datetime): datetime_parts {
  list($y, $m, $d, $h, $i, $s, $u) = $datetime;

  list($h, $i, $s, $u) = overflow_time(tuple($h, $i, $s, $u));
  list($d, $h) = div_mod2($d, $h, 24); // hours to days
  list($y, $m, $d) = overflow_date(tuple($y, $m, $d));

  return tuple($y, $m, $d, $h, $i, $s, $u);
}

function overflow_time(time_parts $time): time_parts {
  list($h, $i, $s, $u) = $time;
  list($s, $u) = div_mod2($s, $u, 1000000); // usecs to secs
  list($i, $s) = div_mod2($i, $s, 60); // secs to mins
  list($h, $i) = div_mod2($h, $i, 60); // mins to hours
  return tuple($h, $i, $s, $u);
}

class TestOverflowDate extends Test {
  public function run(): void {
    self::assertEqual(overflow_date(tuple(2015, 1, 0)), tuple(2014, 12, 31));
    self::assertEqual(
      overflow_date(tuple(2015, 1, 365)),
      tuple(2015, 12, 31),
    );
    self::assertEqual(overflow_date(tuple(2015, 2, 29)), tuple(2015, 3, 1));
    self::assertEqual(
      overflow_date(tuple(2016, 1, 366)),
      tuple(2016, 12, 31),
    );
    self::assertEqual(
      overflow_date(tuple(2015, 13, 366)),
      tuple(2016, 12, 31),
    );
    self::assertEqual(
      overflow_date(tuple(2016, 16, -31 - 28 - 31)),
      tuple(2016, 12, 31),
    );
    self::assertEqual(
      overflow_date(tuple(2016, -3, 30 + 31 + 30 + 31 + 17)),
      tuple(2016, 1, 17),
    );
    self::assertEqual(overflow_date(tuple(2016, -3, -8)), tuple(2015, 8, 23));
  }
}

function overflow_date(date_parts $date): date_parts {
  list($y, $m, $d) = $date;

  $m--;
  $d--;

  // months to years
  list($y, $m) = div_mod2($y, $m, 12);

  // days to months
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

  return tuple($y, $m, $d);
}

class TestValidDate extends Test {
  public function run(): void {
    self::assertEqual(is_valid_date(tuple(2016, 2, 29)), true);
    self::assertEqual(is_valid_date(tuple(2015, 2, 29)), false);
    self::assertEqual(is_valid_date(tuple(2016, 11, 23)), true);
    self::assertEqual(is_valid_date(tuple(2016, 11, 30)), true);
    self::assertEqual(is_valid_date(tuple(2016, 11, 31)), false);
    self::assertEqual(is_valid_date(tuple(2016, 12, 31)), true);
    self::assertEqual(is_valid_date(tuple(2016, 12, 32)), false);
    self::assertEqual(is_valid_date(tuple(2016, 13, 31)), false);
    self::assertEqual(is_valid_date(tuple(2016, 0, 31)), false);
    self::assertEqual(is_valid_date(tuple(2016, -1, 31)), false);
    self::assertEqual(is_valid_date(tuple(2016, 1, 30)), true);
    self::assertEqual(is_valid_date(tuple(2016, 1, 0)), false);
    self::assertEqual(is_valid_date(tuple(2016, 1, -1)), false);
    self::assertEqual(is_valid_date(tuple(0, 1, 1)), true);
    self::assertEqual(is_valid_date(tuple(INT_MAX, 1, 1)), true);
    self::assertEqual(is_valid_date(tuple(INT_MIN, 1, 1)), true);
  }
}

function is_valid_date(date_parts $date): bool {
  list($y, $m, $d) = $date;
  return $m >= 1 && $m <= 12 && $d >= 1 && $d <= days_in_month($y, $m);
}
