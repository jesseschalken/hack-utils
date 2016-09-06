<?hh // strict

namespace HackUtils\tuple;

use HackUtils\vector;
use HackUtils\key;
use HackUtils\map;
use HackUtils\set;

function fst<T>((T, mixed) $t): T {
  return $t[0];
}

function snd<T>((mixed, T) $t): T {
  return $t[1];
}
