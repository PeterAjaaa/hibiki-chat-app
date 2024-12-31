<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

final class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function testUserLogin()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('passwordTest')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'passwordTest',
        ]);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJsonStructure([
                'success',
                'message',
                'data' => [
                    'token'
                ]
            ]);

        $responseData = $response->json();
        $this->assertNotEmpty($responseData['data']['token'], 'Token should not be empty');
    }

    public function testUserLoginMissingRequiredField()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertExactJsonStructure([
                'success',
                'message',
                'error' => [
                    'error_details'
                ]
            ]);
    }


    public function testUserLoginIncorrectDataType()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 12345678,
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertExactJsonStructure([
                'success',
                'message',
                'error' => [
                    'error_details'
                ]
            ]);
    }

    public function testUserLoginOutOfRange()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertExactJsonStructure([
                'success',
                'message',
                'error' => [
                    'error_details'
                ]
            ]);
    }

    public function testUserLoginWrongCredential()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('passwordTestCorrect')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'passwordTestWrong',
        ]);

        $response
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJsonStructure([
                'success',
                'message',
                'error' => [
                    'error_details'
                ]
            ]);
    }

    public function testUserLoginMalformedEmail()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test.example.com',
            'password' => 'passwordTest',
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertExactJsonStructure([
                'success',
                'message',
                'error' => [
                    'error_details'
                ]
            ]);
    }

    public function testUserOverlyLongEmail()
    {
        $response = $this->postJson('/api/login', [
            'email' => Str::random(100),
            'password' => 'passwordTest',
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertExactJsonStructure([
                'success',
                'message',
                'error' => [
                    'error_details'
                ]
            ]);
    }

    public function testUserOverlyLongPassword()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => Str::random(100)
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertExactJsonStructure([
                'success',
                'message',
                'error' => [
                    'error_details'
                ]
            ]);
    }
}
