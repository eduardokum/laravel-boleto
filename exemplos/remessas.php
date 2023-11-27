<?php

foreach (glob(sprintf('%s/*_remessa.php', __DIR__)) as $arquivo) {
    echo shell_exec(sprintf('php %s', $arquivo)) . PHP_EOL;
}
