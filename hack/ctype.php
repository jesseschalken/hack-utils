<?hh // strict

namespace HackUtils\ctype;

use function HackUtils\str\is_empty;

function all_alnum(string $s): bool {
  return is_empty($s) || \ctype_alnum($s);
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
  return is_empty($s) || \ctype_alpha($s);
}

function all_cntrl(string $s): bool {
  return is_empty($s) || \ctype_cntrl($s);
}

function all_digit(string $s): bool {
  return is_empty($s) || \ctype_digit($s);
}

function all_graph(string $s): bool {
  return is_empty($s) || \ctype_graph($s);
}

function all_lower(string $s): bool {
  return is_empty($s) || \ctype_lower($s);
}

function all_print(string $s): bool {
  return is_empty($s) || \ctype_print($s);
}

function all_punct(string $s): bool {
  return is_empty($s) || \ctype_punct($s);
}

function all_space(string $s): bool {
  return is_empty($s) || \ctype_space($s);
}

function all_upper(string $s): bool {
  return is_empty($s) || \ctype_upper($s);
}

function all_xdigit(string $s): bool {
  return is_empty($s) || \ctype_xdigit($s);
}
