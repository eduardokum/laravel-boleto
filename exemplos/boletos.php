<?php

foreach (glob(sprintf('%s/*_boleto.php', __DIR__)) as $arquivo) {
    echo shell_exec(sprintf('php %s', $arquivo)) . PHP_EOL;
}
