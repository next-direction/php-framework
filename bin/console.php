<?php

use NextDirection\Framework\Console\Console;

require_once('vendor/autoload.php');

$console = new Console();
exit($console->run($_SERVER['argv']));
