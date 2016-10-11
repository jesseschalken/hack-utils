<?hh // strict

namespace HackUtils;

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
