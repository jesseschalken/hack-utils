<?hh // strict

namespace HackUtils;

class TestDateTime extends Test {
  public function run(): void {
    $utc = TimeZone::UTC();
    $melb = TimeZone::create('Australia/Melbourne');
    self::assertEqual($utc->getName(), 'UTC');
    self::assertEqual($melb->getName(), 'Australia/Melbourne');

    $dt = DateTime::fromParts(tuple(2017, 1, 3, 22, 20, 8, 15648), $melb);
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
    self::assertEqual($dt->getTimezone()->getName(), 'Australia/Melbourne');

    $format = 'Y-m-d H:i:s.uP';
    self::assertEqual(
      $dt->format($format),
      '2017-01-03 22:20:08.015648+11:00',
    );
    self::assertEqual(
      $dt->withYear(826)->format($format),
      '0826-01-03 22:20:08.015648+10:00',
    );
    self::assertEqual(
      $dt->withMonth(15)->format($format),
      '2018-03-03 22:20:08.015648+11:00',
    );
    self::assertEqual(
      $dt->withDay(15)->format($format),
      '2017-01-15 22:20:08.015648+11:00',
    );

    self::assertEqual(
      $dt->withHour(-5)->format($format),
      '2017-01-02 19:20:08.015648+11:00',
    );
    self::assertEqual(
      $dt->withMinute(-5)->format($format),
      '2017-01-03 21:55:08.015648+11:00',
    );
    self::assertEqual(
      $dt->withSecond(-5)->format($format),
      '2017-01-03 22:19:55.015648+11:00',
    );
    self::assertEqual(
      $dt->withMicrosecond(-5)->format($format),
      '2017-01-03 22:20:07.999995+11:00',
    );

    self::assertEqual(
      $dt->withTimezone($utc)->format($format),
      '2017-01-03 11:20:08.015648+00:00',
    );
    self::assertEqual(
      $dt->withTimestamp(10 - 36000, 8623467)->format($format),
      '1970-01-01 00:00:18.623467+10:00',
    );
    self::assertEqual(
      $dt->withISODate(1984, -25, -8)->format($format),
      '1983-06-25 22:20:08.015648+10:00',
    );

    // Converting to microtimestamp and back should yield the same thing
    self::assertEqual(
      DateTime::fromMicrotimestamp(
        $dt->getMicrotimestamp(),
        $dt->getTimezone(),
      )->format($format),
      '2017-01-03 22:20:08.015648+11:00',
    );

    $dt2 = $dt->withDate(2017, 1, 1);
    self::assertEqual($dt2->getISOYear(), 2016);
    self::assertEqual($dt2->getISOWeek(), 52);
    self::assertEqual($dt2->getISOWeekday(), 7);

    $dt2 = $dt->withTimezone($utc);
    self::assertEqual($dt2->getParts(), tuple(2017, 1, 3, 11, 20, 8, 15648));
    self::assertEqual($dt2->getPart(DateTime::PART_HOUR), 11);
    self::assertEqual(
      self::getException(
        function() use ($dt2) {
          $dt2->getPart(543);
        },
      )->getMessage(),
      'Invalid date/time part: 543',
    );
    self::assertEqual($dt2->getUTCOffset(), 0);

    // Make sure we can get the current time with and without microseconds
    $nowNoUsec = DateTime::now($melb);
    $count = 0;
    do {
      $nowWithUsec = DateTime::now($melb, true);
      $count++;
      if ($count > 10)
        throw new \Exception('Cant get current time with micrseconds :(');
    } while (!$nowWithUsec->getMicrosecond());
    self::assertEqual($nowNoUsec->getMicrosecond(), 0);
    self::assertEqual(
      $nowWithUsec->withMicrosecond(0)->format($format),
      $nowNoUsec->format($format),
    );

    // withMicrosecond() on something that already has no microsecond
    // should yield the same thing
    self::assertEqual(
      $nowNoUsec->withMicrosecond(0)->format($format),
      $nowNoUsec->format($format),
    );

    self::assertEqual(
      DateTime::fuzzyParse('first sat of July 2015', $melb)->format($format),
      '2015-07-04 00:00:00.000000+10:00',
    );

    self::assertEqual(
      DateTime::fromTimestamp(-5 - 36000, $melb, -5)->format($format),
      '1969-12-31 23:59:54.999995+10:00',
    );

    // Test parse failure
    self::assertEqual(
      self::getException(
        function() use ($utc) {
          DateTime::parse('Y-m-d H:i:s', '', $utc);
        },
      )->getMessage(),
      'Could not parse date "" in format "Y-m-d H:i:s": Data missing at offset 0',
    );

    self::assertEqual(
      self::getException(
        function() use ($utc) {
          DateTime::fuzzyParse('99999999999999999', $utc);
        },
      )->getMessage(),
      'DateTimeImmutable::__construct(): Failed to parse time string (99999999999999999) at position 16 (9): Unexpected character',
    );
  }
}

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

  private function __construct(private _DateTimeZone $tz) {}

  public function getName(): string {
    return $this->tz->getName();
  }

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
    self::checkErrors($string, $format);
    return new self($result);
  }

  public static function fuzzyParse(string $string, TimeZone $tz): DateTime {
    $result = new \DateTimeImmutable($string, $tz->_unwrap());
    self::checkErrors($string, NULL_STRING);
    return new self($result);
  }

  private static function checkErrors(string $string, ?string $format): void {
    $errors = \DateTimeImmutable::getLastErrors();
    if ($errors['warning_count'] || $errors['error_count']) {
      $message = [];
      foreach ($errors['errors'] as $offset => $m) {
        $message[] = "$m at offset $offset";
      }
      foreach ($errors['warnings'] as $offset => $m) {
        $message[] = "$m at offset $offset";
      }
      $message = join($message, ', ');
      if ($format !== null) {
        $message =
          "Could not parse date \"$string\" in format \"$format\": $message";
      } else {
        $message = "Could not parse date \"$string\": $message";
      }
      throw new DateTimeParseException($message);
    }
  }

  public static function fromTimestamp(
    int $sec,
    TimeZone $tz,
    int $usec = 0,
  ): DateTime {
    if ($usec) {
      list($sec, $usec) = div_mod2($sec, $usec, 1000000);
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
        $string =
          (new self(new \DateTimeImmutable('@'.$sec, $utc->_unwrap())))
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

  public function getYear(): int {
    return (int) $this->format('Y');
  }

  public function getMonth(): int {
    return (int) $this->format('m');
  }

  public function getDay(): int {
    return (int) $this->format('d');
  }

  public function getHour(): int {
    return (int) $this->format('H');
  }

  public function getMinute(): int {
    return (int) $this->format('i');
  }

  public function getSecond(): int {
    return (int) $this->format('s');
  }

  public function getMicrosecond(): int {
    return (int) $this->format('u');
  }

  /**
   * Same as getYear() but if the Monday of the current week belongs to the
   * previous year, that year is returned instead.
   */
  public function getISOYear(): int {
    return (int) $this->format('o');
  }

  /**
   * The week of the year returned by getISOYear().
   */
  public function getISOWeek(): int {
    return (int) $this->format('W');
  }

  /**
   * 1 for Monday, 7 for Sunday
   */
  public function getISOWeekday(): int {
    return (int) $this->format('N');
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

  public function withYear(int $yaer): DateTime {
    return $this->withDate($yaer, $this->getMonth(), $this->getDay());
  }

  public function withMonth(int $month): DateTime {
    return $this->withDate($this->getYear(), $month, $this->getDay());
  }

  public function withDay(int $day): DateTime {
    return $this->withDate($this->getYear(), $this->getMonth(), $day);
  }

  public function withHour(int $hour): DateTime {
    return $this->withTime($hour, $this->getMinute(), $this->getSecond());
  }

  public function withMinute(int $minute): DateTime {
    return $this->withTime($this->getHour(), $minute, $this->getSecond());
  }

  public function withSecond(int $second): DateTime {
    return $this->withTime($this->getHour(), $this->getMinute(), $second);
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
      list($sec, $usec) = div_mod2($sec, $usec, 1000000);
    }

    return (new self($this->dt->setTimestamp($sec)))->withMicrosecond($usec);
  }

  public function withISODate(int $year, int $week, int $day): DateTime {
    return new self($this->dt->setISODate($year, $week, $day));
  }
}
