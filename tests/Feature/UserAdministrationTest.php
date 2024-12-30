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

    public function testUserRegistrationMissingRequiredField()
    {
        $userData = [
            'email' => 'testuser@example.com',
            'password' => 'passwordTestUser',
            'password_confirmation' => 'passwordTestUser'
        ];

        $response = $this->post('/api/register', $userData);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(
                [
                    'success',
                    'message',
                    'error' => [
                        'error_details',
                    ]
                ]
            );
    }

    public function testUserRegistrationFailingValidation()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'passwordTestUser',
            'password_confirmation' => 'passwordTestUserIsNotTheSame'
        ];

        $response = $this->post('/api/register', $userData);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(
                [
                    'success',
                    'message',
                    'error' => [
                        'error_details',
                    ]
                ]
            );
    }

    public function testUserRegistrationNonUniqueEmail()
    {
        $oldUser = User::factory()->create([
            'email' => 'testuser@example.com',
        ]);

        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'passwordTestUser',
            'password_confirmation' => 'passwordTestUserIsNotTheSame'
        ];

        $response = $this->post('/api/register', $userData);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(
                [
                    'success',
                    'message',
                    'error' => [
                        'error_details',
                    ]
                ]
            );
    }

    public function testUserRegistrationIncorrectDataType()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 12345678,
            'password_confirmation' => 'passwordTestUserIsNotTheSame'
        ];

        $response = $this->post('/api/register', $userData);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(
                [
                    'success',
                    'message',
                    'error' => [
                        'error_details',
                    ]
                ]
            );
    }

    public function testUserRegistrationInvalidPasswordLength()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => '123',
            'password_confirmation' => 'passwordTestUserIsNotTheSame'
        ];

        $response = $this->post('/api/register', $userData);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(
                [
                    'success',
                    'message',
                    'error' => [
                        'error_details',
                    ]
                ]
            );
    }

    public function testUserRegistrationMalformedEmail()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser.example.com',
            'password' => 'passwordTest',
            'password_confirmation' => 'passwordTest'
        ];

        $response = $this->post('/api/register', $userData);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(
                [
                    'success',
                    'message',
                    'error' => [
                        'error_details',
                    ]
                ]
            );
    }

    public function testUserRegistrationOverlyLongName()
    {
        $userData = [
            'name' => Str::random(100),
            'email' => 'testuser@example.com',
            'password' => 'passwordTest',
            'password_confirmation' => 'passwordTest'
        ];

        $response = $this->post('/api/register', $userData);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(
                [
                    'success',
                    'message',
                    'error' => [
                        'error_details',
                    ]
                ]
            );
    }

    public function testUserRegistrationOverlyLongPassword()
    {
        $password = Str::random(100);

        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => $password,
            'password_confirmation' => $password
        ];

        $response = $this->post('/api/register', $userData);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(
                [
                    'success',
                    'message',
                    'error' => [
                        'error_details',
                    ]
                ]
            );
    }

    public function testUserLogin()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('passwordTest')
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
