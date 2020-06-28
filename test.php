<?php

use TorneLIB\Module\Database\Drivers\MySQL;

require_once('vendor/autoload.php');

(new MySQL())->connect(
    'manual',
    null,
    '127.0.0.1',
    sprintf('fail%s', sha1(uniqid('', true))),
    'tornelib1337'
);
