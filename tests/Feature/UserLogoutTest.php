<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

final class UserLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function testUserLogout()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->post('api/logout');

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJsonStructure([
                'success',
                'message',
            ]);
    }

    public function testUserLogoutUnauthorized()
    {
        // This header is needed so that the we can assert the JSON structure
        $response = $this->withHeaders(['Accept' => 'application/json'])->get('api/user');

        // Middleware-based exception has their own way of handling exception
        $response
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJsonStructure(
                [
                    'exception',
                    'file',
                    'line',
                    'message',
                    'trace',
                ]
            );
    }
}
