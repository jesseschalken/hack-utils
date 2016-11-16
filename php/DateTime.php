<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class DateTimeException extends \Exception {}
  class DateTimeParseException extends DateTimeException {}
  class DateTimeFormatException extends DateTimeException {}
  final class TimeZone {
    private static $UTC;
    public static function UTC() {
      return self::$UTC ?: (self::$UTC = self::create("UTC"));
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
      if ($withMicrosecond) {
        $time = \gettimeofday();
        return self::fromTimestamp($time["sec"], $tz, $time["usec"]);
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
      self::checkErrors($string, new_null());
      return new self($result);
    }
    private static function checkErrors($string, $format) {
      $errors = \DateTimeImmutable::getLastErrors();
      if ($errors["warning_count"] || $errors["error_count"]) {
        $message = array();
        foreach ($errors["errors"] as $offset => $m) {
          $message[] = $m." at offset ".$offset;
        }
        foreach ($errors["warnings"] as $offset => $m) {
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
      if ($usec) {
        list($sec, $usec) = self::overflowUsec($sec, $usec);
      }
      if (!$usec) {
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
    private static function overflowUsec($sec, $usec) {
      $diff = \intdiv($usec, 1000000);
      $sec += $diff;
      $usec -= $diff * 1000000;
      if ($usec < 0) {
        $usec += 1000000;
        $sec -= 1;
      }
      return array($sec, $usec);
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
      if ($this->getMicrosecond() != $usec) {
        return self::fromTimestamp(
          $this->getTimestamp(),
          $this->getTimezone(),
          $usec
        );
      }
      return $this;
    }
    public function withTimestamp($sec, $usec = 0) {
      if ($usec) {
        list($sec, $usec) = self::overflowUsec($sec, $usec);
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
