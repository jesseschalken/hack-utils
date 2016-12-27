<?hh // strict

namespace HackUtils;

function _assert_equal<T>(T $actual, T $expected): void {
  if (!_is_equal($actual, $expected)) {
    throw new \Exception(
      \sprintf(
        "Expected %s, got %s",
        \var_export($expected, true),
        \var_export($actual, true),
      ),
    );
  }
}

function _is_equal<T>(T $a, T $b): bool {
  if (\is_float($a) && \is_float($b)) {
    // Consider NAN as equal to itself (PHP doesn't)
    if (\is_nan($a) && \is_nan($b))
      return true;
    // Don't consider -0.0 and +0.0 as equal (PHP does)
    if ($a === 0.0 && $b === 0.0 && (string) $a !== (string) $b)
      return false;
  }

  if (\is_array($a) && \is_array($b)) {
    if (\count($a) !== \count($b))
      return false;
    // Iterate over both arrays in parallel
    $iterA = new ArrayIterator($a);
    $iterB = new ArrayIterator($b);
    for (
      $iterA->rewind(), $iterB->rewind();
      $iterA->valid() && $iterB->valid();
      $iterA->next(), $iterB->next()
    ) {
      if (!_is_equal($iterA->key(), $iterB->key()) ||
          !_is_equal($iterA->current(), $iterB->current())) {
        return false;
      }
    }
    // This shouldn't really happen because we already checked both arrays
    // are the same length, but just in case.
    if ($iterA->valid() != $iterB->valid()) {
      return false;
    }
    return true;
  }

  return $a === $b;
}

function _run_tests(): void {
  _assert_equal(to_hex("\x00\xff\x20"), "00ff20");
  _assert_equal(from_hex("00ff20"), "\x00\xff\x20");
  _assert_equal(from_hex("00Ff20"), "\x00\xff\x20");

  _assert_equal(length(str_shuffle("abc")), 3);

  _assert_equal(reverse_string("abc"), 'cba');
  _assert_equal(reverse_string(""), '');

  _assert_equal(to_lower("ABC.1.2.3"), "abc.1.2.3");
  _assert_equal(to_upper("abc.1.2.3"), "ABC.1.2.3");

  _assert_equal(split(''), []);
  _assert_equal(split('a'), ['a']);
  _assert_equal(split('abc'), ['a', 'b', 'c']);

  _assert_equal(split('', '', 1), []);
  _assert_equal(split('a', '', 1), ['a']);
  _assert_equal(split('abc', '', 1), ['abc']);
  _assert_equal(split('abc', '', 2), ['a', 'bc']);
  _assert_equal(split('abc', '', 3), ['a', 'b', 'c']);

  _assert_equal(split('', 'b'), ['']);
  _assert_equal(split('abc', 'b'), ['a', 'c']);

  _assert_equal(split('abc', 'b', 1), ['abc']);
  _assert_equal(split('abc', 'b', 2), ['a', 'c']);

  _assert_equal(chunk_string('abc', 1), ['a', 'b', 'c']);
  _assert_equal(chunk_string('abc', 2), ['ab', 'c']);
  _assert_equal(chunk_string('abc', 3), ['abc']);

  _assert_equal(join([]), '');
  _assert_equal(join(['abc']), 'abc');
  _assert_equal(join(['a', 'bc']), 'abc');

  _assert_equal(join([], ','), '');
  _assert_equal(join(['abc'], ','), 'abc');
  _assert_equal(join(['a', 'bc'], ','), 'a,bc');

  _assert_equal(replace_count('abc', 'b', 'lol'), tuple('alolc', 1));
  _assert_equal(replace_count('abc', 'B', 'lol'), tuple('abc', 0));
  _assert_equal(replace_count('abc', 'B', 'lol', true), tuple('alolc', 1));

  _assert_equal(splice('abc', 1, 1), 'ac');
  _assert_equal(splice('abc', 1, 1, 'lol'), 'alolc');

  _assert_equal(slice('abc', 1, 1), 'b');
  _assert_equal(slice('abc', -1, 1), 'c');
  _assert_equal(slice('abc', 1, -1), 'b');
  _assert_equal(slice('abc', 1), 'bc');
  _assert_equal(slice('abc', -1), 'c');

  _assert_equal(pad('abc', 3), 'abc');
  _assert_equal(pad('abc', 4), 'abc ');
  _assert_equal(pad('abc', 5), ' abc ');
  _assert_equal(pad('abc', 6), ' abc  ');
  _assert_equal(pad('1', 3, 'ab'), 'a1a');
  _assert_equal(pad('1', 4, 'ab'), 'a1ab');

  _assert_equal(pad_left('abc', 3), 'abc');
  _assert_equal(pad_left('abc', 4), ' abc');
  _assert_equal(pad_left('abc', 5), '  abc');
  _assert_equal(pad_left('abc', 6), '   abc');
  _assert_equal(pad_left('1', 3, 'ab'), 'ab1');
  _assert_equal(pad_left('1', 4, 'ab'), 'aba1');

  _assert_equal(pad_right('abc', 3), 'abc');
  _assert_equal(pad_right('abc', 4), 'abc ');
  _assert_equal(pad_right('abc', 5), 'abc  ');
  _assert_equal(pad_right('abc', 6), 'abc   ');
  _assert_equal(pad_right('1', 3, 'ab'), '1ab');
  _assert_equal(pad_right('1', 4, 'ab'), '1aba');

  _assert_equal(str_repeat('123', 3), '123123123');

  _assert_equal(from_char_code(128), "\x80");
  _assert_equal(from_char_code(0), "\x00");
  _assert_equal(from_char_code(255), "\xFF");

  _assert_equal(char_code_at('a'), 97);
  _assert_equal(char_code_at('a99'), 97);

  _assert_equal(str_cmp('a', 'a'), 0);
  _assert_equal(str_cmp('a', 'A'), 1);
  _assert_equal(str_cmp('', ''), 0);
  _assert_equal(str_cmp('', 'a'), -1);
  _assert_equal(str_cmp('a', ''), 1);

  _assert_equal(str_cmp('a', 'a', true), 0);
  _assert_equal(str_cmp('a', 'A', true), 0);
  _assert_equal(str_cmp('', '', true), 0);
  _assert_equal(str_cmp('', 'a', true), -1);
  _assert_equal(str_cmp('a', '', true), 1);

  _assert_equal(str_eq('a', 'a'), true);
  _assert_equal(str_eq('a', 'A'), false);
  _assert_equal(str_eq('', ''), true);
  _assert_equal(str_eq('', 'a'), false);
  _assert_equal(str_eq('a', ''), false);

  _assert_equal(str_eq('a', 'a', true), true);
  _assert_equal(str_eq('a', 'A', true), true);
  _assert_equal(str_eq('', '', true), true);
  _assert_equal(str_eq('', 'a', true), false);
  _assert_equal(str_eq('a', '', true), false);

  _assert_equal(find('a', 'a'), 0);
  _assert_equal(find('a', 'a', 1), null);
  _assert_equal(find('a', 'a', -1), 0);
  _assert_equal(find('abc', 'a'), 0);
  _assert_equal(find('abc', 'b'), 1);
  _assert_equal(find('abc', 'c'), 2);
  _assert_equal(find('abc', 'a', -2), null);
  _assert_equal(find('abc', 'b', -2), 1);
  _assert_equal(find('abc', 'c', -2), 2);
  _assert_equal(find('abbb', 'bb'), 1);
  _assert_equal(find('abbb', 'bb', 2), 2);

  _assert_equal(find_last('a', 'a'), 0);
  _assert_equal(find_last('a', 'a', 1), null);
  _assert_equal(find_last('a', 'a', -1), 0);
  _assert_equal(find_last('aba', 'a'), 2);
  _assert_equal(find_last('aba', 'b'), 1);
  _assert_equal(find_last('aba', 'c'), null);
  _assert_equal(find_last('aba', 'a', -2), 0);
  _assert_equal(find_last('aba', 'b', -2), 1);
  _assert_equal(find_last('aba', 'c', -2), null);
  _assert_equal(find_last('abbb', 'bb'), 2);
  _assert_equal(find_last('abbb', 'bb', 2), 2);

  _assert_equal(ends_with('abbb', 'bb'), true);
  _assert_equal(ends_with('abbb', 'ba'), false);
  _assert_equal(ends_with('abbb', ''), true);
  _assert_equal(ends_with('', ''), true);
  _assert_equal(ends_with('', 'a'), false);

  _assert_equal(starts_with('abbb', 'ab'), true);
  _assert_equal(starts_with('abbb', 'bb'), false);
  _assert_equal(starts_with('abbb', ''), true);
  _assert_equal(starts_with('', ''), true);
  _assert_equal(starts_with('', 'a'), false);

  _assert_equal(round_half_down(0.5), 0.0);
  _assert_equal(round_half_down(1.5), 1.0);
  _assert_equal(round_half_down(-0.5), -1.0);
  _assert_equal(round_half_down(-1.5), -2.0);
  _assert_equal(round_half_down(INF), INF);
  _assert_equal(round_half_down(-INF), -INF);
  _assert_equal(round_half_down(NAN), NAN);

  _assert_equal(round_half_up(0.5), 1.0);
  _assert_equal(round_half_up(1.5), 2.0);
  _assert_equal(round_half_up(-0.5), 0.0);
  _assert_equal(round_half_up(-1.5), -1.0);
  _assert_equal(round_half_up(INF), INF);
  _assert_equal(round_half_up(-INF), -INF);
  _assert_equal(round_half_up(NAN), NAN);

  _assert_equal(round_half_to_inf(0.5), 1.0);
  _assert_equal(round_half_to_inf(1.5), 2.0);
  _assert_equal(round_half_to_inf(-0.5), -1.0);
  _assert_equal(round_half_to_inf(-1.5), -2.0);
  _assert_equal(round_half_to_inf(INF), INF);
  _assert_equal(round_half_to_inf(-INF), -INF);
  _assert_equal(round_half_to_inf(NAN), NAN);

  _assert_equal(round_half_to_zero(0.5), 0.0);
  _assert_equal(round_half_to_zero(1.5), 1.0);
  _assert_equal(round_half_to_zero(-0.5), 0.0);
  _assert_equal(round_half_to_zero(-1.5), -1.0);
  _assert_equal(round_half_to_zero(INF), INF);
  _assert_equal(round_half_to_zero(-INF), -INF);
  _assert_equal(round_half_to_zero(NAN), NAN);

  _assert_equal(round_half_to_even(0.5), 0.0);
  _assert_equal(round_half_to_even(1.5), 2.0);
  _assert_equal(round_half_to_even(-0.5), 0.0);
  _assert_equal(round_half_to_even(-1.5), -2.0);
  _assert_equal(round_half_to_even(INF), INF);
  _assert_equal(round_half_to_even(-INF), -INF);
  _assert_equal(round_half_to_even(NAN), NAN);

  _assert_equal(round_half_to_odd(0.5), 1.0);
  _assert_equal(round_half_to_odd(1.5), 1.0);
  _assert_equal(round_half_to_odd(-0.5), -1.0);
  _assert_equal(round_half_to_odd(-1.5), -1.0);
  _assert_equal(round_half_to_odd(INF), INF);
  _assert_equal(round_half_to_odd(-INF), -INF);
  _assert_equal(round_half_to_odd(NAN), NAN);

  _assert_equal(set_length('ab', -3), '');
  _assert_equal(set_length('ab', -2), '');
  _assert_equal(set_length('ab', -1), 'a');
  _assert_equal(set_length('ab', 0), '');
  _assert_equal(set_length('ab', 1), 'a');
  _assert_equal(set_length('ab', 2), 'ab');
  _assert_equal(set_length('ab', 3), 'ab ');
  _assert_equal(set_length('ab', 4), 'ab  ');
  _assert_equal(set_length('ab', 3, '12'), 'ab1');
  _assert_equal(set_length('ab', 4, '12'), 'ab12');
  _assert_equal(set_length('ab', 5, '12'), 'ab121');
  _assert_equal(set_length('ab', 6, '12'), 'ab1212');

  _assert_equal(split_at('abc', -4), tuple('', 'abc'));
  _assert_equal(split_at('abc', -3), tuple('', 'abc'));
  _assert_equal(split_at('abc', -2), tuple('a', 'bc'));
  _assert_equal(split_at('abc', -1), tuple('ab', 'c'));
  _assert_equal(split_at('abc', 0), tuple('', 'abc'));
  _assert_equal(split_at('abc', 1), tuple('a', 'bc'));
  _assert_equal(split_at('abc', 2), tuple('ab', 'c'));
  _assert_equal(split_at('abc', 3), tuple('abc', ''));
  _assert_equal(split_at('abc', 4), tuple('abc', ''));

  _assert_equal(is_leap_year(2016), true);
  _assert_equal(is_leap_year(2015), false);
  _assert_equal(is_leap_year(2000), true);
  _assert_equal(is_leap_year(2400), true);
  _assert_equal(is_leap_year(2401), false);
  _assert_equal(is_leap_year(2404), true);
  _assert_equal(is_leap_year(2500), false);
  _assert_equal(is_leap_year(2504), true);
  _assert_equal(is_leap_year(1900), false);
  _assert_equal(is_leap_year(2100), false);
  _assert_equal(is_leap_year(2104), true);

  _assert_equal(days_in_month(2016, 1), 31);
  _assert_equal(days_in_month(2016, 2), 29);
  _assert_equal(days_in_month(2016, 3), 31);
  _assert_equal(days_in_month(2016, 4), 30);
  _assert_equal(days_in_month(2016, 5), 31);
  _assert_equal(days_in_month(2016, 6), 30);
  _assert_equal(days_in_month(2016, 7), 31);
  _assert_equal(days_in_month(2016, 8), 31);
  _assert_equal(days_in_month(2016, 9), 30);
  _assert_equal(days_in_month(2016, 10), 31);
  _assert_equal(days_in_month(2016, 11), 30);
  _assert_equal(days_in_month(2016, 12), 31);
  _assert_equal(days_in_month(2015, 2), 28);
  _assert_equal(days_in_month(2012, 2), 29);

  _assert_equal(overflow_date(2015, 1, 0), tuple(2014, 12, 31));
  _assert_equal(overflow_date(2015, 1, 365), tuple(2015, 12, 31));
  _assert_equal(overflow_date(2015, 2, 29), tuple(2015, 3, 1));
  _assert_equal(overflow_date(2016, 1, 366), tuple(2016, 12, 31));
  _assert_equal(overflow_date(2015, 13, 366), tuple(2016, 12, 31));
  _assert_equal(overflow_date(2016, 16, -31 - 28 - 31), tuple(2016, 12, 31));
  _assert_equal(
    overflow_date(2016, -3, 30 + 31 + 30 + 31 + 17),
    tuple(2016, 1, 17),
  );
  _assert_equal(overflow_date(2016, -3, -8), tuple(2015, 8, 23));

  _assert_equal(is_valid_date(2016, 2, 29), true);
  _assert_equal(is_valid_date(2015, 2, 29), false);
  _assert_equal(is_valid_date(2016, 11, 23), true);
  _assert_equal(is_valid_date(2016, 11, 30), true);
  _assert_equal(is_valid_date(2016, 11, 31), false);
  _assert_equal(is_valid_date(2016, 12, 31), true);
  _assert_equal(is_valid_date(2016, 12, 32), false);
  _assert_equal(is_valid_date(2016, 13, 31), false);
  _assert_equal(is_valid_date(2016, 0, 31), false);
  _assert_equal(is_valid_date(2016, -1, 31), false);
  _assert_equal(is_valid_date(2016, 1, 30), true);
  _assert_equal(is_valid_date(2016, 1, 0), false);
  _assert_equal(is_valid_date(2016, 1, -1), false);
  _assert_equal(is_valid_date(0, 1, 1), true);
  _assert_equal(is_valid_date(INT_MAX, 1, 1), true);
  _assert_equal(is_valid_date(INT_MIN, 1, 1), true);

  _assert_equal(quot(-20, 3), -6);
  _assert_equal(rem(-20, 3), -2);
  _assert_equal(div(-20, 3), -7);
  _assert_equal(mod(-20, 3), 1);
  _assert_equal(mod(2, 3), 2);
  _assert_equal(rem(2, 3), 2);
  _assert_equal(mod(10, 5), 0);
  _assert_equal(rem(10, 5), 0);
  _assert_equal(mod(1, -1), 0);
  _assert_equal(rem(1, -1), 0);
  _assert_equal(mod(2, -3), -1);
  _assert_equal(rem(2, -3), 2);

  _assert_equal(mod(5, 3), 2);
  _assert_equal(rem(5, 3), 2);
  _assert_equal(mod(5, -3), -1);
  _assert_equal(rem(5, -3), 2);
  _assert_equal(mod(-5, 3), 1);
  _assert_equal(rem(-5, 3), -2);
  _assert_equal(mod(-5, -3), -2);
  _assert_equal(rem(-5, -3), -2);

  _assert_equal(div_mod(-20, 3), tuple(-7, 1));
  _assert_equal(div_mod(-20, -3), tuple(6, -2));
  _assert_equal(quot_rem(-20, 3), tuple(-6, -2));
  _assert_equal(quot_rem(-20, -3), tuple(6, -2));

  _assert_equal(concat_map([1, 5], $x ==> [$x + 1, $x + 2]), [2, 3, 6, 7]);

  _test_multiple(
    function($x) {
      return frac($x);
    },
    [
      tuple(0.1, 0.1),
      tuple(0.9, 0.9),
      tuple(0.5, 0.5),
      tuple(0.0, 0.0),
      tuple(5.0, 5.0 - 5.0),
      tuple(5.1, 5.1 - 5.0),
      tuple(5.9, 5.9 - 5.0),
      tuple(5.5, 5.5 - 5.0),
      tuple(-0.1, -0.1),
      tuple(-0.9, -0.9),
      tuple(-0.5, -0.5),
      tuple(-0.0, 0.0),
      tuple(-5.0, -5.0 + 5.0),
      tuple(-5.1, -5.1 + 5.0),
      tuple(-5.9, -5.9 + 5.0),
      tuple(-5.5, -5.5 + 5.0),
    ],
  );

  _test_multiple(
    function($x) {
      return typeof($x);
    },
    [
      tuple(null, 'null'),
      tuple(true, 'bool'),
      tuple(false, 'bool'),
      tuple(0.0, 'float'),
      tuple(PI, 'float'),
      tuple(0, 'int'),
      tuple(129837, 'int'),
      tuple([], 'array'),
      tuple([[]], 'array'),
      tuple([1], 'array'),
      tuple(new \stdClass(), 'stdClass'),
      tuple(function() {}, 'Closure'),
      tuple(\fopen('php://memory', 'rb'), 'resource'),
    ],
  );

  $fs = LocalFileSystem::create();
  $path = $fs->path('/tmp/hufs-test-'.\mt_rand());
  test_filesystem($fs, $path);
  test_filesystem(new FileSystemStreamWrapper($fs), $path);

  print "okay\n";
}

function test_filesystem(FileSystem $fs, Path $base): void {
  _assert_equal($fs->stat($base->format()), null);
  $fs->mkdir($base->format());
  _assert_equal($fs->stat($base->format())?->modeSymbolic(), 'drwxrwxr-x');

  $file = $base->join_str('foo')->format();
  $fs->writeFile($file, 'contents');
  _assert_equal($fs->readFile($file), 'contents');

  $open = $fs->open($file, 'rb');
  _assert_equal($open->eof(), false);
  _assert_equal($open->tell(), 0);
  _assert_equal($open->read(4), 'cont');
  _assert_equal($open->eof(), false);
  _assert_equal($open->tell(), 4);
  $open->seek(2);
  _assert_equal($open->tell(), 2);
  $open->seek(2, \SEEK_CUR);
  _assert_equal($open->tell(), 4);
  _assert_equal($open->eof(), false);
  _assert_equal($open->read(100), 'ents');
  _assert_equal($open->read(100), '');
  _assert_equal($open->eof(), true);
  _assert_equal($open->getSize(), 8);
  _assert_equal($open->stat()->modeSymbolic(), '-rw-rw-r--');
  _assert_equal($open->getContents(), '');
  _assert_equal($open->__toString(), 'contents');
  _assert_equal($open->getContents(), '');
  $open->rewind();
  _assert_equal($open->getContents(), 'contents');
  _assert_equal($open->tell(), 8);
  _assert_equal($open->isReadable(), true);
  _assert_equal($open->isWritable(), false);
  _assert_equal($open->isSeekable(), true);
  $open->close();

  $open = $fs->open($file, 'wb+');
  _assert_equal($open->tell(), 0);
  _assert_equal($open->eof(), false);
  _assert_equal($open->getSize(), 0);
  _assert_equal($open->getContents(), '');
  _assert_equal($open->__toString(), '');
  _assert_equal($open->write('hello'), 5);
  _assert_equal($open->tell(), 5);
  _assert_equal($open->eof(), true);
  _assert_equal($open->getContents(), '');
  _assert_equal($open->__toString(), 'hello');
  $open->rewind();
  _assert_equal($open->getContents(), 'hello');
  _assert_equal($open->getContents(), '');
  $open->seek(2);
  _assert_equal($open->tell(), 2);
  _assert_equal($open->write('__'), 2);
  _assert_equal($open->tell(), 4);
  _assert_equal($open->getContents(), 'o');
  _assert_equal($open->tell(), 5);
  _assert_equal($open->__toString(), 'he__o');
  _assert_equal($open->tell(), 5);
  _assert_equal($open->eof(), true);

  $fs->symlink($file.'2', $file);
  _assert_equal($fs->stat($file)?->modeSymbolic(), '-rw-rw-r--');
  _assert_equal($fs->stat($file.'2')?->modeSymbolic(), '-rw-rw-r--');
  _assert_equal($fs->lstat($file)?->modeSymbolic(), '-rw-rw-r--');
  _assert_equal($fs->lstat($file.'2')?->modeSymbolic(), 'lrwxrwxrwx');

  $fs->unlink($file);

  $fs->rmdir_rec($base->format());
}

function _test_multiple<Tin, Tout>(
  (function(Tin): Tout) $function,
  array<(Tin, Tout)> $samples,
): void {
  foreach ($samples as $pair) {
    _assert_equal($function($pair[0]), $pair[1]);
  }
}
