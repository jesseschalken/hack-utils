<?hh // strict

namespace HackUtils;

function is_leap_year(int $y): bool {
  return ($y % 4 == 0) && (($y % 100 != 0) || ($y % 400 == 0));
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

function is_valid_date(date_parts $date): bool {
  list($y, $m, $d) = $date;
  return $m >= 1 && $m <= 12 && $d >= 1 && $d <= days_in_month($y, $m);
}
