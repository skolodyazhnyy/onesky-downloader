#!/usr/bin/env php
<?php

require_once __DIR__ . "/vendor/autoload.php";

use Seven\OneskyDownloader\Compiler;

$compiler = new Compiler();
$compiler->compile();
