<?hh // strict

namespace HackUtils;

/**
 * Prefixes characters with a backslash, including backslashes.
 */
function escape_chars(string $s, string $chars): string {
  if ($s === '')
    return '';
  $s = replace($s, '\\', '\\\\');
  $l = length($chars);
  for ($i = 0; $i < $l; $i++) {
    $c = $chars[$i];
    $s = replace($s, $c, '\\'.$c);
  }
  return $s;
}

function encode_list(array<string> $list): string {
  $r = '';
  foreach ($list as $x) {
    $r .= escape_chars($x, ';').';';
  }
  return $r;
}

function decode_list(string $s): array<string> {
  $r = [];
  $b = '';
  $e = false;
  $l = \strlen($s);
  for ($i = 0; $i < $l; $i++) {
    $c = $s[$i];
    if ($e) {
      $b .= $c;
      $e = false;
    } else if ($c === '\\') {
      $e = true;
    } else if ($c === ';') {
      $r[] = $b;
      $b = '';
    } else {
      $b .= $c;
    }
  }
  return $r;
}

function encode_map(array<arraykey, string> $map): string {
  $r = '';
  foreach ($map as $k => $v) {
    $k .= '';
    $r .= escape_chars($k, '=;').'=';
    $r .= escape_chars($v, '=;').';';
  }
  return $r;
}

function decode_map(string $s): array<arraykey, string> {
  $r = [];
  $k = null;
  $b = '';
  $l = \strlen($s);
  $e = false;
  for ($i = 0; $i < $l; $i++) {
    $c = $s[$i];
    if ($e) {
      $b .= $c;
      $e = false;
    } else if ($c === '\\') {
      $e = true;
    } else if ($c === '=') {
      // Make sure we are expecting a key
      if ($k !== null) {
        throw new \Exception('Double key');
      }
      $k = $b;
      $b = '';
    } else if ($c === ';') {
      // Make sure we are expecting a value
      if ($k === null) {
        throw new \Exception('Value without key');
      }
      $r[$k] = $b;
      $k = null;
      $b = '';
    } else {
      $b .= $c;
    }
  }
  return $r;
}
