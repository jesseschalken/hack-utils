<?hh // strict

namespace HackUtils;

class TestArrayIterator extends Test {
  public function run(): void {
    $a = new ArrayIterator(['a' => 1, 'b' => 2]);

    self::assertEqual($a->count(), 2);
    self::assertEqual($a->unwrap(), ['a' => 1, 'b' => 2]);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->key(), 'a');
    self::assertEqual($a->current(), 1);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->each(), ['a', 1]);

    self::assertEqual($a->prev(), 1);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->each(), ['a', 1]);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->each(), ['b', 2]);

    self::assertEqual($a->valid(), false);
    self::assertEqual($a->each(), NULL_INT);

    // prev() on an invalid iterator does nothing
    self::assertEqual($a->prev(), NULL_INT);

    self::assertEqual($a->valid(), false);
    self::assertEqual($a->each(), NULL_INT);

    self::assertEqual($a->reset(), 1);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->each(), ['a', 1]);

    self::assertEqual($a->end(), 2);

    self::assertEqual($a->valid(), true);
    self::assertEqual($a->each(), ['b', 2]);

    self::assertEqual($a->valid(), false);
    self::assertEqual($a->each(), NULL_INT);

    self::assertEqual(
      self::getException(
        function() use ($a) {
          $a->current();
        },
      )->getMessage(),
      'Cannot get value: Array is beyond last element',
    );

    self::assertEqual(
      self::getException(
        function() use ($a) {
          $a->key();
        },
      )->getMessage(),
      'Cannot get key: Array is beyond last element',
    );

    $a = new ArrayIterator([]);
    self::assertEqual($a->count(), 0);
    self::assertEqual($a->unwrap(), []);
    self::assertEqual($a->reset(), NULL_INT);
    self::assertEqual($a->end(), NULL_INT);

    $a = new ArrayIterator(['foot', 'bike', 'car', 'plane']);
    self::assertEqual($a->current(), 'foot');
    self::assertEqual($a->next(), 'bike');
    self::assertEqual($a->next(), 'car');
    self::assertEqual($a->prev(), 'bike');
    self::assertEqual($a->end(), 'plane');
  }
}

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
final class ArrayIterator<+Tk, +Tv> {
  public function __construct(private array<Tk, Tv> $array) {}

  public function each(): ?(Tk, Tv) {
    $ret = \each($this->array);
    if ($ret === false)
      return null;
    return tuple($ret[0], $ret[1]);
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

  public function next(): ?Tv {
    $ret = \next($this->array);
    if ($ret === false && !$this->valid())
      return null;
    return $ret;
  }

  public function prev(): ?Tv {
    $ret = \prev($this->array);
    if ($ret === false && !$this->valid())
      return null;
    return $ret;
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

  public function count(): int {
    return \count($this->array);
  }

  public function unwrap(): array<Tk, Tv> {
    return $this->array;
  }
}
