<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Auto-login a user for every test.
     * Tests that need guest context (e.g. AuthTest) should call
     * $this->app['auth']->logout() or simply not rely on this user.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Only create & login if User table exists (avoids early boot issues)
        if (class_exists(User::class)) {
            $this->actingAs(User::factory()->create());
        }
    }
}
