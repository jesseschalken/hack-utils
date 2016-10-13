<?php

if (\defined('HHVM_VERSION')) {
  require_once __DIR__.'/hack/include.php';
} else {
  if (!isset($GLOBALS["HACKLIB_ROOT"])) {
    $GLOBALS["HACKLIB_ROOT"] = __DIR__.'/hacklib.php';
  }
  require_once __DIR__.'/php/include.php';
}
