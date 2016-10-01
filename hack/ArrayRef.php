<?hh // strict

namespace HackUtils;

use HackUtils\vector;
use HackUtils\map;
use HackUtils as utils;

final class ArrayRef<T> {
  public function __construct(private array<T> $array = []) {}

  public function get(int $i): T {
    $this->checkBounds($i);
    return $this->array[$i];
  }

  public function set(int $i, T $v): void {
    $this->checkBounds($i);
    $this->array[$i] = $v;
  }

  public function append(array<T> $array): void {
    // Would this be more efficient as a loop pushing each element?
    $this->array = vector\concat($this->array, $array);
  }

  public function prepend(array<T> $array): void {
    $this->array = vector\concat($array, $this->array);
  }

  public function concat(array<T> $array): ArrayRef<T> {
    $self = clone $this;
    $self->append($array);
    return $self;
  }

  public function indexOf(T $value): ?int {
    return vector\index_of($this->array, $value);
  }

  public function keys(): array<int> {
    return vector\keys($this->array);
  }

  public function contains(T $value): bool {
    return vector\contains($this->array, $value);
  }

  public function unshift(T $x): int {
    return \array_unshift($this->array, $x);
  }

  public function push(T $x): int {
    return \array_push($this->array, $x);
  }

  public function pop(): T {
    $this->checkEmpty('pop last element');
    return \array_pop($this->array);
  }

  public function peek(): T {
    return $this->last();
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
    return $this->array[$this->length() - 1];
  }

  public function length(): int {
    return vector\length($this->array);
  }

  public function isEmpty(): bool {
    return !$this->array;
  }

  public function unwrap(): array<T> {
    return $this->array;
  }

  public function sort((function(T, T): int) $cmp): void {
    $ret = \usort($this->array, $cmp);
    if ($ret === false) {
      throw new \Exception('usort() failed');
    }
  }

  public function shuffle(): void {
    $ret = \shuffle($this->array);
    if ($ret === false) {
      throw new \Exception('shuffle() failed');
    }
  }

  public function map<Tout>((function(T): Tout) $f): ArrayRef<Tout> {
    return new self(vector\map($this->array, $f));
  }

  public function filter((function(T): bool) $f): ArrayRef<T> {
    return new self(vector\filter($this->array, $f));
  }

  public function reduce<Tout>(
    (function(Tout, T): Tout) $f,
    Tout $initial,
  ): Tout {
    return vector\reduce($this->array, $f, $initial);
  }

  public function reduceRight<Tout>(
    (function(Tout, T): Tout) $f,
    Tout $initial,
  ): Tout {
    return vector\reduce_right($this->array, $f, $initial);
  }

  public function reverse(): void {
    $this->array = vector\reverse($this->array);
  }

  public function slice(int $offset, ?int $length = null): ArrayRef<T> {
    return new self(vector\slice($this->array, $offset, $length));
  }

  public function splice(
    int $offset,
    ?int $length = null,
    array<T> $replacement = [],
  ): array<T> {
    return \array_splice($this->array, $offset, $length, $replacement);
  }

  private function checkEmpty(string $op): void {
    if ($this->isEmpty()) {
      throw new \Exception("Cannot $op: Array is empty");
    }
  }

  private function checkBounds(int $index): void {
    $length = $this->length();
    if ($index < 0 || $index >= $length) {
      throw new \Exception(
        "Array index $index out of bounds in array with length $length",
      );
    }
  }
}
