<?hh // strict

namespace HackUtils;

class TestLeapYear extends SampleTest<int, bool> {
  public function evaluate(int $in): bool {
    return is_leap_year($in);
  }
  public function getData(): array<(int, bool)> {
    return [
      tuple(2016, true),
      tuple(2015, false),
      tuple(2000, true),
      tuple(2400, true),
      tuple(2401, false),
      tuple(2404, true),
      tuple(2500, false),
      tuple(2504, true),
      tuple(1900, false),
      tuple(2100, false),
      tuple(2104, true),
    ];
  }
}

function is_leap_year(int $y): bool {
  return ($y % 4 == 0) && (($y % 100 != 0) || ($y % 400 == 0));
}

class TestDaysInMonth extends SampleTest<(int, int), int> {
  public function evaluate((int, int) $in): int {
    return days_in_month($in[0], $in[1]);
  }
  public function getData(): array<((int, int), int)> {
    return [
      tuple(tuple(2016, 1), 31),
      tuple(tuple(2016, 2), 29),
      tuple(tuple(2016, 3), 31),
      tuple(tuple(2016, 4), 30),
      tuple(tuple(2016, 5), 31),
      tuple(tuple(2016, 6), 30),
      tuple(tuple(2016, 7), 31),
      tuple(tuple(2016, 8), 31),
      tuple(tuple(2016, 9), 30),
      tuple(tuple(2016, 10), 31),
      tuple(tuple(2016, 11), 30),
      tuple(tuple(2016, 12), 31),
      tuple(tuple(2015, 2), 28),
      tuple(tuple(2012, 2), 29),
    ];
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

class TestOverflowDateTime
  extends SampleTest<datetime_parts, datetime_parts> {
  public function evaluate(datetime_parts $in): datetime_parts {
    return overflow_datetime($in);
  }
  public function getData(): array<(datetime_parts, datetime_parts)> {
    return [
      tuple(
        tuple(2017, 1, 8, 17, 14, 6, 957416),
        tuple(2017, 1, 8, 17, 14, 6, 957416),
      ),
      tuple(
        tuple(2017, 1, 8, 17, 14, 6, 1957416),
        tuple(2017, 1, 8, 17, 14, 7, 957416),
      ),
      tuple(
        tuple(2017, 1, 8, 17, 14, 66, 957416),
        tuple(2017, 1, 8, 17, 15, 6, 957416),
      ),
      tuple(
        tuple(2017, 1, 8, 17, 74, 6, 957416),
        tuple(2017, 1, 8, 18, 14, 6, 957416),
      ),
      tuple(
        tuple(2017, 1, 8, 49, 14, 6, 957416),
        tuple(2017, 1, 10, 1, 14, 6, 957416),
      ),
      tuple(
        tuple(2017, 1, 40, 17, 14, 6, 957416),
        tuple(2017, 2, 9, 17, 14, 6, 957416),
      ),
      tuple(
        tuple(2017, 0, 8, 17, 14, 6, 957416),
        tuple(2016, 12, 8, 17, 14, 6, 957416),
      ),
      tuple(
        tuple(234, 234, -235, 1234, -2354, 123, -1274682),
        tuple(252, 11, 26, 18, 48, 1, 725318),
      ),
    ];
  }
}

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

class TestOverflowDate extends SampleTest<date_parts, date_parts> {
  public function evaluate(date_parts $in): date_parts {
    return overflow_date($in);
  }
  public function getData(): array<(date_parts, date_parts)> {
    return [
      tuple(tuple(2015, 1, 0), tuple(2014, 12, 31)),
      tuple(tuple(2015, 1, 365), tuple(2015, 12, 31)),
      tuple(tuple(2015, 2, 29), tuple(2015, 3, 1)),
      tuple(tuple(2016, 1, 366), tuple(2016, 12, 31)),
      tuple(tuple(2015, 13, 366), tuple(2016, 12, 31)),
      tuple(tuple(2016, 16, -31 - 28 - 31), tuple(2016, 12, 31)),
      tuple(tuple(2016, -3, 30 + 31 + 30 + 31 + 17), tuple(2016, 1, 17)),
      tuple(tuple(2016, -3, -8), tuple(2015, 8, 23)),
    ];
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

class TestValidDate extends SampleTest<date_parts, bool> {
  public function evaluate(date_parts $in): bool {
    return is_valid_date($in);
  }
  public function getData(): array<(date_parts, bool)> {
    return [
      tuple(tuple(2016, 2, 29), true),
      tuple(tuple(2015, 2, 29), false),
      tuple(tuple(2016, 11, 23), true),
      tuple(tuple(2016, 11, 30), true),
      tuple(tuple(2016, 11, 31), false),
      tuple(tuple(2016, 12, 31), true),
      tuple(tuple(2016, 12, 32), false),
      tuple(tuple(2016, 13, 31), false),
      tuple(tuple(2016, 0, 31), false),
      tuple(tuple(2016, -1, 31), false),
      tuple(tuple(2016, 1, 30), true),
      tuple(tuple(2016, 1, 0), false),
      tuple(tuple(2016, 1, -1), false),
      tuple(tuple(0, 1, 1), true),
      tuple(tuple(INT_MAX, 1, 1), true),
      tuple(tuple(INT_MIN, 1, 1), true),
    ];
  }
}

function is_valid_date(date_parts $date): bool {
  list($y, $m, $d) = $date;
  return $m >= 1 && $m <= 12 && $d >= 1 && $d <= days_in_month($y, $m);
}
