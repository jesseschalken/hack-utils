<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class TestDateTime extends Test {
    public function run() {
      $utc = TimeZone::UTC();
      $melb = TimeZone::create("Australia/Melbourne");
      self::assertEqual($utc->getName(), "UTC");
      self::assertEqual($melb->getName(), "Australia/Melbourne");
      $dt = DateTime::fromParts(array(2017, 1, 3, 22, 20, 8, 15648), $melb);
      self::assertEqual($dt->getYear(), 2017);
      self::assertEqual($dt->getMonth(), 1);
      self::assertEqual($dt->getDay(), 3);
      self::assertEqual($dt->getHour(), 22);
      self::assertEqual($dt->getMinute(), 20);
      self::assertEqual($dt->getSecond(), 8);
      self::assertEqual($dt->getMicrosecond(), 15648);
      self::assertEqual($dt->getTimestamp(), 1483442408);
      self::assertEqual($dt->getMicrotimestamp(), 1483442408015648);
      self::assertEqual($dt->getUTCOffset(), 39600);
      self::assertEqual($dt->getTimezone()->getName(), "Australia/Melbourne");
      $format = "Y-m-d H:i:s.uP";
      self::assertEqual(
        $dt->format($format),
        "2017-01-03 22:20:08.015648+11:00"
      );
      self::assertEqual(
        $dt->withYear(826)->format($format),
        "0826-01-03 22:20:08.015648+10:00"
      );
      self::assertEqual(
        $dt->withMonth(15)->format($format),
        "2018-03-03 22:20:08.015648+11:00"
      );
      self::assertEqual(
        $dt->withDay(15)->format($format),
        "2017-01-15 22:20:08.015648+11:00"
      );
      self::assertEqual(
        $dt->withHour(-5)->format($format),
        "2017-01-02 19:20:08.015648+11:00"
      );
      self::assertEqual(
        $dt->withMinute(-5)->format($format),
        "2017-01-03 21:55:08.015648+11:00"
      );
      self::assertEqual(
        $dt->withSecond(-5)->format($format),
        "2017-01-03 22:19:55.015648+11:00"
      );
      self::assertEqual(
        $dt->withMicrosecond(-5)->format($format),
        "2017-01-03 22:20:07.999995+11:00"
      );
      self::assertEqual(
        $dt->withTimezone($utc)->format($format),
        "2017-01-03 11:20:08.015648+00:00"
      );
      self::assertEqual(
        $dt->withTimestamp(10 - 36000, 8623467)->format($format),
        "1970-01-01 00:00:18.623467+10:00"
      );
      self::assertEqual(
        $dt->withISODate(1984, -25, -8)->format($format),
        "1983-06-25 22:20:08.015648+10:00"
      );
      self::assertEqual(
        DateTime::fromMicrotimestamp(
          $dt->getMicrotimestamp(),
          $dt->getTimezone()
        )->format($format),
        "2017-01-03 22:20:08.015648+11:00"
      );
      $dt2 = $dt->withDate(2017, 1, 1);
      self::assertEqual($dt2->getISOYear(), 2016);
      self::assertEqual($dt2->getISOWeek(), 52);
      self::assertEqual($dt2->getISOWeekday(), 7);
      $dt2 = $dt->withTimezone($utc);
      self::assertEqual(
        $dt2->getParts(),
        array(2017, 1, 3, 11, 20, 8, 15648)
      );
      self::assertEqual($dt2->getPart(DateTime::PART_HOUR), 11);
      self::assertEqual(
        self::getException(
          function() use ($dt2) {
            $dt2->getPart(543);
          }
        )->getMessage(),
        "Invalid date/time part: 543"
      );
      self::assertEqual($dt2->getUTCOffset(), 0);
      $nowNoUsec = DateTime::now($melb);
      $count = 0;
      do {
        $nowWithUsec = DateTime::now($melb, true);
        $count++;
        if ($count > 10) {
          throw new \Exception("Cant get current time with micrseconds :(");
        }
      } while (!\hacklib_cast_as_boolean($nowWithUsec->getMicrosecond()));
      self::assertEqual($nowNoUsec->getMicrosecond(), 0);
      self::assertEqual(
        $nowWithUsec->withMicrosecond(0)->format($format),
        $nowNoUsec->format($format)
      );
      self::assertEqual(
        $nowNoUsec->withMicrosecond(0)->format($format),
        $nowNoUsec->format($format)
      );
      self::assertEqual(
        DateTime::fuzzyParse("first sat of July 2015", $melb)
          ->format($format),
        "2015-07-04 00:00:00.000000+10:00"
      );
      self::assertEqual(
        DateTime::fromTimestamp((-5) - 36000, $melb, -5)->format($format),
        "1969-12-31 23:59:54.999995+10:00"
      );
      self::assertEqual(
        self::getException(
          function() use ($utc) {
            DateTime::parse("Y-m-d H:i:s", "", $utc);
          }
        )->getMessage(),
        "Could not parse date \"\" in format \"Y-m-d H:i:s\": Data missing at offset 0"
      );
      self::assertEqual(
        self::getException(
          function() use ($utc) {
            DateTime::fuzzyParse("99999999999999999", $utc);
          }
        )->getMessage(),
        "DateTimeImmutable::__construct(): Failed to parse time string (99999999999999999) at position 16 (9): Unexpected character"
      );
    }
  }
  class DateTimeException extends \Exception {}
  class DateTimeParseException extends DateTimeException {}
  class DateTimeFormatException extends DateTimeException {}
  final class TimeZone {
    private static $UTC;
    public static function UTC() {
      return \hacklib_cast_as_boolean(self::$UTC) ?: (self::$UTC =
                                                        self::create("UTC"));
    }
    public static function create($tz) {
      return new self(new \DateTimeZone($tz));
    }
    public static function _wrap($tz) {
      return new self($tz);
    }
    private $tz;
    private function __construct($tz) {
      $this->tz = $tz;
    }
    public function getName() {
      return $this->tz->getName();
    }
    public function _unwrap() {
      return $this->tz;
    }
  }
  final class DateTime {
    const PART_YEAR = 0;
    const PART_MONTH = 1;
    const PART_DAY = 2;
    const PART_HOUR = 3;
    const PART_MINUTE = 4;
    const PART_SECOND = 5;
    const PART_MICROSECOND = 6;
    public static function now($tz, $withMicrosecond = false) {
      if (\hacklib_cast_as_boolean($withMicrosecond)) {
        $time = \gettimeofday();
        return self::fromTimestamp(
          $time[\hacklib_id("sec")],
          $tz,
          $time[\hacklib_id("usec")]
        );
      } else {
        return new self(new \DateTimeImmutable("now", $tz->_unwrap()));
      }
    }
    public static function parse($format, $string, $tz) {
      $result = \DateTimeImmutable::createFromFormat(
        "!".$format,
        $string,
        $tz->_unwrap()
      );
      self::checkErrors($string, $format);
      return new self($result);
    }
    public static function fuzzyParse($string, $tz) {
      $result = new \DateTimeImmutable($string, $tz->_unwrap());
      self::checkErrors($string, NULL_STRING);
      return new self($result);
    }
    private static function checkErrors($string, $format) {
      $errors = \DateTimeImmutable::getLastErrors();
      if (\hacklib_cast_as_boolean($errors[\hacklib_id("warning_count")]) ||
          \hacklib_cast_as_boolean($errors[\hacklib_id("error_count")])) {
        $message = array();
        foreach ($errors[\hacklib_id("errors")] as $offset => $m) {
          $message[] = $m." at offset ".$offset;
        }
        foreach ($errors[\hacklib_id("warnings")] as $offset => $m) {
          $message[] = $m." at offset ".$offset;
        }
        $message = join($message, ", ");
        if ($format !== null) {
          $message =
            "Could not parse date \"".
            $string.
            "\" in format \"".
            $format.
            "\": ".
            $message;
        } else {
          $message = "Could not parse date \"".$string."\": ".$message;
        }
        throw new DateTimeParseException($message);
      }
    }
    public static function fromTimestamp($sec, $tz, $usec = 0) {
      if (\hacklib_cast_as_boolean($usec)) {
        list($sec, $usec) = div_mod2($sec, $usec, 1000000);
      }
      if (!\hacklib_cast_as_boolean($usec)) {
        $ret = new self(new \DateTimeImmutable("@".$sec, $tz->_unwrap()));
      } else {
        $utc = TimeZone::UTC();
        if ($sec >= 0) {
          $format = "U";
          $string = $sec."";
        } else {
          $format = "Y-m-d H:i:s";
          $string =
            \hacklib_id(
              new self(new \DateTimeImmutable("@".$sec, $utc->_unwrap()))
            )->format($format);
        }
        $ret = self::parse(
          $format.".u",
          $string.".".\sprintf("%06d", $usec),
          $utc
        );
      }
      $ret = $ret->withTimezone($tz);
      return $ret;
    }
    public static function fromMicrotimestamp($usec, $tz) {
      return self::fromTimestamp(0, $tz, $usec);
    }
    public static function fromParts($parts, $tz) {
      return self::parse(
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
    private $dt;
    private function __construct($dt) {
      $this->dt = $dt;
    }
    public function format($format) {
      $ret = $this->dt->format($format);
      if ($ret === false) {
        throw new DateTimeFormatException(
          "DateTimeImmutable->format() failed"
        );
      }
      return $ret;
    }
    public function getTimezone() {
      return TimeZone::_wrap($this->dt->getTimezone());
    }
    public function getTimestamp() {
      return $this->dt->getTimestamp();
    }
    public function getYear() {
      return (int) $this->format("Y");
    }
    public function getMonth() {
      return (int) $this->format("m");
    }
    public function getDay() {
      return (int) $this->format("d");
    }
    public function getHour() {
      return (int) $this->format("H");
    }
    public function getMinute() {
      return (int) $this->format("i");
    }
    public function getSecond() {
      return (int) $this->format("s");
    }
    public function getMicrosecond() {
      return (int) $this->format("u");
    }
    public function getISOYear() {
      return (int) $this->format("o");
    }
    public function getISOWeek() {
      return (int) $this->format("W");
    }
    public function getISOWeekday() {
      return (int) $this->format("N");
    }
    public function getMicrotimestamp() {
      return ($this->getTimestamp() * 1000000) + $this->getMicrosecond();
    }
    public function getUTCOffset() {
      return $this->dt->getOffset();
    }
    public function getParts() {
      $p = map(
        split($this->format("Y m d H i s u"), " "),
        function($x) {
          return (int) $x;
        }
      );
      return array($p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6]);
    }
    public function getPart($part) {
      if (($part < 0) || ($part > self::PART_MICROSECOND)) {
        throw new \Exception("Invalid date/time part: ".$part);
      }
      $f = "YmdHisu";
      return (int) $this->format($f[$part]);
    }
    public function withYear($yaer) {
      return $this->withDate($yaer, $this->getMonth(), $this->getDay());
    }
    public function withMonth($month) {
      return $this->withDate($this->getYear(), $month, $this->getDay());
    }
    public function withDay($day) {
      return $this->withDate($this->getYear(), $this->getMonth(), $day);
    }
    public function withHour($hour) {
      return $this->withTime($hour, $this->getMinute(), $this->getSecond());
    }
    public function withMinute($minute) {
      return $this->withTime($this->getHour(), $minute, $this->getSecond());
    }
    public function withSecond($second) {
      return $this->withTime($this->getHour(), $this->getMinute(), $second);
    }
    public function withTimezone($tz) {
      return new self($this->dt->setTimezone($tz->_unwrap()));
    }
    public function withDate($year, $month, $day) {
      return new self($this->dt->setDate($year, $month, $day));
    }
    public function withTime($hour, $minute, $second) {
      return new self($this->dt->setTime($hour, $minute, $second));
    }
    public function withMicrosecond($usec) {
      if (\hacklib_not_equals($this->getMicrosecond(), $usec)) {
        return self::fromTimestamp(
          $this->getTimestamp(),
          $this->getTimezone(),
          $usec
        );
      }
      return $this;
    }
    public function withTimestamp($sec, $usec = 0) {
      if (\hacklib_cast_as_boolean($usec)) {
        list($sec, $usec) = div_mod2($sec, $usec, 1000000);
      }
      return
        \hacklib_id(new self($this->dt->setTimestamp($sec)))
          ->withMicrosecond($usec);
    }
    public function withISODate($year, $week, $day) {
      return new self($this->dt->setISODate($year, $week, $day));
    }
  }
}
