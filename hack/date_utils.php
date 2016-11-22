<?hh // strict

namespace HackUtils;

function is_leap_year(int $y): bool {
  return ($y % 4 == 0) && (($y % 100 != 0) || ($y % 400 == 0));
}

final class _DaysInMonth {
  private static array<int>
    $months = [31, -1, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

  public static function get(int $y, int $m): int {
    return $m == 2 ? (is_leap_year($y) ? 29 : 28) : self::$months[$m - 1];
  }
}

function days_in_month(int $y, int $m): int {
  return _DaysInMonth::get($y, $m);
}

function overflow_date(int $y, int $m, int $d): (int, int, int) {
  // underflow month
  for (; $m < 1; $y--, $m += 12) {
  }
  // overflow month
  for (; $m > 12; $y++, $m -= 12) {
  }
  // underflow day
  while ($d < 1) {
    // decrement month and underflow
    for ($m--; $m < 1; $y--, $m += 12) {
    }
    $d += days_in_month($y, $m);
  }
  // overflow day
  while ($d > ($t = days_in_month($y, $m))) {
    $d -= $t;
    // increment month and overflow
    for ($m++; $m > 12; $y++, $m -= 12) {
    }
  }
  return tuple($y, $m, $d);
}

function is_valid_date(int $y, int $m, int $d): bool {
  return $m >= 1 && $m <= 12 && $d >= 1 && $d <= days_in_month($y, $m);
}
