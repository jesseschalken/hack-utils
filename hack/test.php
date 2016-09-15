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

  assert_eqaul(str\length(str\shuffle("abc")), 3);

  assert_eqaul(str\reverse("abc"), 'cba');
  assert_eqaul(str\reverse(""), '');

  assert_eqaul(str\to_lower("ABC.1.2.3"), "abc.1.2.3");
  assert_eqaul(str\to_upper("abc.1.2.3"), "ABC.1.2.3");

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

  assert_eqaul(str\pad_left('abc', 3), 'abc');
  assert_eqaul(str\pad_left('abc', 4), ' abc');
  assert_eqaul(str\pad_left('abc', 5), '  abc');
  assert_eqaul(str\pad_left('abc', 6), '   abc');
  assert_eqaul(str\pad_left('1', 3, 'ab'), 'ab1');
  assert_eqaul(str\pad_left('1', 4, 'ab'), 'aba1');

  assert_eqaul(str\pad_right('abc', 3), 'abc');
  assert_eqaul(str\pad_right('abc', 4), 'abc ');
  assert_eqaul(str\pad_right('abc', 5), 'abc  ');
  assert_eqaul(str\pad_right('abc', 6), 'abc   ');
  assert_eqaul(str\pad_right('1', 3, 'ab'), '1ab');
  assert_eqaul(str\pad_right('1', 4, 'ab'), '1aba');

  assert_eqaul(str\repeat('123', 3), '123123123');

  assert_eqaul(str\from_code(128), "\x80");
  assert_eqaul(str\from_code(0), "\x00");
  assert_eqaul(str\from_code(255), "\xFF");

  assert_eqaul(str\get_code_at('a'), 97);
  assert_eqaul(str\get_code_at('a99'), 97);

  assert_eqaul(str\compare('a', 'a'), 0);
  assert_eqaul(str\compare('a', 'A'), 1);
  assert_eqaul(str\compare('', ''), 0);
  assert_eqaul(str\compare('', 'a'), -1);
  assert_eqaul(str\compare('a', ''), 1);

  assert_eqaul(str\icompare('a', 'a'), 0);
  assert_eqaul(str\icompare('a', 'A'), 0);
  assert_eqaul(str\icompare('', ''), 0);
  assert_eqaul(str\icompare('', 'a'), -1);
  assert_eqaul(str\icompare('a', ''), 1);

  assert_eqaul(str\equal('a', 'a'), true);
  assert_eqaul(str\equal('a', 'A'), false);
  assert_eqaul(str\equal('', ''), true);
  assert_eqaul(str\equal('', 'a'), false);
  assert_eqaul(str\equal('a', ''), false);

  assert_eqaul(str\iequal('a', 'a'), true);
  assert_eqaul(str\iequal('a', 'A'), true);
  assert_eqaul(str\iequal('', ''), true);
  assert_eqaul(str\iequal('', 'a'), false);
  assert_eqaul(str\iequal('a', ''), false);

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

  assert_eqaul(str\find_last('a', 'a'), 0);
  assert_eqaul(str\find_last('a', 'a', 1), null);
  assert_eqaul(str\find_last('a', 'a', -1), 0);
  assert_eqaul(str\find_last('aba', 'a'), 2);
  assert_eqaul(str\find_last('aba', 'b'), 1);
  assert_eqaul(str\find_last('aba', 'c'), null);
  assert_eqaul(str\find_last('aba', 'a', -2), 2);
  assert_eqaul(str\find_last('aba', 'b', -2), 1);
  assert_eqaul(str\find_last('aba', 'c', -2), null);
  assert_eqaul(str\find_last('abbb', 'bb'), 2);
  assert_eqaul(str\find_last('abbb', 'bb', 2), 2);

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
