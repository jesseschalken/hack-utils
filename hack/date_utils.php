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

type datetime_parts = (int, int, int, int, int, int, int);

function overflow_datetime(datetime_parts $parts): datetime_parts {
  list($y, $m, $d, $h, $i, $s, $u) = $parts;

  list($s, $u) = div_mod2($s, $u, 1000000); // usecs to secs
  list($i, $s) = div_mod2($i, $s, 60); // secs to mins
  list($h, $i) = div_mod2($h, $i, 60); // mins to hours
  list($d, $h) = div_mod2($d, $h, 24); // hours to days
  list($y, $m, $d) = overflow_date($y, $m, $d);

  return tuple($y, $m, $d, $h, $i, $s, $u);
}

function overflow_date(int $y, int $m, int $d): (int, int, int) {
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

function is_valid_date(int $y, int $m, int $d): bool {
  return $m >= 1 && $m <= 12 && $d >= 1 && $d <= days_in_month($y, $m);
}
