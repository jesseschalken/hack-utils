<?hh // strict

namespace HackUtils;

function zip<Ta, Tb>(array<Ta> $a, array<Tb> $b): array<(Ta, Tb)> {
  $r = [];
  $l = min(count($a), count($b));
  for ($i = 0; $i < $l; $i++) {
    $r[] = tuple($a[$i], $b[$i]);
  }
  return $r;
}

function zip_assoc<Tk, Ta, Tb>(
  array<Tk, Ta> $a,
  array<Tk, Tb> $b,
): array<Tk, (Ta, Tb)> {
  $ret = [];
  foreach ($a as $k => $v) {
    if (key_exists($b, $k)) {
      $ret[$k] = tuple($v, $b[$k]);
    }
  }
  return $ret;
}

function unzip<Ta, Tb>(array<(Ta, Tb)> $x): (array<Ta>, array<Tb>) {
  $a = [];
  $b = [];
  foreach ($x as $p) {
    $a[] = $p[0];
    $b[] = $p[1];
  }
  return tuple($a, $b);
}

function unzip_assoc<Tk, Ta, Tb>(
  array<Tk, (Ta, Tb)> $map,
): (array<Tk, Ta>, array<Tk, Tb>) {
  $a = [];
  $b = [];
  foreach ($map as $k => $v) {
    $a[$k] = $v[0];
    $b[$k] = $v[1];
  }
  return tuple($a, $b);
}
