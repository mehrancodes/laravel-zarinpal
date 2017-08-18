<?php

namespace Rasulian\ZarinPal\Test;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('zarinpal.params.merchant-id', '');
        $app['config']->set('zarinpal.params.callback-url', '');
        $app['config']->set('zarinpal.params.description', '');
        $app['config']->set('zarinpal.testing', true);


        parent::getEnvironmentSetUp($app);
    }
}