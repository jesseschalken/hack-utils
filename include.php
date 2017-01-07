<?php

require_once __DIR__.'/error_clear_last.php';
require_once __DIR__.'/zend_compat.php';

if (\defined('HHVM_VERSION')) {
  require_once __DIR__.'/hack/include.php';
} else {
  if (!isset($GLOBALS["HACKLIB_ROOT"])) {
    $GLOBALS["HACKLIB_ROOT"] = __DIR__.'/hacklib.php';
  }
  require_once __DIR__.'/php/include.php';
}

if (\defined('HHVM_VERSION')) {
  require_once __DIR__.'/idx_hhvm.php';
} else if (\PHP_VERSION_ID >= 70000) {
  require_once __DIR__.'/idx_php7.php';
} else {
  require_once __DIR__.'/idx_php5.php';
}
