<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

final class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testUserProfile()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->get('api/user');

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJsonStructure([
                'success',
                'message',
                'data' => [
                    'user'
                ]
            ]);

        $responseData = $response->json();
        $this->assertNotEmpty($responseData['data']['user'], 'User data should not be empty');
    }

    public function testUserProfileUnauthorized()
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
