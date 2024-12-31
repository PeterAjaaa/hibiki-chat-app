<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

final class TokenRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function testUserTokenRefresh()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->post('api/tokens/refresh');

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token'
                ]
            ]);


        $newToken = $response->json()['data']['token'];
        $this->assertNotEmpty($newToken, 'New token data should not be empty');
        $this->assertNotSame($token, $newToken, 'Old and new token should not be the same');
    }

    public function testUserTokenRefreshUnauthorized()
    {
        // This header is needed so that the we can assert the JSON structure
        $response = $this->withHeaders(['Accept' => 'application/json'])->post('api/tokens/refresh');

        // Middleware-based exception has their own way of handling exception
        $response
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonStructure(
                [
                    'message',
                ]
            );
    }
}
