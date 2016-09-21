<?hh // strict

namespace HackUtils\ctype;

function isalnum(string $s, int $i = 0): bool {
  return \ctype_alnum(_char($s, $i));
}

function isblank(string $s, int $i = 0): bool {
  $c = _char($s, $i);
  return $c === "\t" || $c === " ";
}

function isapha(string $s, int $i = 0): bool {
  return \ctype_alpha(_char($s, $i));
}

function iscntrl(string $s, int $i = 0): bool {
  return \ctype_cntrl(_char($s, $i));
}

function isdigit(string $s, int $i = 0): bool {
  return \ctype_digit(_char($s, $i));
}

function isgraph(string $s, int $i = 0): bool {
  return \ctype_graph(_char($s, $i));
}

function islower(string $s, int $i = 0): bool {
  return \ctype_lower(_char($s, $i));
}

function isprint(string $s, int $i = 0): bool {
  return \ctype_print(_char($s, $i));
}

function ispunct(string $s, int $i = 0): bool {
  return \ctype_punct(_char($s, $i));
}

function isspace(string $s, int $i = 0): bool {
  return \ctype_space(_char($s, $i));
}

function isupper(string $s, int $i = 0): bool {
  return \ctype_upper(_char($s, $i));
}

function isxdigit(string $s, int $i = 0): bool {
  return \ctype_xdigit(_char($s, $i));
}

function _char(string $s, int $i): string {
  $l = \strlen($s);
  if ($i < 0) {
    $i += $l;
  }
  if ($i < 0 || $i >= $l) {
    throw new \Exception("Byte offset $i out of bounds in string '$s'");
  }
  return $s[$i];
}
