<?php

namespace Rasulian\ZarinPal\Test;

use Rasulian\ZarinPal\ZarinPal;

class ZarinPalTest extends TestCase
{
    /** @test */
    public function test_it_throws_error_if_merchant_id_is_not_set_up()
    {
        // Make the merchant id empty
        $this->app['config']->set('zarinpal.params.merchant-id', '');

        $content = (new ZarinPal)->request(1000, [], 'callback', 'description');

        // No time already
    }
}