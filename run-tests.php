<?php

namespace HackUtils;

require_once __DIR__ . '/vendor/autoload.php';

class Coverage {
  private $coverage;
  public function __construct() {
    $this->coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();
    $this->coverage->setAddUncoveredFilesFromWhitelist(true);
    $this->coverage->filter()->addDirectoryToWhitelist(SRC_DIR);
  }
  public function start() {
    $this->coverage->start('Hack Utils');
  }
  public function stop() {
    $this->coverage->stop();
  }
  public function write() {
    $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade();
    $writer->process($this->coverage, __DIR__ . '/coverage');
  }
}

function tests_main() {
  \date_default_timezone_set('UTC');
  \umask(0022);
  \error_reporting(-1);
  \ini_set('log_errors', '0');
  \ini_set('display_errors', '1');
  \ini_set('html_errors', '0');

  $coverage = new Coverage();
  $coverage->start();

  try {
    _Tests::main();
  } finally {
    $coverage->stop();
  }
  $coverage->write();
}

tests_main();
