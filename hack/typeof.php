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
  // if (\is_vec($x))
  //   return 'vec';
  // if (\is_dict($x))
  //   return 'dict';
  // if (\is_keyset($x))
  //   return 'keyset';
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
