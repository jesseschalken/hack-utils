<?php

namespace HackUtils;

function _idx_isset($array, $key, $default) {
  return isset($array[$key]) ? $array[$key] : $default;
}

function _idx($array, $key, $default) {
  return \array_key_exists($key, $array) ? $array[$key] : $default;
}
