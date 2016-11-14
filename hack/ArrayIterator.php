<?hh // strict

namespace HackUtils;

/**
 * If you have code that depends on the PHP functions end(), current(), key(),
 * next(), prev(), reset() or end(), you can use this wrapper class to migrate
 * it.
 *
 * Using this class, you can:
 * - Resume iterating from the array's existing cursor position
 * - Iterate in reverse using prev()
 * - Jump to the last element using end()
 */
final class ArrayIterator<+Tk, +Tv> implements \Iterator<Tv>, \Countable {
  public function __construct(private array<Tk, Tv> $array) {}

  public function each(): ?(Tk, Tv) {
    $ret = \each($this->array);
    if ($ret === false)
      return null;
    return $ret;
  }

  public function current(): Tv {
    $ret = \current($this->array);
    // Returns FALSE if end of array, but FALSE could be a real value,
    // so we should check valid() as well, but only if we got FALSE back.
    if ($ret === false && !$this->valid())
      throw new \Exception('Cannot get value: Array is beyond last element');
    return $ret;
  }

  public function key(): Tk {
    $ret = \key($this->array);
    if ($ret === null)
      throw new \Exception('Cannot get key: Array is beyond last element');
    return $ret;
  }

  public function valid(): bool {
    return \key($this->array) !== null;
  }

  public function next(): void {
    // There's no way we can tell whether the return value of next() is
    // a real value or FALSE because we were at the end of the array without
    // making another call to valid()/key(), so just ignore the return value
    // and require the caller to call current() if they want the current value
    // instead.
    \next($this->array);
  }

  public function prev(): void {
    // See comment in next() for why the return value is ignored.
    \prev($this->array);
  }

  public function reset(): ?Tv {
    $ret = \reset($this->array);
    if ($ret === false && !$this->valid())
      return null;
    return $ret;
  }

  public function end(): ?Tv {
    $ret = \end($this->array);
    if ($ret === false && !$this->valid())
      return null;
    return $ret;
  }

  public function rewind(): void {
    $this->reset();
  }

  public function count(): int {
    return \count($this->array);
  }

  public function unwrap(): array<Tk, Tv> {
    return $this->array;
  }
}
