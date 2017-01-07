<?hh // strict

namespace HackUtils;

function typeof(mixed $x): string {
  if (\is_int($x))
    return 'int';
  if (\is_string($x))
    return 'string';
  if (\is_float($x))
    return 'float';
  if (\is_null($x))
    return 'null';
  if (\is_bool($x))
    return 'bool';
  if (\is_resource($x))
    return 'resource';
  if (\is_vec($x))
    return 'vec';
  if (\is_dict($x))
    return 'dict';
  if (\is_keyset($x))
    return 'keyset';
  if (\is_array($x))
    return 'array';
  // HHVM returns something like "Closure$The\Namesapce\outer_function#4;9" for
  // get_class() on Closures. While that's useful and all, it's not what Zend
  // does, so we better return 'Closure' for compatibility.
  if ($x instanceof \Closure)
    return 'Closure';
  if (\is_object($x))
    return \get_class($x);
  unreachable();
}

function dump(mixed $x): string {
  if (\is_int($x))
    return (string) $x;
  if (\is_bool($x))
    return $x ? 'true' : 'false';
  if (\is_resource($x))
    return \get_resource_type($x).' resource';
  if (\is_object($x))
    return \get_class($x);

  if (\is_string($x)) {
    $s = '';
    $l = min(\strlen($x), 100);
    for ($i = 0; $i < $l; $i++) {
      $c = $x[$i];
      $o = \ord($c);
      if ($c === "\r")
        $s .= '\r'; else if ($c === "\v")
        $s .= '\v'; else if ($c === "\\")
        $s .= '\\\\'; else if ($c === "\"")
        $s .= '\"'; else if ($c === "\$")
        $s .= '\$'; else if ($c === "\f")
        $s .= '\f'; else if ($o < 32 || $o >= 127)
        $s .= '\x'.pad_left(\dechex($o), 2, '0'); else
        $s .= $c;
    }
    $s = "\"$s\"";
    if ($l < \strlen($s))
      $s .= '...';
    return $s;
  }

  if (\is_float($x)) {
    $s = (string) $x;
    // Make sure there is a decimal point or it is in scientific format
    // otherwise it will look like an int.
    if (find($s, '.') === null &&
        find($s, 'e') === null &&
        find($s, 'E') === null) {
      $s .= '.0';
    }
    return $s;
  }

  if (\is_keyset($x))
    return 'keyset['.dump_iterable_contents($x, false).']';
  if (\is_vec($x))
    return 'vec['.dump_iterable_contents($x, false).']';
  if (\is_dict($x))
    return 'dict['.dump_iterable_contents($x, true).']';
  if (\is_array($x))
    return '['.dump_iterable_contents($x, is_assoc($x)).']';
  if ($x instanceof Map)
    return 'Map {'.dump_iterable_contents($x, true).'}';
  if ($x instanceof Set)
    return 'Set {'.dump_iterable_contents($x, false).'}';
  if ($x instanceof Vector)
    return 'Vector {'.dump_iterable_contents($x, false).'}';

  return typeof($x);
}

function dump_iterable_contents(
  KeyedTraversable<mixed, mixed> $x,
  bool $assoc,
): string {
  $p = [];
  foreach ($x as $k => $v) {
    if (\count($p) >= 3) {
      $p[] = '...';
      break;
    }
    $s = '';
    if ($assoc)
      $s .= dump($k).' => ';
    $s .= dump($k);
    $p[] = $s;
  }
  return join($p, ', ');
}
