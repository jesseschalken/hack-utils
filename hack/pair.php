<?hh // strict

namespace HackUtils\pair;

use HackUtils\vector;
use HackUtils\key;
use HackUtils\map;
use HackUtils\set;

function create<T1, T2>(T1 $a, T2 $b): (T1, T2) {
  return tuple($a, $b);
}

function fst<T>((T, mixed) $t): T {
  return $t[0];
}

function snd<T>((mixed, T) $t): T {
  return $t[1];
}

function cast(mixed $x): ?(mixed, mixed) {
  return
    !\is_array($x) || \count($x) != 2 || !vector\is_vector($x)
      ? null
      : tuple($x[0], $x[1]);
}
