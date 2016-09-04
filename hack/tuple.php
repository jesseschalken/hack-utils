<?hh // strict

namespace HackUtils\tuple;

use HackUtils\list;
use HackUtils\key;
use HackUtils\map;
use HackUtils\set;

function fst<T>((T, mixed) $t): T {
  return $t[0];
}

function snd<T>((mixed, T) $t): T {
  return $t[1];
}

function zip<Ta, Tb>(list<Ta> $a, list<Tb> $b): list<(Ta, Tb)> {
  $r = [];
  $l = min(count($a), count($b));
  for ($i = 0; $i < $l; $i++) {
    $r[] = tuple($a[$i], $b[$i]);
  }
  return $r;
}

function unzip<Ta, Tb>(list<(Ta, Tb)> $x): (list<Ta>, list<Tb>) {
  $a = [];
  $b = [];
  foreach ($x as $p) {
    $a[] = $p[0];
    $b[] = $p[1];
  }
  return tuple($a, $b);
}
