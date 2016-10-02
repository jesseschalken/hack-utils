<?hh // strict

namespace HackUtils;

use HackUtils as utils;
use HackUtils\str;
use HackUtils\vector;
use HackUtils\map;
use HackUtils\tuple;

final class GenericMap<Tk, Tv> {
  private array<arraykey, (Tk, Tv)> $map = [];

  public function __construct(array<(Tk, Tv)> $pairs = []) {
    foreach ($pairs as $pair) {
      $this->set($pair[0], $pair[1]);
    }
  }

  public function set(Tk $k, Tv $v): void {
    $this->map[$this->toString($k)] = tuple($k, $v);
  }

  public function get(Tk $k): Tv {
    return $this->map[$this->toString($k)][1];
  }

  public function softGet(Tk $k): ?Tv {
    $pair = map\soft_get($this->map, $this->toString($k));
    return $pair ? $pair[1] : utils\new_null();
  }

  public function hasKey(Tk $k): bool {
    return map\has_key($this->map, $this->toString($k));
  }

  public function delete(Tk $k): void {
    unset($this->map[$this->toString($k)]);
  }

  public function toPairs(): array<(Tk, Tv)> {
    return map\values($this->map);
  }

  private function toString(mixed $x): string {
    if (\is_string($x)) {
      return 's'.$x;
    } else if (\is_bool($x)) {
      return 'b'.($x ? '1' : '0');
    } else if (\is_int($x)) {
      return 'i'.$x;
    } else if (\is_float($x)) {
      return 'f'.\sprintf('%.20E', $x);
    } else if (\is_resource($x)) {
      return 'r'.(int) $x;
    } else if (\is_object($x)) {
      return 'o'.\spl_object_hash($x);
    } else if (\is_null($x)) {
      return 'n';
    } else if (\is_array($x)) {
      return 'a'.$this->arrayToString($x);
    } else {
      throw new \Exception('Unhandled type: '.\gettype($x));
    }
  }

  private function arrayToString(array<arraykey, mixed> $x): string {
    $r = '';
    foreach ($x as $k => $v) {
      $k = map\fixkey($k);
      $v = $this->toString($v);
      $k = str\escape($k, '=;');
      $v = str\escape($v, '=;');
      $r .= "$k=$v;";
    }
    return $r;
  }
}
