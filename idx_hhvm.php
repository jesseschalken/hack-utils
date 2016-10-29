<?hh // strict

namespace HackUtils;

function _idx_isset<Tk, Tv>(array<Tk, Tv> $array, Tk $key, Tv $default): Tv {
  return $array[$key] ?? $default;
}

function _idx<Tk, Tv>(array<Tk, Tv> $array, Tk $key, Tv $default): Tv {
  return idx($array, $key, $default);
}
