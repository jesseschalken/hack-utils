<?php
namespace HackUtils\datetime {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\str;
  use \HackUtils\math;
  use \HackUtils\vector;
  use \HackUtils\map;
  use \HackUtils\set;
  use \HackUtils as HU;
  class Exception extends \Exception {}
  class ParseException extends Exception {}
  class FormatException extends Exception {}
  function create_timezone($tz) {
    return new \DateTimeZone($tz);
  }
  class _UTCTimeZone {
    public static $singleton = null;
  }
  function utc_timezone() {
    return
      clone (\hacklib_cast_as_boolean(_UTCTimeZone::$singleton) ?: (_UTCTimeZone::$singleton =
                                                                      create_timezone(
                                                                        "UTC"
                                                                      )));
  }
  function parse($format, $string, $tz) {
    $result = \DateTimeImmutable::createFromFormat("!".$format, $string, $tz);
    $errors = \DateTimeImmutable::getLastErrors();
    if (\hacklib_cast_as_boolean($errors[\hacklib_id("warning_count")]) ||
        \hacklib_cast_as_boolean($errors[\hacklib_id("error_count")]) ||
        (!($result instanceof \DateTimeImmutable))) {
      $message = array();
      foreach ($errors[\hacklib_id("errors")] as $offset => $m) {
        $message[] = $m." at offset ".$offset;
      }
      foreach ($errors[\hacklib_id("warnings")] as $offset => $m) {
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
    if (\hacklib_cast_as_boolean($usec)) {
      list($sec, $usec) = _overflow_usec($sec, $usec);
    }
    if (!\hacklib_cast_as_boolean($usec)) {
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
    if (\hacklib_not_equals(get_microsecond($dt), $microsecond)) {
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
  final class Part {
    const YEAR = 0;
    const MONTH = 1;
    const DAY = 2;
    const HOUR = 3;
    const MINUTE = 4;
    const SECOND = 5;
    const MICROSECOND = 6;
    private function __construct() {}
  }
  function from_parts($parts, $tz) {
    return parse(
      "Y-m-d H:i:s.u",
      \sprintf(
        "%04d-%02d-%02d %02d:%02d:%02d.%06d",
        $parts[Part::YEAR],
        $parts[Part::MONTH],
        $parts[Part::DAY],
        $parts[Part::HOUR],
        $parts[Part::MINUTE],
        $parts[Part::SECOND],
        $parts[Part::MICROSECOND]
      ),
      $tz
    );
  }
  function get_parts($dt) {
    list($year, $month, $day, $hour, $minute, $second, $microsecond) =
      HU\map(
        HU\split(format($dt, "Y m d H i s u"), " "),
        function($x) {
          return (int) $x;
        }
      );
    return array(
      Part::YEAR => $year,
      Part::MONTH => $month,
      Part::DAY => $day,
      Part::HOUR => $hour,
      Part::MINUTE => $minute,
      Part::SECOND => $second,
      Part::MICROSECOND => $microsecond
    );
  }
  function get_part($dt, $part) {
    $f = "YmdHis";
    return (int) format($dt, $f[$part]);
  }
  function now($tz, $withMicroseconds = false) {
    if (\hacklib_cast_as_boolean($withMicroseconds)) {
      $time = \gettimeofday();
      return from_timestamp(
        $time[\hacklib_id("sec")],
        $tz,
        $time[\hacklib_id("usec")]
      );
    } else {
      return new \DateTimeImmutable("now", $tz);
    }
  }
}
