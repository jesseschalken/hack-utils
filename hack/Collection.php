<?hh // strict

namespace HackUtils;

use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;
use HackUtils\pair;

interface IConstCollection<+T> {
  public function toArray(): array<T>;
  public function get(int $index): T;
  public function length(): int;
  public function isEmpty(): bool;

  public function slice(
    int $offset,
    ?int $length = null,
  ): IConstCollection<T>;

  public function chunk(int $size): array<IConstCollection<T>>;

  public function filter(fun1<T, bool> $f): IConstCollection<T>;
  public function map<Tr>(fun1<T, Tr> $f): array<Tr>;
  public function reduce<Tr>(fun2<Tr, T, Tr> $f, Tr $initial): Tr;

  public function contains(mixed $value): bool;
  public function containsAll(IConstCollection<mixed> $values): bool;

  public function indexOf(mixed $value): ?int;
  public function lastIndexOf(mixed $value): ?int;
}

/**
 * A collection is a list of values. The collection may impose a total or
 * partial uniqueness constraint on the values. For example, a set enforces
 * its values are unique, while a map is a collection of (key,value) pairs
 * and enforces that the keys are unique.
 */
interface ICollection<T> extends IConstCollection<T> {
  public function add(T $value): void;
  public function remove(T $value): void;
  public function retain(T $value): void;

  public function addAll(IConstCollection<T> $values): void;
  public function removeAll(IConstCollection<T> $values): void;
  public function retainAll(IConstCollection<T> $values): void;

  public function fromArray(array<T> $values): void;
  public function set(int $index, T $value): void;
  public function delete(int $index): void;
  public function clear(): void;

  public function shuffle(): void;
  public function sort(fun2<T, T, int> $cmp): void;
  public function reverse(): void;

  public function splice(
    int $offset,
    ?int $length = null,
    ?IConstCollection<T> $replacement = null,
  ): ICollection<T>;
}

interface IConstMap<+Tk, +Tv> extends IConstCollection<(Tk, Tv)> {
  public function exists(mixed $key): bool;
  public function fetch(mixed $key): Tv;
  public function fetchOrNull(mixed $key): ?Tv;
  public function fetchOrDefault<Tr super Tv>(mixed $key, Tr $default): Tr;

  public function filterValues(fun1<Tv, bool> $f): IConstMap<Tk, Tv>;
  public function filterKeys(fun1<Tk, bool> $f): IConstMap<Tk, Tv>;

  public function mapValues<Tr>(fun1<Tv, Tr> $f): array<Tr>;
  public function mapKeys<Tr>(fun1<Tk, Tr> $f): array<Tk>;

  public function reduceValues<Tr>(fun2<Tr, Tv, Tr> $f, Tr $initial): Tr;
  public function reduceKeys<Tr>(fun2<Tr, Tk, Tr> $f, Tr $initial): Tr;
}

interface IMap<Tk, Tv> extends ICollection<(Tk, Tv)>, IConstMap<Tk, Tv> {
  public function assign(Tk $key, Tv $value): void;
  public function unassign(Tk $key): void;

  public function sortValues(fun2<Tv, Tv, int> $cmp): void;
  public function sortKeys(fun2<Tk, Tk, int> $cmp): void;
}

interface IConstVector<+T> extends IConstCollection<T> {
  public function concat<T2 super T>(
    IConstCollection<T2> $values,
  ): IConstVector<T2>;
}

interface IVector<T> extends ICollection<T> {
  public function append(IConstCollection<T> $values): void;
  public function prepend(IConstCollection<T> $values): void;
  public function push(T $value): void;
  public function pop(): T;
  public function unshift(T $value): void;
  public function shift(): T;
}

final class ArrayVector<T> implements IVector<T> {
  private function __construct(private array<T> $array = []) {}

  public function clear(): void {
    $this->array = [];
  }

  public function isEmpty(): bool {
    return !$this->array;
  }

  public function add(T $value): void {
    $this->array[] = $value;
  }

  public function addAll(IConstCollection<T> $values): void {
    $this->array = vector\concat($this->array, $values->toArray());
  }

  public function chunk(int $size): array<ArrayVector<T>> {
    return vector\map(
      vector\chunk($this->array, $size),
      $chunk ==> new self($chunk),
    );
  }

  public function contains(mixed $value): bool {
    return vector\contains($this->array, $value);
  }

  public function containsAll(IConstCollection<mixed> $values): bool {
    foreach ($values->toArray() as $value) {
      if (!$this->contains($value)) {
        return false;
      }
    }
    return true;
  }

  public function delete(int $index): void {
    \array_splice($this->array, $index, 1);
  }

  public function filter(fun1<T, bool> $f): ArrayVector<T> {
    return new self(vector\filter($this->array, $f));
  }

  public function fromArray(array<T> $array): void {
    $this->array = $array;
  }

  public function get(int $index): T {
    return vector\get($this->array, $index);
  }

  public function indexOf(mixed $value): ?int {
    return vector\index_of($this->array, $value);
  }

  public function lastIndexOf(mixed $value): ?int {
    return vector\last_index_of($this->array, $value);
  }

  public function length(): int {
    return vector\length($this->array);
  }

  public function map<Tr>(fun1<T, Tr> $f): array<Tr> {
    return vector\map($this->array, $f);
  }

  public function remove(T $value): void {
    $this->array = vector\filter($this->array, $x ==> $x !== $value);
  }

  public function removeAll(IConstCollection<T> $values): void {
    $this->array = vector\filter($this->array, $x ==> !$values->contains($x));
  }

  public function retain(T $value): void {
    $this->array = vector\filter($this->array, $x ==> $x === $value);
  }

  public function retainAll(IConstCollection<T> $values): void {
    $this->array = vector\filter($this->array, $x ==> $values->contains($x));
  }

  public function reverse(): void {
    $this->array = \array_reverse($this->array);
  }

  public function set(int $index, T $value): void {
    $length = $this->length();
    if ($index < 0) {
      $index += $length;
    }
    if ($index < 0 || $index >= $length) {
      throw new \Exception(
        "Index $index out of bounds in vector of length $length",
      );
    }
    $this->array[$index] = $value;
  }

  public function shuffle(): void {
    \shuffle($this->array);
  }

  public function slice(int $offset, ?int $length = null): ArrayVector<T> {
    return new self(vector\slice($this->array, $offset, $length));
  }

  public function reduce<Tr>(fun2<Tr, T, Tr> $f, Tr $initial): Tr {
    return vector\reduce($this->array, $f, $initial);
  }

  public function sort(fun2<T, T, int> $cmp): void {
    $ok = \usort($this->array, $cmp);
    if ($ok === false) {
      throw new \Exception('usort() failed');
    }
  }

  public function splice(
    int $offset,
    ?int $length = null,
    ?IConstCollection<T> $replacement = null,
  ): ArrayVector<T> {
    return new self(
      \array_splice(
        $this->array,
        $offset,
        $length,
        $replacement ? $replacement->toArray() : [],
      ),
    );
  }

  public function toArray(): array<T> {
    return $this->array;
  }

  public function prepend(IConstCollection<T> $values): void {
    $this->array = vector\concat($values->toArray(), $this->array);
  }

  public function append(IConstCollection<T> $values): void {
    $this->array = vector\concat($this->array, $values->toArray());
  }

  public function pop(): T {
    if ($this->isEmpty()) {
      throw new \Exception('Cannot pop last element: Array is empty');
    }
    return \array_pop($this->array);
  }

  public function peek(): T {
    return $this->get(-1);
  }

  public function push(T $value): void {
    $this->array[] = $value;
  }

  public function shift(): T {
    if ($this->isEmpty()) {
      throw new \Exception('Cannot shift first element: Array is empty');
    }
    return \array_shift($this->array);
  }

  public function unshift(T $value): void {
    \array_unshift($this->array, $value);
  }
}
