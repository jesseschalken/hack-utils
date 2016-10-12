<?hh // strict

namespace HackUtils;

type type_assert<+T> = (function(mixed): T);

final class _Cache {
  public static ?type_assert<string> $string;
  public static ?type_assert<float> $float;
  public static ?type_assert<int> $int;
  public static ?type_assert<num> $num;
  public static ?type_assert<arraykey> $arraykey;
  public static ?type_assert<bool> $bool;
  public static ?type_assert<mixed> $mixed;
  public static ?type_assert<resource> $resource;
  public static ?type_assert<array<mixed>> $mixedArray;
  public static ?type_assert<array<arraykey, mixed>> $mixedAssoc;
}

function assert_string(): type_assert<string> {
  return _Cache::$string ?: _Cache::$string =
    $x ==> \is_string($x) ? $x : _type_error($x, 'string');
}

function assert_float(): type_assert<float> {
  return _Cache::$float ?: _Cache::$float =
    $x ==> \is_float($x) ? $x : _type_error($x, 'float');
}

function assert_int(): type_assert<int> {
  return _Cache::$int ?: _Cache::$int =
    $x ==> \is_int($x) ? $x : _type_error($x, 'int');
}

function assert_bool(): type_assert<bool> {
  return _Cache::$bool ?: _Cache::$bool =
    $x ==> \is_bool($x) ? $x : _type_error($x, 'bool');
}

function assert_resource(): type_assert<resource> {
  return _Cache::$resource ?: _Cache::$resource =
    $x ==> \is_resource($x) ? $x : _type_error($x, 'bool');
}

function assert_null<T>(): type_assert<?T> {
  return $x ==> $x === null ? null : _type_error($x, 'null');
}

function assert_nullable<T>(type_assert<T> $t): type_assert<?T> {
  return $x ==> $x === null ? null : $t($x);
}

function assert_array<T>(type_assert<T> $t): type_assert<array<T>> {
  return $x ==> {
    $x =
      \is_array($x) && is_vector($x)
        ? $x
        : _type_error($x, 'array (vector-like)');
    return map($x, $t);
  };
}

function assert_assoc<Tk, Tv>(
  type_assert<Tk> $ak,
  type_assert<Tv> $av,
): type_assert<array<Tk, Tv>> {
  return $x ==> {
    $x = \is_array($x) ? $x : _type_error($x, 'array (map-like)');
    $r = [];
    foreach ($x as $k => $v) {
      $r[$ak($k)] = $av($v);
    }
    return $r;
  };
}

function assert_object<T>(classname<T> $class): type_assert<T> {
  return $x ==> $x instanceof $class ? $x : _type_error($x, $class);
}

function assert_num(): type_assert<num> {
  return _Cache::$num ?: _Cache::$num = $x ==> {
    if (\is_float($x))
      return $x;
    if (\is_int($x))
      return $x;
    return _type_error($x, 'num (int|float)');
  };
}

function assert_arraykey(): type_assert<arraykey> {
  return _Cache::$arraykey ?: _Cache::$arraykey = $x ==> {
    if (\is_string($x))
      return $x;
    if (\is_int($x))
      return $x;
    return _type_error($x, 'arraykey (int|string)');
  };
}

function assert_mixed(): type_assert<mixed> {
  return _Cache::$mixed ?: _Cache::$mixed = $x ==> $x;
}

function assert_mixed_array(): type_assert<array<mixed>> {
  return _Cache::$mixedArray ?: _Cache::$mixedArray =
    assert_array(assert_mixed());
}

function assert_mixed_assoc(): type_assert<array<arraykey, mixed>> {
  return _Cache::$mixedAssoc ?: _Cache::$mixedAssoc =
    assert_assoc(assert_arraykey(), assert_mixed());
}

function assert_shape<T>(
  (function(array<arraykey, mixed>): T) $f,
): type_assert<T> {
  $mixedAssoc = assert_mixed_assoc();
  return $x ==> $f($mixedAssoc($x));
}

function assert_pair<Ta, Tb>(
  type_assert<Ta> $a,
  type_assert<Tb> $b,
): type_assert<(Ta, Tb)> {
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
