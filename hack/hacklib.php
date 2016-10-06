<?hh // strict

function hacklib_cast_as_boolean(mixed $x): bool {
  return (bool) $x;
}

function hacklib_equals(mixed $a, mixed $b): bool {
  return $a == $b;
}

function hacklib_not_equals(mixed $a, mixed $b): bool {
  return $a != $b;
}

function hacklib_id(mixed $x): mixed {
  return $x;
}

function hacklib_instanceof(mixed $x, classname<stdClass> $class): bool {
  return $x instanceof $class;
}

function hacklib_nullsafe(mixed $v): mixed {
  if ($v === null) {
    return _HackLibNullObj::$instance ?: (_HackLibNullObj::$instance =
                                            new _HackLibNullObj());
  }
  return $v;
}

final class _HackLibNullObj {
  public static ?_HackLibNullObj $instance;
  public function __call(string $method, array<mixed> $arguments): mixed {
    return null;
  }
  public function __get(string $prop): mixed {
    return null;
  }
}
