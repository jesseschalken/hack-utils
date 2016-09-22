<?hh // strict

namespace HackUtils\ctype;

use function HackUtils\str\is_empty;

function alnum(string $s): bool {
  return is_empty($s) || \ctype_alnum($s);
}

function blank(string $s): bool {
  $l = \strlen($s);
  for ($i = 0; $i < $l; $i++) {
    $c = $s[$i];
    if ($c !== "\t" && $c !== " ") {
      return false;
    }
  }
  return true;
}

function alpha(string $s): bool {
  return is_empty($s) || \ctype_alpha($s);
}

function cntrl(string $s): bool {
  return is_empty($s) || \ctype_cntrl($s);
}

function digit(string $s): bool {
  return is_empty($s) || \ctype_digit($s);
}

function graph(string $s): bool {
  return is_empty($s) || \ctype_graph($s);
}

function lower(string $s): bool {
  return is_empty($s) || \ctype_lower($s);
}

function print(string $s): bool {
  return is_empty($s) || \ctype_print($s);
}

function punct(string $s): bool {
  return is_empty($s) || \ctype_punct($s);
}

function space(string $s): bool {
  return is_empty($s) || \ctype_space($s);
}

function upper(string $s): bool {
  return is_empty($s) || \ctype_upper($s);
}

function xdigit(string $s): bool {
  return is_empty($s) || \ctype_xdigit($s);
}
