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

  public function addArray(array<T> $items): void {
    foreach ($items as $item) {
      $this->add($item);
    }
  }

  public function addAll(namespace\Collection<T> $items): void {
    $this->addArray($items->unwrap());
  }

  public function size(): int {
    return \count($this->unwrap());
  }

  public abstract function slice(
    int $offset,
    ?int $length = null,
  ): namespace\Collection<T>;

  public abstract function reverse(): namespace\Collection<T>;

  public function contains(T $value): bool {
    return vector\contains($this->unwrap(), $value);
  }

  public function indexOf(T $value): ?int {
    return vector\index_of($this->unwrap(), $value);
  }

  public function lastIndexOf(T $value): ?int {
    return vector\last_index_of($this->unwrap(), $value);
  }

  public abstract function splice(
    int $offset,
    ?int $length = null,
    array<T> $replacement = [],
  ): namespace\Collection<T>;

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

  public function first(): T {
    $this->checkEmpty('get first element');
    return $this->get(0);
  }

  public function last(): T {
    $this->checkEmpty('get last element');
    return $this->get($this->size() - 1);
  }

  public function map<T2>((function(T): T2) $f): namespace\Vector<T2> {
    return namespace\Vector::wrap(vector\map($this->unwrap(), $f));
  }

  public abstract function filter(
    (function(T): bool) $f,
  ): namespace\Collection<T>;

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
    return !$this->size();
  }

  public abstract function unwrap(): array<T>;

  public abstract function setContents(array<T> $array): void;

  protected function checkEmpty(string $op): void {
    if ($this->isEmpty()) {
      throw new \Exception("Cannot $op: Collection is empty");
    }
  }

  protected function checkBounds(int $index): void {
    $size = $this->size();
    if ($index < 0 || $index >= $size) {
      throw new \Exception(
        "Collection index $index out of bounds in collection with size $size",
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
  public function filter((function(T): bool) $f): namespace\Vector<T> {
    return new self(vector\filter($this->array, $f));
  }

  <<__Override>>
  public function reverse(): namespace\Vector<T> {
    return new self(vector\reverse($this->array));
  }

  <<__Override>>
  public function slice(
    int $offset = 0,
    ?int $length = null,
  ): namespace\Vector<T> {
    return new self(vector\slice($this->array, $offset, $length));
  }

  <<__Override>>
  public function add(T $value): void {
    $this->push($value);
  }

  <<__Override>>
  public function addArray(array<T> $array): void {
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

class Set<T as arraykey> extends namespace\Collection<T> {
  <<__Override>>
  public static function wrap(array<T> $array): namespace\Set<T> {
    return new self(\array_fill_keys($array, true));
  }

  private function __construct(private array<T, mixed> $array = []) {}

  public function addAll(namespace\Collection<T> $values): void {
    if ($values instanceof self) {
      $this->array = set\union($this->array, $values->array);
      return;
    }

    parent::addAll($values);
  }

  public function splice(
    int $offset,
    ?int $length = null,
    array<T> $replacement = [],
  ): namespace\Set<T> {
    list($this->array, $return) =
      map\splice($this->array, $offset, $length, set\create($replacement));
    return new self($return);
  }

  public function filter((function(T): bool) $f): namespace\Set<T> {
    return new self(map\filter_keys($this->array, $f));
  }

  <<__Override>>
  public function get(int $index): T {
    return pair\fst(map\get_pair($this->array, $index));
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
  public function size(): int {
    return \count($this->array);
  }

  <<__Override>>
  public function reverse(): namespace\Set<T> {
    return new self(set\reverse($this->array));
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
    return new self(map\slice($this->array, $offset, $length));
  }

  <<__Override>>
  public function setContents(array<T> $array): void {
    $this->array = set\create($array);
  }

  <<__Override>>
  public function unwrap(): array<T> {
    return set\values($this->array);
  }

  public function toArray(): array<T> {
    return $this->unwrap();
  }
}

class Map<Tk as arraykey, Tv> extends namespace\Collection<(Tk, Tv)> {
  public static function wrap(array<(Tk, Tv)> $array): namespace\Map<Tk, Tv> {
    return new self(map\from_pairs($array));
  }

  private function __construct(private array<Tk, Tv> $array = []) {}

  public function addAll(namespace\Collection<(Tk, Tv)> $items): void {
    if ($items instanceof self) {
      $this->array = map\union($this->array, $items->array);
      return;
    }

    parent::addAll($items);
  }

  public function splice(
    int $offset,
    ?int $length = 0,
    array<(Tk, Tv)> $replacement = [],
  ): namespace\Map<Tk, Tv> {
    list($this->array, $return) = map\splice(
      $this->array,
      $offset,
      $length,
      map\from_pairs($replacement),
    );
    return new self($return);
  }

  <<__Override>>
  public function filter(
    (function((Tk, Tv)): bool) $f,
  ): namespace\Map<Tk, Tv> {
    return new self(map\filter_pairs($this->array, $f));
  }

  <<__Override>>
  public function size(): int {
    return \count($this->array);
  }

  <<__Override>>
  public function reverse(): namespace\Map<Tk, Tv> {
    return new self(map\reverse($this->array));
  }

  <<__Override>>
  public function add((Tk, Tv) $pair): void {
    list($key, $value) = $pair;
    $this->array[$key] = $value;
  }

  <<__Override>>
  public function get(int $index): (Tk, Tv) {
    if ($index < 0) {
      $index += $this->size();
    }
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
    return new self(map\slice($this->array, $offset, $length));
  }

  public function assign(Tk $key, Tv $value): void {
    $this->array[$key] = $value;
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

  public function fetch(Tk $key): Tv {
    return $this->array[$key];
  }

  public function fetchOrNull(Tk $key): ?Tv {
    return map\soft_get($this->array, $key);
  }

  public function fetchOrDefault<T super Tv>(Tk $key, T $value): T {
    return map\get_default($this->array, $key, $value);
  }

  public function deleteKey(Tk $key): void {
    unset($this->array[$key]);
  }

  public function toArray(): array<Tk, Tv> {
    return $this->array;
  }

  <<__Override>>
  public function contains((Tk, Tv) $pair): bool {
    list($key, $value) = $pair;
    return $this->containsKey($key) && $this->fetch($key) === $value;
  }

  public function keys(): namespace\Vector<Tk> {
    return namespace\Vector::wrap(map\keys($this->array));
  }

  public function values(): namespace\Vector<Tv> {
    return namespace\Vector::wrap(map\values($this->array));
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

  public function mapPairs<Tk2 as arraykey, Tv2>(
    (function((Tk, Tv)): (Tk2, Tv2)) $f,
  ): namespace\Map<Tk2, Tv2> {
    return new self(map\map_pairs($this->array, $f));
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
