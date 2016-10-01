<?hh // strict

namespace HackUtils;

use HackUtils\map;

final class AssocArrayRef<Tk as arraykey, Tv> {
  public function __construct(private array<Tk, Tv> $array) {}

  public function get(Tk $key): Tv {
    return $this->array[$key];
  }

  public function getDefault<Tres super Tv>(Tk $key, Tres $default): Tres {
    return map\get_default($this->array, $key, $default);
  }

  public function set(Tk $key, Tv $value): void {
    $this->array[$key] = $value;
  }

  public function softGet(Tk $key): ?Tv {
    return map\soft_get($this->array, $key);
  }

  public function hasKey(Tk $key): bool {
    return map\has_key($this->array, $key);
  }

  public function delete(Tk $key): void {
    unset($this->array[$key]);
  }

  public function unwrap(): array<Tk, Tv> {
    return $this->array;
  }

  public function shuffle(): void {
    \shuffle($this->array);
  }

  public function sort((function(Tv, Tv): int) $cmp): void {
    $ret = \uasort($this->array, $cmp);
    if ($ret === false) {
      throw new \Exception('uasort() failed');
    }
  }

  public function sortKeys((function(Tk, Tk): int) $cmp): void {
    $ret = \uksort($this->array, $cmp);
    if ($ret === false) {
      throw new \Exception('uksort() failed');
    }
  }

  public function map<Tout>((function(Tv): Tout) $f): AssocArrayRef<Tk, Tout> {
    return new self(map\map($this->array, $f));
  }

  public function filter((function(Tv): bool) $f): AssocArrayRef<Tk, Tv> {
    return new self(map\filter($this->array, $f));
  }

  public function reduce<Tout>(
    (function(Tout, Tv): Tout) $f,
    Tout $initial,
  ): Tout {
    return map\reduce($this->array, $f, $initial);
  }

  public function isEmpty(): bool {
    return !$this->array;
  }

  public function size(): int {
    return \count($this->array);
  }

  public function contains(Tv $value): bool {
    return map\contains($this->array, $value);
  }

  public function import(array<Tk, Tv> $array): void {
    $this->array = array_replace($this->array, $array);
  }

  public function union(array<Tk, Tv> $array): AssocArrayRef<Tk, Tv> {
    $self = clone $this;
    $self->import($array);
    return $self;
  }

  public function find(Tv $value): ?Tk {
    return map\find($this->array, $value);
  }

  public function slice(
    int $offset = 0,
    ?int $length = null,
  ): AssocArrayRef<Tk, Tv> {
    return new self(map\slice($this->array, $offset, $length));
  }

  public function toPairs(): array<(Tk, Tv)> {
    return map\to_pairs($this->array);
  }

  public function values(): array<Tv> {
    return map\values($this->array);
  }

  public function keys(): array<Tk> {
    return map\keys($this->array);
  }
}
