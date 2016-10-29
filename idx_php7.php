<?php

namespace HackUtils;

function _idx_isset($array, $key, $default) {
  return $array[$key] ?? $default;
}

function _idx($array, $key, $default) {
  // If the default is null we can do one lookup using "??"
  if ($default === null) {
    return $array[$key] ?? null;
  }
  return \array_key_exists($key, $array) ? $array[$key] : $default;
}
