<?php

namespace Tests\Feature;

use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_login_page_is_available(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
