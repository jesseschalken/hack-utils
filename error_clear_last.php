<?php

if (!\function_exists('error_clear_last')) {
  function error_clear_last() {
    \set_error_handler(function() {});
    try {
      \trigger_error('');
    } catch (\Exception $e) {
      \restore_error_handler();
      throw $e;
    }
    \restore_error_handler();
  }
}
