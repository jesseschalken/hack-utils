<?hh // strict

namespace HackUtils;

final class _Tests {
  private static function assertEqual(mixed $actual, mixed $expected): void {
    if (!self::isEqual($actual, $expected)) {
      throw new \Exception(
        \sprintf(
          "Expected %s, got %s",
          \var_export($expected, true),
          \var_export($actual, true),
        ),
      );
    }
  }

  private static function isEqual(mixed $a, mixed $b): bool {
    if (\is_float($a) && \is_float($b)) {
      // Consider NAN as equal to itself (PHP doesn't)
      if (\is_nan($a) && \is_nan($b))
        return true;
      // Don't consider -0.0 and +0.0 as equal (PHP does)
      if (signbit($a) != signbit($b))
        return false;
    }

    if (\is_array($a) && \is_array($b)) {
      if (\count($a) !== \count($b))
        return false;
      // Iterate over both arrays in parallel
      $iterA = new ArrayIterator($a);
      $iterB = new ArrayIterator($b);
      for (
        $iterA->reset(), $iterB->reset();
        $iterA->valid() || $iterB->valid();
        $iterA->next(), $iterB->next()
      ) {
        if (!self::isEqual($iterA->key(), $iterB->key()) ||
            !self::isEqual($iterA->current(), $iterB->current())) {
          return false;
        }
      }
      return true;
    }

    return $a === $b;
  }

  private static function getException((function(): void) $f): \Exception {
    try {
      $f();
    } catch (\Exception $e) {
      return $e;
    }
    throw new \Exception('Code was supposed to throw but didnt');
  }

  private static function testAssertions(): void {
    self::assertEqual(
      self::getException(
        function() {
          _Tests::assertEqual([], [1]);
        },
      )->getMessage(),
      "Expected array (\n  0 => 1,\n), got array (\n)",
    );

    self::assertEqual(
      self::getException(
        function() {
          _Tests::assertEqual([2], [1]);
        },
      )->getMessage(),
      "Expected array (\n  0 => 1,\n), got array (\n  0 => 2,\n)",
    );

    self::assertEqual(
      self::getException(
        function() {
          _Tests::assertEqual([0.0], [0.0 * -1.0]);
        },
      )->getMessage(),
      "Expected array (\n  0 => -0.0,\n), got array (\n  0 => 0.0,\n)",
    );

    self::assertEqual(
      self::getException(
        function() {
          _Tests::assertEqual(0.0, 0.0 * -1.0);
        },
      )->getMessage(),
      'Expected -0.0, got 0.0',
    );

    self::assertEqual(
      self::getException(
        function() {
          _Tests::getException(function() {});
        },
      )->getMessage(),
      'Code was supposed to throw but didnt',
    );
  }

  public static function main(): void {
    self::testAssertions();

    self::log('to_hex');
    self::assertEqual(to_hex("\x00\xff\x20"), "00ff20");

    self::log('from_hex');
    self::assertEqual(from_hex("00ff20"), "\x00\xff\x20");
    self::assertEqual(from_hex("00Ff20"), "\x00\xff\x20");

    self::log('shuffle_string');
    self::assertEqual(length(shuffle_string("abc")), 3);

    self::log('reverse_string');
    self::assertEqual(reverse_string("abc"), 'cba');
    self::assertEqual(reverse_string(""), '');

    self::log('to_lower');
    self::assertEqual(to_lower("ABC.1.2.3"), "abc.1.2.3");

    self::log('to_upper');
    self::assertEqual(to_upper("abc.1.2.3"), "ABC.1.2.3");

    self::log('split');
    self::assertEqual(split(''), []);
    self::assertEqual(split('a'), ['a']);
    self::assertEqual(split('abc'), ['a', 'b', 'c']);

    self::assertEqual(split('', '', 1), []);
    self::assertEqual(split('a', '', 1), ['a']);
    self::assertEqual(split('abc', '', 1), ['abc']);
    self::assertEqual(split('abc', '', 2), ['a', 'bc']);
    self::assertEqual(split('abc', '', 3), ['a', 'b', 'c']);

    self::assertEqual(split('', 'b'), ['']);
    self::assertEqual(split('abc', 'b'), ['a', 'c']);

    self::assertEqual(split('abc', 'b', 1), ['abc']);
    self::assertEqual(split('abc', 'b', 2), ['a', 'c']);

    self::log('chunk_string');
    self::assertEqual(chunk_string('abc', 1), ['a', 'b', 'c']);
    self::assertEqual(chunk_string('abc', 2), ['ab', 'c']);
    self::assertEqual(chunk_string('abc', 3), ['abc']);

    self::log('join');
    self::assertEqual(join([]), '');
    self::assertEqual(join(['abc']), 'abc');
    self::assertEqual(join(['a', 'bc']), 'abc');

    self::assertEqual(join([], ','), '');
    self::assertEqual(join(['abc'], ','), 'abc');
    self::assertEqual(join(['a', 'bc'], ','), 'a,bc');

    self::log('replace_count');
    self::assertEqual(replace_count('abc', 'b', 'lol'), tuple('alolc', 1));
    self::assertEqual(replace_count('abc', 'B', 'lol'), tuple('abc', 0));
    self::assertEqual(
      replace_count('abc', 'B', 'lol', true),
      tuple('alolc', 1),
    );

    self::log('splice');
    self::assertEqual(splice('abc', 1, 1), 'ac');
    self::assertEqual(splice('abc', 1, 1, 'lol'), 'alolc');

    self::log('slice');
    self::assertEqual(slice('abc', 1, 1), 'b');
    self::assertEqual(slice('abc', -1, 1), 'c');
    self::assertEqual(slice('abc', 1, -1), 'b');
    self::assertEqual(slice('abc', 1), 'bc');
    self::assertEqual(slice('abc', -1), 'c');

    self::log('pad');
    self::assertEqual(pad('abc', 3), 'abc');
    self::assertEqual(pad('abc', 4), 'abc ');
    self::assertEqual(pad('abc', 5), ' abc ');
    self::assertEqual(pad('abc', 6), ' abc  ');
    self::assertEqual(pad('1', 3, 'ab'), 'a1a');
    self::assertEqual(pad('1', 4, 'ab'), 'a1ab');

    self::log('pad_left');
    self::assertEqual(pad_left('abc', 3), 'abc');
    self::assertEqual(pad_left('abc', 4), ' abc');
    self::assertEqual(pad_left('abc', 5), '  abc');
    self::assertEqual(pad_left('abc', 6), '   abc');
    self::assertEqual(pad_left('1', 3, 'ab'), 'ab1');
    self::assertEqual(pad_left('1', 4, 'ab'), 'aba1');

    self::log('pad_right');
    self::assertEqual(pad_right('abc', 3), 'abc');
    self::assertEqual(pad_right('abc', 4), 'abc ');
    self::assertEqual(pad_right('abc', 5), 'abc  ');
    self::assertEqual(pad_right('abc', 6), 'abc   ');
    self::assertEqual(pad_right('1', 3, 'ab'), '1ab');
    self::assertEqual(pad_right('1', 4, 'ab'), '1aba');

    self::log('repeat_string');
    self::assertEqual(repeat_string('123', 3), '123123123');

    self::log('from_char_code');
    self::assertEqual(from_char_code(128), "\x80");
    self::assertEqual(from_char_code(0), "\x00");
    self::assertEqual(from_char_code(255), "\xFF");

    self::log('char_code_at');
    self::assertEqual(char_code_at('a'), 97);
    self::assertEqual(char_code_at('a99'), 97);

    self::log('str_cmp');
    self::assertEqual(str_cmp('a', 'a'), 0);
    self::assertEqual(str_cmp('a', 'A'), 1);
    self::assertEqual(str_cmp('', ''), 0);
    self::assertEqual(str_cmp('', 'a'), -1);
    self::assertEqual(str_cmp('a', ''), 1);

    self::assertEqual(str_cmp('a', 'a', true), 0);
    self::assertEqual(str_cmp('a', 'A', true), 0);
    self::assertEqual(str_cmp('', '', true), 0);
    self::assertEqual(str_cmp('', 'a', true), -1);
    self::assertEqual(str_cmp('a', '', true), 1);

    self::log('str_eq');
    self::assertEqual(str_eq('a', 'a'), true);
    self::assertEqual(str_eq('a', 'A'), false);
    self::assertEqual(str_eq('', ''), true);
    self::assertEqual(str_eq('', 'a'), false);
    self::assertEqual(str_eq('a', ''), false);

    self::assertEqual(str_eq('a', 'a', true), true);
    self::assertEqual(str_eq('a', 'A', true), true);
    self::assertEqual(str_eq('', '', true), true);
    self::assertEqual(str_eq('', 'a', true), false);
    self::assertEqual(str_eq('a', '', true), false);

    self::log('find');
    self::assertEqual(find('a', 'a'), 0);
    self::assertEqual(find('a', 'a', 1), NULL_INT);
    self::assertEqual(find('a', 'a', -1), 0);
    self::assertEqual(find('abc', 'a'), 0);
    self::assertEqual(find('abc', 'b'), 1);
    self::assertEqual(find('abc', 'c'), 2);
    self::assertEqual(find('abc', 'a', -2), NULL_INT);
    self::assertEqual(find('abc', 'b', -2), 1);
    self::assertEqual(find('abc', 'c', -2), 2);
    self::assertEqual(find('abbb', 'bb'), 1);
    self::assertEqual(find('abbb', 'bb', 2), 2);

    self::log('find_last');
    self::assertEqual(find_last('a', 'a'), 0);
    self::assertEqual(find_last('a', 'a', 1), NULL_INT);
    self::assertEqual(find_last('a', 'a', -1), 0);
    self::assertEqual(find_last('aba', 'a'), 2);
    self::assertEqual(find_last('aba', 'b'), 1);
    self::assertEqual(find_last('aba', 'c'), NULL_INT);
    self::assertEqual(find_last('aba', 'a', -2), 0);
    self::assertEqual(find_last('aba', 'b', -2), 1);
    self::assertEqual(find_last('aba', 'c', -2), NULL_INT);
    self::assertEqual(find_last('abbb', 'bb'), 2);
    self::assertEqual(find_last('abbb', 'bb', 2), 2);

    self::log('ends_with');
    self::assertEqual(ends_with('abbb', 'bb'), true);
    self::assertEqual(ends_with('abbb', 'ba'), false);
    self::assertEqual(ends_with('abbb', ''), true);
    self::assertEqual(ends_with('', ''), true);
    self::assertEqual(ends_with('', 'a'), false);

    self::log('starts_with');
    self::assertEqual(starts_with('abbb', 'ab'), true);
    self::assertEqual(starts_with('abbb', 'bb'), false);
    self::assertEqual(starts_with('abbb', ''), true);
    self::assertEqual(starts_with('', ''), true);
    self::assertEqual(starts_with('', 'a'), false);

    self::log('round_half_down');
    self::assertEqual(round_half_down(0.5), 0.0);
    self::assertEqual(round_half_down(1.5), 1.0);
    self::assertEqual(round_half_down(-0.5), -1.0);
    self::assertEqual(round_half_down(-1.5), -2.0);
    self::assertEqual(round_half_down(INF), INF);
    self::assertEqual(round_half_down(-INF), -INF);
    self::assertEqual(round_half_down(NAN), NAN);

    self::log('round_half_up');
    self::assertEqual(round_half_up(0.5), 1.0);
    self::assertEqual(round_half_up(1.5), 2.0);
    self::assertEqual(round_half_up(-0.5), 0.0);
    self::assertEqual(round_half_up(-1.5), -1.0);
    self::assertEqual(round_half_up(INF), INF);
    self::assertEqual(round_half_up(-INF), -INF);
    self::assertEqual(round_half_up(NAN), NAN);

    self::log('round_half_to_inf');
    self::assertEqual(round_half_to_inf(0.5), 1.0);
    self::assertEqual(round_half_to_inf(1.5), 2.0);
    self::assertEqual(round_half_to_inf(-0.5), -1.0);
    self::assertEqual(round_half_to_inf(-1.5), -2.0);
    self::assertEqual(round_half_to_inf(INF), INF);
    self::assertEqual(round_half_to_inf(-INF), -INF);
    self::assertEqual(round_half_to_inf(NAN), NAN);

    self::log('round_half_to_zero');
    self::assertEqual(round_half_to_zero(0.5), 0.0);
    self::assertEqual(round_half_to_zero(1.5), 1.0);
    self::assertEqual(round_half_to_zero(-0.5), 0.0);
    self::assertEqual(round_half_to_zero(-1.5), -1.0);
    self::assertEqual(round_half_to_zero(INF), INF);
    self::assertEqual(round_half_to_zero(-INF), -INF);
    self::assertEqual(round_half_to_zero(NAN), NAN);

    self::log('round_half_to_even');
    self::assertEqual(round_half_to_even(0.5), 0.0);
    self::assertEqual(round_half_to_even(1.5), 2.0);
    self::assertEqual(round_half_to_even(-0.5), 0.0);
    self::assertEqual(round_half_to_even(-1.5), -2.0);
    self::assertEqual(round_half_to_even(INF), INF);
    self::assertEqual(round_half_to_even(-INF), -INF);
    self::assertEqual(round_half_to_even(NAN), NAN);

    self::log('round_half_to_odd');
    self::assertEqual(round_half_to_odd(0.5), 1.0);
    self::assertEqual(round_half_to_odd(1.5), 1.0);
    self::assertEqual(round_half_to_odd(-0.5), -1.0);
    self::assertEqual(round_half_to_odd(-1.5), -1.0);
    self::assertEqual(round_half_to_odd(INF), INF);
    self::assertEqual(round_half_to_odd(-INF), -INF);
    self::assertEqual(round_half_to_odd(NAN), NAN);

    self::log('set_length');
    self::assertEqual(set_length('ab', -3), '');
    self::assertEqual(set_length('ab', -2), '');
    self::assertEqual(set_length('ab', -1), 'a');
    self::assertEqual(set_length('ab', 0), '');
    self::assertEqual(set_length('ab', 1), 'a');
    self::assertEqual(set_length('ab', 2), 'ab');
    self::assertEqual(set_length('ab', 3), 'ab ');
    self::assertEqual(set_length('ab', 4), 'ab  ');
    self::assertEqual(set_length('ab', 3, '12'), 'ab1');
    self::assertEqual(set_length('ab', 4, '12'), 'ab12');
    self::assertEqual(set_length('ab', 5, '12'), 'ab121');
    self::assertEqual(set_length('ab', 6, '12'), 'ab1212');

    self::log('split_at');
    self::assertEqual(split_at('abc', -4), tuple('', 'abc'));
    self::assertEqual(split_at('abc', -3), tuple('', 'abc'));
    self::assertEqual(split_at('abc', -2), tuple('a', 'bc'));
    self::assertEqual(split_at('abc', -1), tuple('ab', 'c'));
    self::assertEqual(split_at('abc', 0), tuple('', 'abc'));
    self::assertEqual(split_at('abc', 1), tuple('a', 'bc'));
    self::assertEqual(split_at('abc', 2), tuple('ab', 'c'));
    self::assertEqual(split_at('abc', 3), tuple('abc', ''));
    self::assertEqual(split_at('abc', 4), tuple('abc', ''));

    self::log('is_leap_year');
    self::assertEqual(is_leap_year(2016), true);
    self::assertEqual(is_leap_year(2015), false);
    self::assertEqual(is_leap_year(2000), true);
    self::assertEqual(is_leap_year(2400), true);
    self::assertEqual(is_leap_year(2401), false);
    self::assertEqual(is_leap_year(2404), true);
    self::assertEqual(is_leap_year(2500), false);
    self::assertEqual(is_leap_year(2504), true);
    self::assertEqual(is_leap_year(1900), false);
    self::assertEqual(is_leap_year(2100), false);
    self::assertEqual(is_leap_year(2104), true);

    self::log('days_in_month');
    self::assertEqual(days_in_month(2016, 1), 31);
    self::assertEqual(days_in_month(2016, 2), 29);
    self::assertEqual(days_in_month(2016, 3), 31);
    self::assertEqual(days_in_month(2016, 4), 30);
    self::assertEqual(days_in_month(2016, 5), 31);
    self::assertEqual(days_in_month(2016, 6), 30);
    self::assertEqual(days_in_month(2016, 7), 31);
    self::assertEqual(days_in_month(2016, 8), 31);
    self::assertEqual(days_in_month(2016, 9), 30);
    self::assertEqual(days_in_month(2016, 10), 31);
    self::assertEqual(days_in_month(2016, 11), 30);
    self::assertEqual(days_in_month(2016, 12), 31);
    self::assertEqual(days_in_month(2015, 2), 28);
    self::assertEqual(days_in_month(2012, 2), 29);

    self::log('overflow_date');
    self::assertEqual(overflow_date(2015, 1, 0), tuple(2014, 12, 31));
    self::assertEqual(overflow_date(2015, 1, 365), tuple(2015, 12, 31));
    self::assertEqual(overflow_date(2015, 2, 29), tuple(2015, 3, 1));
    self::assertEqual(overflow_date(2016, 1, 366), tuple(2016, 12, 31));
    self::assertEqual(overflow_date(2015, 13, 366), tuple(2016, 12, 31));
    self::assertEqual(
      overflow_date(2016, 16, -31 - 28 - 31),
      tuple(2016, 12, 31),
    );
    self::assertEqual(
      overflow_date(2016, -3, 30 + 31 + 30 + 31 + 17),
      tuple(2016, 1, 17),
    );
    self::assertEqual(overflow_date(2016, -3, -8), tuple(2015, 8, 23));

    self::log('is_valid_date');
    self::assertEqual(is_valid_date(2016, 2, 29), true);
    self::assertEqual(is_valid_date(2015, 2, 29), false);
    self::assertEqual(is_valid_date(2016, 11, 23), true);
    self::assertEqual(is_valid_date(2016, 11, 30), true);
    self::assertEqual(is_valid_date(2016, 11, 31), false);
    self::assertEqual(is_valid_date(2016, 12, 31), true);
    self::assertEqual(is_valid_date(2016, 12, 32), false);
    self::assertEqual(is_valid_date(2016, 13, 31), false);
    self::assertEqual(is_valid_date(2016, 0, 31), false);
    self::assertEqual(is_valid_date(2016, -1, 31), false);
    self::assertEqual(is_valid_date(2016, 1, 30), true);
    self::assertEqual(is_valid_date(2016, 1, 0), false);
    self::assertEqual(is_valid_date(2016, 1, -1), false);
    self::assertEqual(is_valid_date(0, 1, 1), true);
    self::assertEqual(is_valid_date(INT_MAX, 1, 1), true);
    self::assertEqual(is_valid_date(INT_MIN, 1, 1), true);

    self::log('quot/rem/div/mod');
    self::assertEqual(quot(-20, 3), -6);
    self::assertEqual(rem(-20, 3), -2);
    self::assertEqual(div(-20, 3), -7);
    self::assertEqual(mod(-20, 3), 1);
    self::assertEqual(mod(2, 3), 2);
    self::assertEqual(rem(2, 3), 2);
    self::assertEqual(mod(10, 5), 0);
    self::assertEqual(rem(10, 5), 0);
    self::assertEqual(mod(1, -1), 0);
    self::assertEqual(rem(1, -1), 0);
    self::assertEqual(mod(2, -3), -1);
    self::assertEqual(rem(2, -3), 2);

    self::assertEqual(mod(5, 3), 2);
    self::assertEqual(rem(5, 3), 2);
    self::assertEqual(mod(5, -3), -1);
    self::assertEqual(rem(5, -3), 2);
    self::assertEqual(mod(-5, 3), 1);
    self::assertEqual(rem(-5, 3), -2);
    self::assertEqual(mod(-5, -3), -2);
    self::assertEqual(rem(-5, -3), -2);

    self::log('div_mod/quot_rem');
    self::assertEqual(div_mod(-20, 3), tuple(-7, 1));
    self::assertEqual(div_mod(-20, -3), tuple(6, -2));
    self::assertEqual(quot_rem(-20, 3), tuple(-6, -2));
    self::assertEqual(quot_rem(-20, -3), tuple(6, -2));

    self::log('concat_map');
    self::assertEqual(
      concat_map([1, 5], $x ==> [$x + 1, $x + 2]),
      [2, 3, 6, 7],
    );

    self::log('frac');
    self::assertEqual(frac(0.1), 0.1);
    self::assertEqual(frac(0.9), 0.9);
    self::assertEqual(frac(0.5), 0.5);
    self::assertEqual(frac(0.0), 0.0);
    self::assertEqual(frac(5.0), 5.0 - 5.0);
    self::assertEqual(frac(5.1), 5.1 - 5.0);
    self::assertEqual(frac(5.9), 5.9 - 5.0);
    self::assertEqual(frac(5.5), 5.5 - 5.0);
    self::assertEqual(frac(-0.1), -0.1);
    self::assertEqual(frac(-0.9), -0.9);
    self::assertEqual(frac(-0.5), -0.5);
    self::assertEqual(frac(-0.0), 0.0);
    self::assertEqual(frac(-5.0), -5.0 + 5.0);
    self::assertEqual(frac(-5.1), -5.1 + 5.0);
    self::assertEqual(frac(-5.9), -5.9 + 5.0);
    self::assertEqual(frac(-5.5), -5.5 + 5.0);

    self::log('typeof');
    self::assertEqual(typeof(NULL_INT), 'null');
    self::assertEqual(typeof(true), 'bool');
    self::assertEqual(typeof(false), 'bool');
    self::assertEqual(typeof(0.0), 'float');
    self::assertEqual(typeof(PI), 'float');
    self::assertEqual(typeof(0), 'int');
    self::assertEqual(typeof(129837), 'int');
    self::assertEqual(typeof([]), 'array');
    self::assertEqual(typeof([[]]), 'array');
    self::assertEqual(typeof([1]), 'array');
    self::assertEqual(typeof(new \stdClass()), 'stdClass');
    self::assertEqual(typeof(function() {}), 'Closure');
    self::assertEqual(typeof(\fopen('php://memory', 'rb')), 'resource');

    self::log('LocalFileSystem');
    $fs = new LocalFileSystem();
    $path = '/tmp/hufs-test-'.\mt_rand();
    self::testFilesystem($fs, $path);
    $fs = new StreamWrapperFileSystem(new FileSystemStreamWrapper($fs));
    self::testFilesystem($fs, $path);

    self::log('ArrayIterator');
    self::testArrayIterator();

    self::log('DateTime');
    self::testDateTime();

    self::log('done');
  }

  private static function testDateTime(): void {
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

  private static function testArrayIterator(): void {
    $a = new ArrayIterator(['a' => 1, 'b' => 2]);

    self::assertEqual($a->count(), 2);
    self::assertEqual($a->unwrap(), ['a' => 1, 'b' => 2]);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->key(), 'a');
    self::assertEqual($a->current(), 1);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->each(), ['a', 1]);

    self::assertEqual($a->prev(), 1);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->each(), ['a', 1]);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->each(), ['b', 2]);

    self::assertEqual($a->valid(), false);
    self::assertEqual($a->each(), NULL_INT);

    // prev() on an invalid iterator does nothing
    self::assertEqual($a->prev(), NULL_INT);

    self::assertEqual($a->valid(), false);
    self::assertEqual($a->each(), NULL_INT);

    self::assertEqual($a->reset(), 1);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->each(), ['a', 1]);

    self::assertEqual($a->end(), 2);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->each(), ['b', 2]);

    self::assertEqual($a->valid(), false);
    self::assertEqual($a->each(), NULL_INT);

    self::assertEqual(
      self::getException(
        function() use ($a) {
          $a->current();
        },
      )->getMessage(),
      'Cannot get value: Array is beyond last element',
    );

    self::assertEqual(
      self::getException(
        function() use ($a) {
          $a->key();
        },
      )->getMessage(),
      'Cannot get key: Array is beyond last element',
    );

    $a = new ArrayIterator([]);
    self::assertEqual($a->count(), 0);
    self::assertEqual($a->unwrap(), []);
    self::assertEqual($a->reset(), NULL_INT);
    self::assertEqual($a->end(), NULL_INT);

    $a = new ArrayIterator(['foot', 'bike', 'car', 'plane']);
    self::assertEqual($a->current(), 'foot');
    self::assertEqual($a->next(), 'bike');
    self::assertEqual($a->next(), 'car');
    self::assertEqual($a->prev(), 'bike');
    self::assertEqual($a->end(), 'plane');
  }

  private static function testFilesystem(FileSystem $fs, string $base): void {
    self::assertEqual($fs->trystat($base), NULL_INT);
    $fs->mkdir($base);
    self::assertEqual($fs->stat($base)->modeSymbolic(), 'drwxr-xr-x');

    $file = $fs->join($base, 'foo');
    $fs->writeFile($file, 'contents');
    self::assertEqual($fs->readFile($file), 'contents');

    $open = $fs->open($file, 'rb');
    self::assertEqual($open->eof(), false);
    self::assertEqual($open->tell(), 0);
    self::assertEqual($open->read(4), 'cont');
    self::assertEqual($open->eof(), false);
    self::assertEqual($open->tell(), 4);
    $open->seek(2);
    self::assertEqual($open->tell(), 2);
    $open->seek(2, \SEEK_CUR);
    self::assertEqual($open->tell(), 4);
    self::assertEqual($open->eof(), false);
    self::assertEqual($open->read(100), 'ents');
    self::assertEqual($open->read(100), '');
    self::assertEqual($open->eof(), true);
    self::assertEqual($open->getSize(), 8);
    self::assertEqual($open->stat()->modeSymbolic(), '-rw-r--r--');
    self::assertEqual($open->getContents(), '');
    self::assertEqual($open->__toString(), 'contents');
    self::assertEqual($open->getContents(), '');
    $open->rewind();
    self::assertEqual($open->getContents(), 'contents');
    self::assertEqual($open->tell(), 8);
    self::assertEqual($open->isReadable(), true);
    self::assertEqual($open->isWritable(), false);
    self::assertEqual($open->isSeekable(), true);
    $open->close();

    $open = $fs->open($file, 'wb+');
    self::assertEqual($open->tell(), 0);
    self::assertEqual($open->eof(), false);
    self::assertEqual($open->getSize(), 0);
    self::assertEqual($open->getContents(), '');
    self::assertEqual($open->__toString(), '');
    self::assertEqual($open->write('hello'), 5);
    self::assertEqual($open->tell(), 5);
    self::assertEqual($open->eof(), true);
    self::assertEqual($open->getContents(), '');
    self::assertEqual($open->__toString(), 'hello');
    $open->rewind();
    self::assertEqual($open->getContents(), 'hello');
    self::assertEqual($open->getContents(), '');
    $open->seek(2);
    self::assertEqual($open->tell(), 2);
    self::assertEqual($open->write('__'), 2);
    self::assertEqual($open->tell(), 4);
    self::assertEqual($open->getContents(), 'o');
    self::assertEqual($open->tell(), 5);
    self::assertEqual($open->__toString(), 'he__o');
    self::assertEqual($open->tell(), 5);
    self::assertEqual($open->eof(), true);

    // $fs->symlink($file.'2', $file);
    // self::assertEqual($fs->stat($file)?->modeSymbolic(), '-rw-r--r--');
    // self::assertEqual($fs->stat($file.'2')?->modeSymbolic(), '-rw-r--r--');
    // self::assertEqual($fs->lstat($file)?->modeSymbolic(), '-rw-r--r--');
    // self::assertEqual($fs->lstat($file.'2')?->modeSymbolic(), 'lrwxrwxrwx');

    $fs->unlink($file);

    $fs->rmdirRec($base);
  }

  private static function log(string $message): void {
    print $message."\n";
  }
}
