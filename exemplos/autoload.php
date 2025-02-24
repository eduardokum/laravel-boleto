<?php

if (file_exists(realpath(__DIR__ . '/../../../') . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require 'autoload.php';
} elseif (file_exists(realpath(__DIR__ . '/../vendor/') . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require realpath(__DIR__ . '/../vendor/') . DIRECTORY_SEPARATOR . 'autoload.php';
} else {
    trigger_error('autoload.php não localizado');
}
