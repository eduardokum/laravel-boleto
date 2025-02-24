<?php

namespace Eduardokum\LaravelBoleto\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{

    protected function aceite()
    {
        $aValues = ['S', 'N'];
        return $aValues[rand() & 1];
    }

    protected function vencimento()
    {
        return (new \Carbon\Carbon())->addDays(rand(0, 365));
    }

    protected function valor()
    {
        return mt_rand(100, 30000);
    }

    protected function multa()
    {
        //       sem multa, decimal entre 1 e 10
        $aValues = [false, (mt_rand(10, 10 * 10) / 10)];
        return $aValues[rand() & 1];
    }

    protected function juros()
    {
        //       sem multa, decimal entre 1 e 10
        $aValues = [false, (mt_rand(10, 10 * 10) / 10)];
        return $aValues[rand() & 1];
    }
}
