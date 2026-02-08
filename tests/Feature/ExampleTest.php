<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_guest_diarahkan_ke_halaman_login_dari_root(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
