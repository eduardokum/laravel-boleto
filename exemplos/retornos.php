<?php
foreach (glob(sprintf('%s/*retorno.php', __DIR__)) as $arquivo) {
    echo shell_exec(sprintf('php %s', $arquivo)) . PHP_EOL;
}
