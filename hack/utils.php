<?hh // strict

namespace HackUtils;

/**
 * The Hack typechecker reports "null" as "Partially type checked code.
 * Consider adding type annotations". To avoid that, you can replace it with
 * a call to this function.
 */
function new_null<T>(): ?T {
  return null;
}

/**
 * Convert a nullable value into a non-nullable value, throwing an exception
 * in the case of null.
 */
function null_throws<T>(?T $value, string $message = "Unexpected null"): T {
  if ($value === null)
    throw new \Exception($message);
  return $value;
}

function if_null<T>(?T $x, T $y): T {
  return $x === null ? $y : $x;
}

function fix_range(int $offset, ?int $length, int $total): (int, int) {
  $offset = fix_offset($offset, $total);
  $length = fix_offset($length ?? $total, $total - $offset);
  return tuple($offset, $length);
}

/**
 * Constrains an offset into a string or array of the given length,
 * with negative values measuring from the end.
 *
 *   +---+---+---+---+
 *   | a | b | c | d |
 *   +---+---+---+---+
 *   0   1   2   3   4
 *   -4  -3  -2  -1
 *
 */
function fix_offset(int $num, int $len): int {
  if ($len < 0)
    throw new \Exception("Length must be >= 0, $len given");
  if ($num < 0)
    $num += $len;
  if ($num < 0)
    return 0;
  if ($num > $len)
    return $len;
  return $num;
}

/**
 * Simple container for a value of a given type. Useful to replace PHP's
 * built in references, which are not supported in Hack.
 */
final class Ref<T> {
  public function __construct(private T $value) {}

  public function get(): T {
    return $this->value;
  }

  public function set(T $value): void {
    $this->value = $value;
  }
}
