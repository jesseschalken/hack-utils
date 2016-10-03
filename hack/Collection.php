<?hh // strict

namespace HackUtils;

use HackUtils\vector;
use HackUtils\map;
use HackUtils\set;
use HackUtils\pair;

abstract class Collection<T> {
  public static abstract function wrap(
    array<T> $array,
  ): namespace\Collection<T>;

  public abstract function add(T $item): void;

  public function addAll(array<T> $items): void {
    foreach ($items as $item) {
      $this->add($item);
    }
  }

  public final function size(): int {
    return $this->length();
  }

  public function length(): int {
    return \count($this->unwrap());
  }

  public function slice(
    int $offset,
    ?int $length = null,
  ): namespace\Collection<T> {
    return static::wrap(vector\slice($this->unwrap(), $offset, $length));
  }

  public function reverse(): namespace\Collection<T> {
    return static::wrap(vector\reverse($this->unwrap()));
  }

  public function contains(T $value): bool {
    return vector\contains($this->unwrap(), $value);
  }

  public function indexOf(T $value): ?int {
    return vector\index_of($this->unwrap(), $value);
  }

  public function lastIndexOf(T $value): ?int {
    return vector\last_index_of($this->unwrap(), $value);
  }

  public function splice(
    int $offset,
    ?int $length = null,
    array<T> $replacement = [],
  ): namespace\Collection<T> {
    $array = $this->unwrap();
    $result = \array_splice($array, $offset, $length, $replacement);
    $this->setContents($array);
    return static::wrap($result);
  }

  public function shuffle(): void {
    $array = $this->unwrap();
    $okay = \shuffle($array);
    if ($okay === false) {
      throw new \Exception('shuffle() failed');
    }
    $this->setContents($array);
  }

  public function sort((function(T, T): int) $cmp): void {
    $array = $this->unwrap();
    $okay = \usort($array, $cmp);
    if ($okay === false) {
      throw new \Exception('usort() failed');
    }
    $this->setContents($array);
  }

  public function clear(): void {
    $this->setContents([]);
  }

  public function get(int $index): T {
    $this->checkBounds($index);
    $array = $this->unwrap();
    return $array[$index];
  }

  public function set(int $index, T $value): void {
    $this->checkBounds($index);
    $array = $this->unwrap();
    $array[$index] = $value;
    $this->setContents($array);
  }

  public function delete(int $index): void {
    $this->checkBounds($index);
    $array = $this->unwrap();
    \array_splice($array, $index, 1);
    $this->setContents($array);
  }

  public function remove(T $value): void {
    $this->setContents(\array_filter($this->unwrap(), $x ==> $x !== $value));
  }

  public function skip(int $num): namespace\Collection<T> {
    return $this->slice($num);
  }

  public function take(int $num): namespace\Collection<T> {
    return $this->slice(0, $num);
  }

  public function first(): T {
    $this->checkEmpty('get first element');
    return $this->get(0);
  }

  public function last(): T {
    $this->checkEmpty('get last element');
    return $this->get($this->length() - 1);
  }

  public function map<T2>((function(T): T2) $f): namespace\Vector<T2> {
    return namespace\Vector::wrap(vector\map($this->unwrap(), $f));
  }

  public function filter((function(T): bool) $f): namespace\Collection<T> {
    return static::wrap(vector\filter($this->unwrap(), $f));
  }

  public function reduce<Tout>(
    (function(Tout, T): Tout) $f,
    Tout $initial,
  ): Tout {
    return vector\reduce($this->unwrap(), $f, $initial);
  }

  public function reduceRight<Tout>(
    (function(Tout, T): Tout) $f,
    Tout $initial,
  ): Tout {
    return vector\reduce_right($this->unwrap(), $f, $initial);
  }

  public function isEmpty(): bool {
    return !$this->length();
  }

  public abstract function unwrap(): array<T>;

  public abstract function setContents(array<T> $array): void;

  protected function checkEmpty(string $op): void {
    if ($this->isEmpty()) {
      throw new \Exception("Cannot $op: Collection is empty");
    }
  }

  protected function checkBounds(int $index): void {
    $length = $this->length();
    if ($index < 0 || $index >= $length) {
      throw new \Exception(
        "Collection index $index out of bounds in collection with length $length",
      );
    }
  }
}

class Vector<T> extends namespace\Collection<T> {
  <<__Override>>
  public static function wrap(array<T> $array): namespace\Vector<T> {
    return new self($array);
  }

  private function __construct(private array<T> $array = []) {}

  <<__Override>>
  public function add(T $value): void {
    $this->push($value);
  }

  <<__Override>>
  public function addAll(array<T> $array): void {
    $this->append($array);
  }

  public function concat(array<T> $array): namespace\Vector<T> {
    $self = clone $this;
    $self->append($array);
    return $self;
  }

  public function append(array<T> $array): void {
    // Would this be more efficient as a loop pushing each element?
    $this->array = vector\concat($this->array, $array);
  }

  public function prepend(array<T> $array): void {
    $this->array = vector\concat($array, $this->array);
  }

  <<__Override>>
  public function splice(
    int $offset,
    ?int $length = null,
    array<T> $replacement = [],
  ): namespace\Vector<T> {
    return static::wrap(
      \array_splice($this->array, $offset, $length, $replacement),
    );
  }

  <<__Override>>
  public function set(int $index, T $value): void {
    $this->checkBounds($index);
    $this->array[$index] = $value;
  }

  <<__Override>>
  public function get(int $index): T {
    $this->checkBounds($index);
    return $this->array[$index];
  }

  <<__Override>>
  public function shuffle(): void {
    $okay = \shuffle($this->array);
    if ($okay === false) {
      throw new \Exception('shuffle() failed');
    }
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

  <<__Override>>
  public function sort((function(T, T): int) $cmp): void {
    $okay = \usort($this->array, $cmp);
    if ($okay === false) {
      throw new \Exception('usort() failed');
    }
  }

  <<__Override>>
  public function setContents(array<T> $contents): void {
    $this->array = $contents;
  }

  <<__Override>>
  public function unwrap(): array<T> {
    return $this->array;
  }
}

class Set<T> extends namespace\Collection<T> {
  <<__Override>>
  public static function wrap(array<T> $array): namespace\Set<T> {
    return new self(\array_fill_keys($array, true));
  }

  private function __construct(private array<T, mixed> $array = []) {}

  <<__Override>>
  public function get(int $index): T {
    $this->checkBounds($index);
    // Slicing is probably the fastest/easiest way to get the key at a given
    // index
    $piece = \array_slice($this->array, $index, 1, true);
    foreach ($piece as $k => $v) {
      return $k;
    }
    throw new \Exception();
  }

  <<__Override>>
  public function add(T $value): void {
    $this->array[$value] = true;
  }

  <<__Override>>
  public function remove(T $value): void {
    unset($this->array[$value]);
  }

  <<__Override>>
  public function contains(T $value): bool {
    return \array_key_exists($value, $this->array);
  }

  <<__Override>>
  public function length(): int {
    return \count($this->array);
  }

  <<__Override>>
  public function reverse(): namespace\Set<T> {
    return new self(\array_reverse($this->array, true));
  }

  <<__Override>>
  public function sort((function(T, T): int) $cmp): void {
    $okay = \uksort($this->array, $cmp);
    if ($okay === false) {
      throw new \Exception('oksort() failed');
    }
  }

  <<__Override>>
  public function slice(int $offset, ?int $length = null): namespace\Set<T> {
    return new self(\array_slice($this->array, $offset, $length, true));
  }

  <<__Override>>
  public function setContents(array<T> $array): void {
    $this->array = \array_fill_keys($array, true);
  }

  <<__Override>>
  public function unwrap(): array<T> {
    return \array_keys($this->array);
  }
}

class Map<Tk, Tv> extends namespace\Collection<(Tk, Tv)> {
  public static function wrap(array<(Tk, Tv)> $array): namespace\Map<Tk, Tv> {
    return new self(map\from_pairs($array));
  }

  private function __construct(private array<Tk, Tv> $array = []) {}

  <<__Override>>
  public function length(): int {
    return \count($this->array);
  }

  <<__Override>>
  public function reverse(): namespace\Map<Tk, Tv> {
    return new self(\array_reverse($this->array));
  }

  <<__Override>>
  public function add((Tk, Tv) $pair): void {
    list($key, $value) = $pair;
    $this->array[$key] = $value;
  }

  <<__Override>>
  public function get(int $index): (Tk, Tv) {
    $this->checkBounds($index);
    $piece = map\slice($this->array, $index, 1);
    foreach ($piece as $k => $v) {
      return tuple($k, $v);
    }
    throw new \Exception();
  }

  <<__Override>>
  public function slice(
    int $offset,
    ?int $length = null,
  ): namespace\Map<Tk, Tv> {
    return new self(\array_slice($this->array, $offset, $length));
  }

  public function firstKey(): Tk {
    return pair\fst($this->first());
  }

  public function firstValue(): Tv {
    return pair\snd($this->first());
  }

  public function lastKey(): Tk {
    return pair\fst($this->last());
  }

  public function lastValue(): Tv {
    return pair\snd($this->last());
  }

  public function containsKey(Tk $key): bool {
    return \array_key_exists($key, $this->array);
  }

  public function containsValue(Tv $value): bool {
    return \in_array($value, $this->array, true);
  }

  public function lookup(Tk $key): Tv {
    return $this->array[$key];
  }

  public function lookupSoft(Tk $key): ?Tv {
    return map\soft_get($this->array, $key);
  }

  public function lookupDefault<T super Tv>(Tk $key, T $value): T {
    return map\get_default($this->array, $key, $value);
  }

  <<__Override>>
  public function contains((Tk, Tv) $pair): bool {
    list($key, $value) = $pair;
    return $this->containsKey($key) && $this->lookup($key) === $value;
  }

  public function keys(): array<Tk> {
    return map\keys($this->array);
  }

  public function values(): array<Tv> {
    return map\values($this->array);
  }

  public function unwrap(): array<(Tk, Tv)> {
    return map\to_pairs($this->array);
  }

  public function sortKeys((function(Tk, Tk): int) $cmp): void {
    $okay = \uksort($this->array, $cmp);
    if ($okay === false) {
      throw new \Exception('uksort() failed');
    }
  }

  public function sortValues((function(Tv, Tv): int) $cmp): void {
    $okay = \uasort($this->array, $cmp);
    if ($okay === false) {
      throw new \Exception('uasort() failed');
    }
  }

  public function setContents(array<(Tk, Tv)> $pairs): void {
    $this->array = map\from_pairs($pairs);
  }

  public function mapValues<Tout>(
    (function(Tv): Tout) $f,
  ): namespace\Map<Tk, Tout> {
    return new self(map\map($this->array, $f));
  }

  public function filterValues(
    (function(Tv): bool) $f,
  ): namespace\Map<Tk, Tv> {
    return new self(map\filter($this->array, $f));
  }

  public function reduceValues<Tout>(
    (function(Tout, Tv): Tout) $f,
    Tout $initial,
  ): Tout {
    return map\reduce($this->array, $f, $initial);
  }
}
