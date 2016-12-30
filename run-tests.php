#!/usr/bin/php
<?php

namespace HackUtils;

require_once __DIR__ . '/vendor/autoload.php';

$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();
$coverage->setAddUncoveredFilesFromWhitelist(true);
$coverage->filter()->addDirectoryToWhitelist(__DIR__ . '/php');
$coverage->start('Hack Utils');

try {
    _run_tests();
} finally {
    $coverage->stop();

    $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade();
    $writer->process($coverage, __DIR__ . '/coverage');
}


