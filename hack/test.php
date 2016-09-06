<?hh // strict

namespace HackUtils;

require_once __DIR__.'/include.php';

function assert_eqaul<T>(T $actual, T $expected): void {
  if ($actual !== $expected) {
    throw new \Exception(
      \sprintf(
        "Expected %s, got %s",
        \var_export($expected, true),
        \var_export($actual, true),
      ),
    );
  }
}

function run_tests(): void {
  assert_eqaul(str\to_hex("\x00\xff\x20"), "00ff20");
  assert_eqaul(str\from_hex("00ff20"), "\x00\xff\x20");
  assert_eqaul(str\from_hex("00Ff20"), "\x00\xff\x20");

  assert_eqaul(str\len(str\shuffle("abc")), 3);

  assert_eqaul(str\reverse("abc"), 'cba');
  assert_eqaul(str\reverse(""), '');

  assert_eqaul(str\lower("ABC.1.2.3"), "abc.1.2.3");
  assert_eqaul(str\upper("abc.1.2.3"), "ABC.1.2.3");

  assert_eqaul(str\split(''), []);
  assert_eqaul(str\split('a'), ['a']);
  assert_eqaul(str\split('abc'), ['a', 'b', 'c']);

  assert_eqaul(str\split('', '', 1), []);
  assert_eqaul(str\split('a', '', 1), ['a']);
  assert_eqaul(str\split('abc', '', 1), ['abc']);
  assert_eqaul(str\split('abc', '', 2), ['a', 'bc']);
  assert_eqaul(str\split('abc', '', 3), ['a', 'b', 'c']);

  assert_eqaul(str\split('', 'b'), ['']);
  assert_eqaul(str\split('abc', 'b'), ['a', 'c']);

  assert_eqaul(str\split('abc', 'b', 1), ['abc']);
  assert_eqaul(str\split('abc', 'b', 2), ['a', 'c']);

  assert_eqaul(str\chunk('abc', 1), ['a', 'b', 'c']);
  assert_eqaul(str\chunk('abc', 2), ['ab', 'c']);
  assert_eqaul(str\chunk('abc', 3), ['abc']);

  assert_eqaul(str\join([]), '');
  assert_eqaul(str\join(['abc']), 'abc');
  assert_eqaul(str\join(['a', 'bc']), 'abc');

  assert_eqaul(str\join([], ','), '');
  assert_eqaul(str\join(['abc'], ','), 'abc');
  assert_eqaul(str\join(['a', 'bc'], ','), 'a,bc');

  assert_eqaul(str\replace('abc', 'b', 'lol'), tuple('alolc', 1));
  assert_eqaul(str\replace('abc', 'B', 'lol'), tuple('abc', 0));
  assert_eqaul(str\ireplace('abc', 'B', 'lol'), tuple('alolc', 1));

  assert_eqaul(str\splice('abc', 1, 1), 'ac');
  assert_eqaul(str\splice('abc', 1, 1, 'lol'), 'alolc');

  assert_eqaul(str\slice('abc', 1, 1), 'b');
  assert_eqaul(str\slice('abc', -1, 1), 'c');
  assert_eqaul(str\slice('abc', 1, -1), 'b');
  assert_eqaul(str\slice('abc', 1), 'bc');
  assert_eqaul(str\slice('abc', -1), 'c');

  assert_eqaul(str\pad('abc', 3), 'abc');
  assert_eqaul(str\pad('abc', 4), 'abc ');
  assert_eqaul(str\pad('abc', 5), ' abc ');
  assert_eqaul(str\pad('abc', 6), ' abc  ');
  assert_eqaul(str\pad('1', 3, 'ab'), 'a1a');
  assert_eqaul(str\pad('1', 4, 'ab'), 'a1ab');

  assert_eqaul(str\lpad('abc', 3), 'abc');
  assert_eqaul(str\lpad('abc', 4), ' abc');
  assert_eqaul(str\lpad('abc', 5), '  abc');
  assert_eqaul(str\lpad('abc', 6), '   abc');
  assert_eqaul(str\lpad('1', 3, 'ab'), 'ab1');
  assert_eqaul(str\lpad('1', 4, 'ab'), 'aba1');

  assert_eqaul(str\rpad('abc', 3), 'abc');
  assert_eqaul(str\rpad('abc', 4), 'abc ');
  assert_eqaul(str\rpad('abc', 5), 'abc  ');
  assert_eqaul(str\rpad('abc', 6), 'abc   ');
  assert_eqaul(str\rpad('1', 3, 'ab'), '1ab');
  assert_eqaul(str\rpad('1', 4, 'ab'), '1aba');

  assert_eqaul(str\repeat('123', 3), '123123123');

  assert_eqaul(str\chr(128), "\x80");
  assert_eqaul(str\chr(0), "\x00");
  assert_eqaul(str\chr(255), "\xFF");

  assert_eqaul(str\ord('a'), 97);
  assert_eqaul(str\ord('a99'), 97);

  assert_eqaul(str\cmp('a', 'a'), 0);
  assert_eqaul(str\cmp('a', 'A'), 1);
  assert_eqaul(str\cmp('', ''), 0);
  assert_eqaul(str\cmp('', 'a'), -1);
  assert_eqaul(str\cmp('a', ''), 1);

  assert_eqaul(str\icmp('a', 'a'), 0);
  assert_eqaul(str\icmp('a', 'A'), 0);
  assert_eqaul(str\icmp('', ''), 0);
  assert_eqaul(str\icmp('', 'a'), -1);
  assert_eqaul(str\icmp('a', ''), 1);

  assert_eqaul(str\eq('a', 'a'), true);
  assert_eqaul(str\eq('a', 'A'), false);
  assert_eqaul(str\eq('', ''), true);
  assert_eqaul(str\eq('', 'a'), false);
  assert_eqaul(str\eq('a', ''), false);

  assert_eqaul(str\ieq('a', 'a'), true);
  assert_eqaul(str\ieq('a', 'A'), true);
  assert_eqaul(str\ieq('', ''), true);
  assert_eqaul(str\ieq('', 'a'), false);
  assert_eqaul(str\ieq('a', ''), false);

  assert_eqaul(str\find('a', 'a'), 0);
  assert_eqaul(str\find('a', 'a', 1), null);
  assert_eqaul(str\find('a', 'a', -1), 0);
  assert_eqaul(str\find('abc', 'a'), 0);
  assert_eqaul(str\find('abc', 'b'), 1);
  assert_eqaul(str\find('abc', 'c'), 2);
  assert_eqaul(str\find('abc', 'a', -2), null);
  assert_eqaul(str\find('abc', 'b', -2), 1);
  assert_eqaul(str\find('abc', 'c', -2), 2);
  assert_eqaul(str\find('abbb', 'bb'), 1);
  assert_eqaul(str\find('abbb', 'bb', 2), 2);

  assert_eqaul(str\rfind('a', 'a'), 0);
  assert_eqaul(str\rfind('a', 'a', 1), null);
  assert_eqaul(str\rfind('a', 'a', -1), 0);
  assert_eqaul(str\rfind('aba', 'a'), 2);
  assert_eqaul(str\rfind('aba', 'b'), 1);
  assert_eqaul(str\rfind('aba', 'c'), null);
  assert_eqaul(str\rfind('aba', 'a', -2), 2);
  assert_eqaul(str\rfind('aba', 'b', -2), 1);
  assert_eqaul(str\rfind('aba', 'c', -2), null);
  assert_eqaul(str\rfind('abbb', 'bb'), 2);
  assert_eqaul(str\rfind('abbb', 'bb', 2), 2);

  assert_eqaul(str\ends_with('abbb', 'bb'), true);
  assert_eqaul(str\ends_with('abbb', 'ba'), false);
  assert_eqaul(str\ends_with('abbb', ''), true);
  assert_eqaul(str\ends_with('', ''), true);
  assert_eqaul(str\ends_with('', 'a'), false);

  assert_eqaul(str\starts_with('abbb', 'ab'), true);
  assert_eqaul(str\starts_with('abbb', 'bb'), false);
  assert_eqaul(str\starts_with('abbb', ''), true);
  assert_eqaul(str\starts_with('', ''), true);
  assert_eqaul(str\starts_with('', 'a'), false);
}

/* HH_IGNORE_ERROR[1002] */
run_tests();
