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
  $coverage = new Coverage();
  $coverage->start();

  try {
    \umask(0022);
    _run_tests();
  } finally {
    $coverage->stop();
    $coverage->write();
  }
}

tests_main();
