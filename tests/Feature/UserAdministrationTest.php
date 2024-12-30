<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

final class UserAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function testUserRegistration()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'passwordTestUser',
            'password_confirmation' => 'passwordTestUser'
        ];

        $response = $this->post('/api/register', $userData);

        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(
                [
                    'success',
                    'message',
                    'data' => [
                        'user',
                        'token',
                    ]
                ]
            );

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email']
        ]);
    }

    public function testUserLogin()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('passwordTest'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'passwordTest',
        ]);

        $response->assertStatus(Response::HTTP_OK)->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
            ]
        ]);
    }
}
