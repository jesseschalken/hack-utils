<?hh // strict

namespace HackUtils;

class DateTimeException extends \Exception {}
class DateTimeParseException extends DateTimeException {}
class DateTimeFormatException extends DateTimeException {}

/**
 * To hide the type wrapped by TimeZone.
 */
newtype _DateTimeZone = \DateTimeZone;

final class TimeZone {
  private static ?TimeZone $UTC;

  public static function UTC(): TimeZone {
    return self::$UTC ?: (self::$UTC = self::create('UTC'));
  }

  public static function create(string $tz): TimeZone {
    return new self(new \DateTimeZone($tz));
  }

  /**
   * Not to be used from outside this library.
   */
  public static function _wrap(_DateTimeZone $tz): TimeZone {
    return new self($tz);
  }

  private function __construct(private \DateTimeZone $tz) {}

  /**
   * Not to be used from outside this library.
   */
  public function _unwrap(): _DateTimeZone {
    return $this->tz;
  }
}

final class DateTime {
  const int PART_YEAR = 0;
  const int PART_MONTH = 1;
  const int PART_DAY = 2;
  const int PART_HOUR = 3;
  const int PART_MINUTE = 4;
  const int PART_SECOND = 5;
  const int PART_MICROSECOND = 6;

  public static function now(
    TimeZone $tz,
    bool $withMicrosecond = false,
  ): DateTime {
    if ($withMicrosecond) {
      $time = \gettimeofday();
      return self::fromTimestamp($time['sec'], $tz, $time['usec']);
    } else {
      return new self(new \DateTimeImmutable('now', $tz->_unwrap()));
    }
  }

  /**
   * Note all date parts default to 0. Year defaults to 1970.
   */
  public static function parse(
    string $format,
    string $string,
    TimeZone $tz,
  ): DateTime {
    $result = \DateTimeImmutable::createFromFormat(
      "!$format",
      $string,
      $tz->_unwrap(),
    );
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
      throw new DateTimeParseException(
        sprintf(
          'Could not parse date "%s" in format "%s": %s.',
          $string,
          $format,
          implode(', ', $message),
        ),
      );
    }
    return new self($result);
  }

  public static function fromTimestamp(
    int $sec,
    TimeZone $tz,
    int $usec = 0,
  ): DateTime {
    if ($usec) {
      list($sec, $usec) = self::overflowUsec($sec, $usec);
    }

    // 'new \DateTimeImmutable()' doesn't accept microseconds.
    if (!$usec) {
      // Easy mode, just instantiate DateTimeImmutable with our timestamp.
      $ret = new self(new \DateTimeImmutable('@'.$sec, $tz->_unwrap()));
    } else {
      $utc = TimeZone::UTC();
      // The only way we can get microseconds in is by parsing with the 'u'
      // specifier. It will be faster parsing in format 'U.u' than 'Y-m-d
      // H:i:s.u', but 'U' doesn't accept negative timestamps, so if the
      // timestamp is negative we still have to use 'Y-m-d H:i:s.u'.
      if ($sec >= 0) {
        $format = 'U';
        $string = $sec.'';
      } else {
        $format = 'Y-m-d H:i:s';
        $string = new self(new \DateTimeImmutable('@'.$sec, $utc->_unwrap()))
          ->format($format);
      }

      $ret =
        self::parse($format.'.u', $string.'.'.\sprintf('%06d', $usec), $utc);
    }

    // Because we specified a timestamp, the object will have timezone UTC
    // even though we provided a timezone, so now we have to set it.
    $ret = $ret->withTimezone($tz);

    return $ret;
  }

  public static function fromMicrotimestamp(
    int $usec,
    TimeZone $tz,
  ): DateTime {
    return self::fromTimestamp(0, $tz, $usec);
  }

  /**
   * Parts must be valid. Values do not overflow.
   */
  public static function fromParts(
    (int, int, int, int, int, int, int) $parts,
    TimeZone $tz,
  ): DateTime {
    return self::parse(
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

  private static function overflowUsec(int $sec, int $usec): (int, int) {
    $diff = \intdiv($usec, 1000000);
    $sec += $diff;
    $usec -= $diff * 1000000;
    if ($usec < 0) {
      $usec += 1000000;
      $sec -= 1;
    }
    return tuple($sec, $usec);
  }

  private function __construct(private \DateTimeImmutable $dt) {}

  public function format(string $format): string {
    $ret = $this->dt->format($format);
    if ($ret === false) {
      throw new DateTimeFormatException('DateTimeImmutable->format() failed');
    }
    return $ret;
  }

  public function getTimezone(): TimeZone {
    return TimeZone::_wrap($this->dt->getTimezone());
  }

  public function getTimestamp(): int {
    return $this->dt->getTimestamp();
  }

  public function getMicrosecond(): int {
    return (int) $this->format('u');
  }

  public function getMicrotimestamp(): int {
    return ($this->getTimestamp() * 1000000) + $this->getMicrosecond();
  }

  public function getUTCOffset(): int {
    return $this->dt->getOffset();
  }

  public function getParts(): (int, int, int, int, int, int, int) {
    $p = map(split($this->format('Y m d H i s u'), ' '), $x ==> (int) $x);
    return tuple($p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6]);
  }

  public function getPart(int $part): int {
    if ($part < 0 || $part > self::PART_MICROSECOND) {
      throw new \Exception('Invalid date/time part: '.$part);
    }
    $f = 'YmdHisu';
    return (int) $this->format($f[$part]);
  }

  public function withTimezone(TimeZone $tz): DateTime {
    return new self($this->dt->setTimezone($tz->_unwrap()));
  }

  public function withDate(int $year, int $month, int $day): DateTime {
    return new self($this->dt->setDate($year, $month, $day));
  }

  /**
   * Note that this doesn't set the microsecond!
   */
  public function withTime(int $hour, int $minute, int $second): DateTime {
    return new self($this->dt->setTime($hour, $minute, $second));
  }

  public function withMicrosecond(int $usec): DateTime {
    if ($this->getMicrosecond() != $usec) {
      return self::fromTimestamp(
        $this->getTimestamp(),
        $this->getTimezone(),
        $usec,
      );
    }
    return $this;
  }

  public function withTimestamp(int $sec, int $usec = 0): DateTime {
    if ($usec) {
      list($sec, $usec) = self::overflowUsec($sec, $usec);
    }
    return new self($this->dt->setTimestamp($sec))->withMicrosecond($usec);
  }

  public function withISODate(int $year, int $week, int $day): DateTime {
    return new self($this->dt->setISODate($year, $week, $day));
  }
}
