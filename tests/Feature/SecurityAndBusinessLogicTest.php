<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityAndBusinessLogicTest extends TestCase
{
    /**
     * Test that login is rate-limited after multiple failed attempts.
     */
    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $email = 'unknown_attacker@example.com';

        // Hit failed login 5 times
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should trigger rate limiting with validation error
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $errorMessage = session('errors')->first('email');
        $this->assertStringContainsString('Too many login attempts', $errorMessage);

        // Clear rate limiter for test cleanliness
        RateLimiter::clear(strtolower($email) . '|127.0.0.1');
    }

    /**
     * Test that Customer model accessors respect pre-loaded aggregate attributes to prevent N+1 queries.
     */
    public function test_customer_spend_accessor_uses_preloaded_aggregates(): void
    {
        $customer = new Customer();
        $customer->total_spend = 450.75;
        $customer->completed_visits_count = 6;

        $this->assertEquals(450.75, $customer->total_spend);
        $this->assertEquals(6, $customer->completed_visits_count);
    }

    /**
     * Test that unauthenticated users are redirected from protected operations.
     */
    public function test_unauthenticated_user_cannot_access_pos(): void
    {
        $response = $this->get('/pos');
        $response->assertRedirect('/login');
    }

    /**
     * Test that unauthenticated user cannot access staff management.
     */
    public function test_unauthenticated_user_cannot_access_staff(): void
    {
        $response = $this->get('/staff');
        $response->assertRedirect('/login');
    }
}
