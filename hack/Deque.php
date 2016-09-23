<?hh // strict

namespace HackUtils;

final class Deque<T> implements \Countable, \IteratorAggregate<T> {
  private array<T> $array = [];

  public function unshift(T $x): void {
    \array_unshift($this->array, $x);
  }

  public function push(T $x): void {
    \array_push($this->array, $x);
  }

  public function pop(): T {
    $this->checkEmpty('pop last element');
    return \array_pop($this->array);
  }

  public function shift(): T {
    $this->checkEmpty('pop first element');
    return \array_shift($this->array);
  }

  public function first(): T {
    $this->checkEmpty('get first element');
    return $this->array[0];
  }

  public function last(): T {
    $this->checkEmpty('get last element');
    return $this->array[$this->count() - 1];
  }

  public function count(): int {
    return \count($this->array);
  }

  public function isEmpty(): bool {
    return !$this->array;
  }

  public function toArray(): array<T> {
    return $this->array;
  }

  public function getIterator(): \Iterator<T> {
    return new \ArrayIterator($this->array);
  }

  private function checkEmpty(string $op): void {
    if ($this->isEmpty()) {
      throw new \Exception("Cannot $op: Deque is empty");
    }
  }
}
