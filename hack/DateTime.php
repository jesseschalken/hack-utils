<?hh // strict

namespace HackUtils\DateTime;

use HackUtils as HU;

newtype datetime = \DateTimeImmutable;
newtype timezone = \DateTimeZone;

class Exception extends \Exception {}
class ParseException extends Exception {}
class FormatException extends Exception {}

function new_timezone(string $tz): timezone {
  return new \DateTimeZone($tz);
}

class _UTCTimeZone {
  public static ?timezone $singleton = null;
}

function utc_timezone(): timezone {
  return _UTCTimeZone::$singleton ?: (_UTCTimeZone::$singleton =
                                        new_timezone('UTC'));
}

/**
 * Note all date parts default to 0. Year defaults to 1970.
 */
function parse(string $format, string $string, timezone $tz): datetime {
  $result = \DateTimeImmutable::createFromFormat("!$format", $string, $tz);
  $errors = \DateTimeImmutable::getLastErrors();
  if ($errors['warning_count'] ||
      $errors['error_count'] ||
      !($result instanceof \DateTimeImmutable)) {
    $message = [];
    foreach ($errors['errors'] as $offset => $m) {
      $message[] = "$m at offset $offset";
    }
    foreach ($errors['warnings'] as $offset => $m) {
      $message[] = "$m at offset $offset";
    }
    throw new ParseException(
      sprintf(
        'Could not parse date "%s" in format "%s": %s.',
        $string,
        $format,
        implode(', ', $message),
      ),
    );
  }
  return $result;
}

function format(datetime $dt, string $format): string {
  $ret = $dt->format($format);
  if ($ret === false) {
    throw new FormatException('DateTimeImmutable->format() failed');
  }
  return $ret;
}

function get_timezone(datetime $dt): timezone {
  return $dt->getTimezone();
}

function get_timestamp(datetime $dt): int {
  return $dt->getTimestamp();
}

function get_microsecond(datetime $dt): int {
  return (int) format($dt, 'u');
}

function get_microtimestamp(datetime $dt): int {
  return get_microsecond($dt) + get_timestamp($dt) * 1000000;
}

function get_utc_offset(datetime $dt): int {
  return $dt->getOffset();
}

function from_microtimestamp(int $usec, timezone $tz): datetime {
  return from_timestamp(0, $tz, $usec);
}

function _overflow_usec(int $sec, int $usec): (int, int) {
  $diff = \intdiv($usec, 1000000);
  $sec += $diff;
  $usec -= $diff * 1000000;
  if ($usec < 0) {
    $usec += 1000000;
    $sec -= 1;
  }
  return tuple($sec, $usec);
}

function from_timestamp(int $sec, timezone $tz, int $usec = 0): datetime {
  if ($usec) {
    list($sec, $usec) = _overflow_usec($sec, $usec);
  }

  // 'new \DateTimeImmutable()' doesn't accept microseconds.
  if (!$usec) {
    // Easy mode, just instantiate DateTimeImmutable with our timestamp.
    $ret = new \DateTimeImmutable('@'.$sec, $tz);
  } else {
    $utc = utc_timezone();
    // The only way we can get microseconds in is by parsing with the 'u'
    // specifier. It will be faster parsing in format 'U.u' than 'Y-m-d
    // H:i:s.u', but 'U' doesn't accept negative timestamps, so if the
    // timestamp is negative we still have to use 'Y-m-d H:i:s.u'.
    if ($sec >= 0) {
      $format = 'U';
      $string = $sec.'';
    } else {
      $format = 'Y-m-d H:i:s';
      $string = format(new \DateTimeImmutable('@'.$sec, $utc), $format);
    }

    $ret = parse($format.'.u', $string.'.'.\sprintf('%06d', $usec), $utc);
  }

  // Because we specified a timestamp, the object will have timezone UTC
  // even though we provided a timezone, so now we have to set it.
  $ret = $ret->setTimezone($tz);

  return $ret;
}

function set_date(datetime $dt, int $year, int $month, int $day): datetime {
  return $dt->setDate($year, $month, $day);
}

/**
 * Note that this doesn't set the microsecond!
 */
function set_time(
  datetime $dt,
  int $hour,
  int $minute,
  int $second,
): datetime {
  return $dt->setTime($hour, $minute, $second);
}

function set_microsecond(datetime $dt, int $microsecond): datetime {
  if (get_microsecond($dt) != $microsecond) {
    // If $microsecond < 0 or >= 1000000, from_timestamp() will do the
    // overflow for us.
    $dt = from_timestamp(get_timestamp($dt), get_timezone($dt), $microsecond);
  }
  return $dt;
}

function set_timestamp(
  datetime $dt,
  int $timestamp,
  int $microsecond = 0,
): datetime {
  $dt = $dt->setTimestamp($timestamp);
  $dt = set_microsecond($dt, $microsecond);
  return $dt;
}

function set_iso_date(
  datetime $dt,
  int $year,
  int $week,
  int $day = 1,
): datetime {
  return $dt->setISODate($year, $week, $day);
}

function set_timezone(datetime $dt, timezone $tz): datetime {
  return $dt->setTimezone($tz);
}

const int PART_YEAR = 0;
const int PART_MONTH = 1;
const int PART_DAY = 2;
const int PART_HOUR = 3;
const int PART_MINUTE = 4;
const int PART_SECOND = 5;
const int PART_MICROSECOND = 6;

type datetimeparts = (int, int, int, int, int, int, int);

/**
 * Parts must be valid. Values do not overflow.
 */
function from_parts(datetimeparts $parts, timezone $tz): datetime {
  return parse(
    'Y-m-d H:i:s.u',
    \sprintf(
      '%04d-%02d-%02d %02d:%02d:%02d.%06d',
      $parts[0],
      $parts[1],
      $parts[2],
      $parts[3],
      $parts[4],
      $parts[5],
      $parts[6],
    ),
    $tz,
  );
}

function get_parts(datetime $dt): datetimeparts {
  $p = HU\map(HU\split(format($dt, 'Y m d H i s u'), ' '), $x ==> (int) $x);
  return tuple($p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6]);
}

function get_part(datetime $dt, int $part): int {
  if ($part < 0 || $part > PART_MICROSECOND) {
    throw new \Exception('Invalid date/time part: '.$part);
  }
  $f = 'YmdHis';
  return (int) format($dt, $f[$part]);
}

function now(timezone $tz, bool $withMicroseconds = false): datetime {
  if ($withMicroseconds) {
    $time = \gettimeofday();
    return from_timestamp($time['sec'], $tz, $time['usec']);
  } else {
    return new \DateTimeImmutable('now', $tz);
  }
}
