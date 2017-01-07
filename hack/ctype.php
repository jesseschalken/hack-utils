<?hh // strict

namespace HackUtils;

class TestCtype extends Test {
  public function run(): void {
    self::assertEqual(all_alnum(''), true);
    self::assertEqual(all_blank(''), true);
    self::assertEqual(all_alpha(''), true);
    self::assertEqual(all_cntrl(''), true);
    self::assertEqual(all_digit(''), true);
    self::assertEqual(all_graph(''), true);
    self::assertEqual(all_lower(''), true);
    self::assertEqual(all_print(''), true);
    self::assertEqual(all_punct(''), true);
    self::assertEqual(all_space(''), true);
    self::assertEqual(all_upper(''), true);
    self::assertEqual(all_xdigit(''), true);

    self::assertEqual(all_alnum("\x00"), false);
    self::assertEqual(all_blank("\x00"), false);
    self::assertEqual(all_alpha("\x00"), false);
    self::assertEqual(all_cntrl("\x00"), true);
    self::assertEqual(all_digit("\x00"), false);
    self::assertEqual(all_graph("\x00"), false);
    self::assertEqual(all_lower("\x00"), false);
    self::assertEqual(all_print("\x00"), false);
    self::assertEqual(all_punct("\x00"), false);
    self::assertEqual(all_space("\x00"), false);
    self::assertEqual(all_upper("\x00"), false);
    self::assertEqual(all_xdigit("\x00"), false);

    self::assertEqual(all_alnum("hd8whASDe1"), true);
    self::assertEqual(all_blank("   \t\t  \t"), true);
    self::assertEqual(all_alpha("jashbAUJSHYBDef"), true);
    self::assertEqual(all_cntrl("\x00\r\v\f\t\n\r"), true);
    self::assertEqual(all_digit("24976230"), true);
    self::assertEqual(all_graph("$%&'(56?@NO]^bclmn|"), true);
    self::assertEqual(all_lower("jefjwehfasd"), true);
    self::assertEqual(all_print("$%&'(56?@NO]^bclmn| "), true);
    self::assertEqual(all_punct("$%&'(?@]^|"), true);
    self::assertEqual(all_space("\r\v\f\t\n\r "), true);
    self::assertEqual(all_upper("AHBFJHEBFOIAS"), true);
    self::assertEqual(all_xdigit("98234ABCabdfDEF"), true);

    self::assertEqual(all_alnum("hd8,whASDe1"), false);
    self::assertEqual(all_blank("   \t\td  \t"), false);
    self::assertEqual(all_alpha("jashbA|UJSHYBDef"), false);
    self::assertEqual(all_cntrl("\x00\rf\v\f\t\n\r"), false);
    self::assertEqual(all_digit("24976d230"), false);
    self::assertEqual(all_graph("$%&'(5 6?@\tNO]^bclmn|"), false);
    self::assertEqual(all_lower("jefjwehfDasd"), false);
    self::assertEqual(all_print("$%&'(56?@\r\tNO]^bclmn| "), false);
    self::assertEqual(all_punct("$%&'(?@da]^|"), false);
    self::assertEqual(all_space("\r\v\f\tp\n\r "), false);
    self::assertEqual(all_upper("AHBFJHEBasdFOIAS"), false);
    self::assertEqual(all_xdigit("98234ABCDEFGHIJklm"), false);

    self::assertEqual(is_alnum("hd8,whASDe1", 3), false);
    self::assertEqual(is_blank("   \t\td  \t", 5), false);
    self::assertEqual(is_alpha("jashbA|UJSHYBDef", 6), false);
    self::assertEqual(is_cntrl("\x00\rf\v\f\t\n\r", 2), false);
    self::assertEqual(is_digit("24976d230", 5), false);
    self::assertEqual(is_graph("$%&'(5 6?@\tNO]^bclmn|", 6), false);
    self::assertEqual(is_lower("jefjwehfDasd", 8), false);
    self::assertEqual(is_print("$%&'(56?@\r\tNO]^bclmn| ", 9), false);
    self::assertEqual(is_punct("$%&'(?@da]^|", 8), false);
    self::assertEqual(is_space("\r\v\f\tp\n\r ", 4), false);
    self::assertEqual(is_upper("AHBFJHEBasdFOIAS", 10), false);
    self::assertEqual(is_xdigit("98234ABCDEFGHIJklm", 11), false);

    self::assertEqual(is_alnum("hd8,whASDe1", 2), true);
    self::assertEqual(is_blank("   \t\td  \t", 8), true);
    self::assertEqual(is_alpha("jashbA|UJSHYBDef", 2), true);
    self::assertEqual(is_cntrl("\x00\rf\v\f\t\n\r", 6), true);
    self::assertEqual(is_digit("24976d230", 3), true);
    self::assertEqual(is_graph("$%&'(5 6?@\tNO]^bclmn|", 8), true);
    self::assertEqual(is_lower("jefjwehfDasd", 5), true);
    self::assertEqual(is_print("$%&'(56?@\r\tNO]^bclmn| ", 13), true);
    self::assertEqual(is_punct("$%&'(?@da]^|", 4), true);
    self::assertEqual(is_space("\r\v\f\tp\n\r ", 6), true);
    self::assertEqual(is_upper("AHBFJHEBasdFOIAS", 4), true);
    self::assertEqual(is_xdigit("98234ABCDEFGHIJklm", 5), true);
  }
}

function all_alnum(string $s): bool {
  return $s === '' || \ctype_alnum($s);
}

function all_blank(string $s): bool {
  $l = \strlen($s);
  for ($i = 0; $i < $l; $i++) {
    $c = $s[$i];
    if ($c !== "\t" && $c !== " ") {
      return false;
    }
  }
  return true;
}

function all_alpha(string $s): bool {
  return $s === '' || \ctype_alpha($s);
}

function all_cntrl(string $s): bool {
  return $s === '' || \ctype_cntrl($s);
}

function all_digit(string $s): bool {
  return $s === '' || \ctype_digit($s);
}

function all_graph(string $s): bool {
  return $s === '' || \ctype_graph($s);
}

function all_lower(string $s): bool {
  return $s === '' || \ctype_lower($s);
}

function all_print(string $s): bool {
  return $s === '' || \ctype_print($s);
}

function all_punct(string $s): bool {
  return $s === '' || \ctype_punct($s);
}

function all_space(string $s): bool {
  return $s === '' || \ctype_space($s);
}

function all_upper(string $s): bool {
  return $s === '' || \ctype_upper($s);
}

function all_xdigit(string $s): bool {
  return $s === '' || \ctype_xdigit($s);
}

function is_alnum(string $s, int $i = 0): bool {
  return \ctype_alnum(char_at($s, $i));
}

function is_blank(string $s, int $i = 0): bool {
  $c = char_at($s, $i);
  return $c === ' ' || $c === "\t";
}

function is_alpha(string $s, int $i = 0): bool {
  return \ctype_alpha(char_at($s, $i));
}

function is_cntrl(string $s, int $i = 0): bool {
  return \ctype_cntrl(char_at($s, $i));
}

function is_digit(string $s, int $i = 0): bool {
  return \ctype_digit(char_at($s, $i));
}

function is_graph(string $s, int $i = 0): bool {
  return \ctype_graph(char_at($s, $i));
}

function is_lower(string $s, int $i = 0): bool {
  return \ctype_lower(char_at($s, $i));
}

function is_print(string $s, int $i = 0): bool {
  return \ctype_print(char_at($s, $i));
}

function is_punct(string $s, int $i = 0): bool {
  return \ctype_punct(char_at($s, $i));
}

function is_space(string $s, int $i = 0): bool {
  return \ctype_space(char_at($s, $i));
}

function is_upper(string $s, int $i = 0): bool {
  return \ctype_upper(char_at($s, $i));
}

function is_xdigit(string $s, int $i = 0): bool {
  return \ctype_xdigit(char_at($s, $i));
}
