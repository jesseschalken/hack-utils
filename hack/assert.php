<?hh // strict

namespace HackUtils;

type assertion<+T> = (function(mixed): T);

function assert_string(): assertion<string> {
  return $x ==> \is_string($x) ? $x : _type_error($x, 'string');
}

function assert_float(): assertion<float> {
  return $x ==> \is_float($x) ? $x : _type_error($x, 'float');
}

function assert_int(): assertion<int> {
  return $x ==> \is_int($x) ? $x : _type_error($x, 'int');
}

function assert_bool(): assertion<bool> {
  return $x ==> \is_bool($x) ? $x : _type_error($x, 'bool');
}

function assert_resource(): assertion<resource> {
  return $x ==> \is_resource($x) ? $x : _type_error($x, 'bool');
}

function assert_null<T>(): assertion<?T> {
  return $x ==> $x === null ? null : _type_error($x, 'null');
}

function assert_nullable<T>(assertion<T> $t): assertion<?T> {
  return $x ==> $x === null ? null : $t($x);
}

function assert_array<T>(assertion<T> $t): assertion<array<T>> {
  return $x ==> {
    $x =
      \is_array($x) && is_vector($x)
        ? $x
        : _type_error($x, 'array (vector-like)');
    return map($x, $t);
  };
}

function assert_assoc<Tk, Tv>(
  assertion<Tk> $ak,
  assertion<Tv> $av,
): assertion<array<Tk, Tv>> {
  return $x ==> {
    $x = \is_array($x) ? $x : _type_error($x, 'array (map-like)');
    $r = [];
    foreach ($x as $k => $v) {
      $r[$ak($k)] = $av($v);
    }
    return $r;
  };
}

function assert_object<T>(classname<T> $class): assertion<T> {
  return $x ==> $x instanceof $class ? $x : _type_error($x, $class);
}

function assert_num(): assertion<num> {
  return $x ==> {
    if (\is_float($x))
      return $x;
    if (\is_int($x))
      return $x;
    return _type_error($x, 'num (int|float)');
  };
}

function assert_arraykey(): assertion<arraykey> {
  return $x ==> {
    if (\is_string($x))
      return $x;
    if (\is_int($x))
      return $x;
    return _type_error($x, 'arraykey (int|string)');
  };
}

function assert_mixed(): assertion<mixed> {
  return $x ==> $x;
}

function assert_pair<Ta, Tb>(
  assertion<Ta> $a,
  assertion<Tb> $b,
): assertion<(Ta, Tb)> {
  return $x ==> \is_array($x) && \count($x) == 2 && is_vector($x)
    ? tuple($a($x[0]), $b($x[1]))
    : _type_error($x, 'pair (vector array of length 2)');
}

function _typeof(mixed $x): string {
  if (\is_int($x))
    return 'int';
  if (\is_string($x))
    return 'string';
  if (\is_null($x))
    return 'void';
  if (\is_float($x))
    return 'float';
  if (\is_object($x))
    return \get_class($x);
  if (\is_bool($x))
    return 'bool';
  if (\is_resource($x))
    return 'resource';

  if (\is_array($x)) {
    $l = \count($x);
    $i = 0;
    foreach ($x as $k => $v) {
      if ($k !== $i++) {
        return "array (map-like, size = $l)";
      }
    }
    return "array (vector-like, length = $l)";
  }

  throw new \Exception('unreachable');
}

function _type_error<T>(mixed $x, string $type): T {
  throw new AssertionFailed("Expected $type, got "._typeof($x));
}

class AssertionFailed extends \Exception {}
