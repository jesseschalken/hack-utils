<?php
namespace HackUtils\DateTime {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils as HU;
  class Exception extends \Exception {}
  class ParseException extends Exception {}
  class FormatException extends Exception {}
  function new_timezone($tz) {
    return new \DateTimeZone($tz);
  }
  class _UTCTimeZone {
    public static $singleton = null;
  }
  function utc_timezone() {
    return _UTCTimeZone::$singleton ?: (_UTCTimeZone::$singleton =
                                          new_timezone("UTC"));
  }
  function parse($format, $string, $tz) {
    $result = \DateTimeImmutable::createFromFormat("!".$format, $string, $tz);
    $errors = \DateTimeImmutable::getLastErrors();
    if ($errors["warning_count"] ||
        $errors["error_count"] ||
        (!($result instanceof \DateTimeImmutable))) {
      $message = array();
      foreach ($errors["errors"] as $offset => $m) {
        $message[] = $m." at offset ".$offset;
      }
      foreach ($errors["warnings"] as $offset => $m) {
        $message[] = $m." at offset ".$offset;
      }
      throw new ParseException(
        sprintf(
          "Could not parse date \"%s\" in format \"%s\": %s.",
          $string,
          $format,
          implode(", ", $message)
        )
      );
    }
    return $result;
  }
  function format($dt, $format) {
    $ret = $dt->format($format);
    if ($ret === false) {
      throw new FormatException("DateTimeImmutable->format() failed");
    }
    return $ret;
  }
  function get_timezone($dt) {
    return $dt->getTimezone();
  }
  function get_timestamp($dt) {
    return $dt->getTimestamp();
  }
  function get_microsecond($dt) {
    return (int) format($dt, "u");
  }
  function get_microtimestamp($dt) {
    return get_microsecond($dt) + (get_timestamp($dt) * 1000000);
  }
  function get_utc_offset($dt) {
    return $dt->getOffset();
  }
  function from_microtimestamp($usec, $tz) {
    return from_timestamp(0, $tz, $usec);
  }
  function _overflow_usec($sec, $usec) {
    $diff = \intdiv($usec, 1000000);
    $sec += $diff;
    $usec -= $diff * 1000000;
    if ($usec < 0) {
      $usec += 1000000;
      $sec -= 1;
    }
    return array($sec, $usec);
  }
  function from_timestamp($sec, $tz, $usec = 0) {
    if ($usec) {
      list($sec, $usec) = _overflow_usec($sec, $usec);
    }
    if (!$usec) {
      $ret = new \DateTimeImmutable("@".$sec, $tz);
    } else {
      $utc = utc_timezone();
      if ($sec >= 0) {
        $format = "U";
        $string = $sec."";
      } else {
        $format = "Y-m-d H:i:s";
        $string = format(new \DateTimeImmutable("@".$sec, $utc), $format);
      }
      $ret = parse($format.".u", $string.".".\sprintf("%06d", $usec), $utc);
    }
    $ret = $ret->setTimezone($tz);
    return $ret;
  }
  function set_date($dt, $year, $month, $day) {
    return $dt->setDate($year, $month, $day);
  }
  function set_time($dt, $hour, $minute, $second) {
    return $dt->setTime($hour, $minute, $second);
  }
  function set_microsecond($dt, $microsecond) {
    if (get_microsecond($dt) != $microsecond) {
      $dt =
        from_timestamp(get_timestamp($dt), get_timezone($dt), $microsecond);
    }
    return $dt;
  }
  function set_timestamp($dt, $timestamp, $microsecond = 0) {
    $dt = $dt->setTimestamp($timestamp);
    $dt = set_microsecond($dt, $microsecond);
    return $dt;
  }
  function set_iso_date($dt, $year, $week, $day = 1) {
    return $dt->setISODate($year, $week, $day);
  }
  function set_timezone($dt, $tz) {
    return $dt->setTimezone($tz);
  }
  const PART_YEAR = 0;
  const PART_MONTH = 1;
  const PART_DAY = 2;
  const PART_HOUR = 3;
  const PART_MINUTE = 4;
  const PART_SECOND = 5;
  const PART_MICROSECOND = 6;
  function from_parts($parts, $tz) {
    return parse(
      "Y-m-d H:i:s.u",
      \sprintf(
        "%04d-%02d-%02d %02d:%02d:%02d.%06d",
        $parts[0],
        $parts[1],
        $parts[2],
        $parts[3],
        $parts[4],
        $parts[5],
        $parts[6]
      ),
      $tz
    );
  }
  function get_parts($dt) {
    $p = HU\map(
      HU\split(format($dt, "Y m d H i s u"), " "),
      function($x) {
        return (int) $x;
      }
    );
    return array($p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6]);
  }
  function get_part($dt, $part) {
    if (($part < 0) || ($part > PART_MICROSECOND)) {
      throw new \Exception("Invalid date/time part: ".$part);
    }
    $f = "YmdHisu";
    return (int) format($dt, $f[$part]);
  }
  function now($tz, $withMicroseconds = false) {
    if ($withMicroseconds) {
      $time = \gettimeofday();
      return from_timestamp($time["sec"], $tz, $time["usec"]);
    } else {
      return new \DateTimeImmutable("now", $tz);
    }
  }
}
