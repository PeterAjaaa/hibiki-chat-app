<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

final class ConversationTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateNewConversation()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $token = auth('api')->login($sender);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(
                '/api/conversations',
                [
                    'user_ids' => [$sender->id, $receiver->id]
                ]
            );


        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJsonStructure([
                'success',
                'message',
                'data' => [
                    'conversation' => [
                        'id',
                        'users',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);

        $this->assertDatabaseHas('conversations', [
            'id' => $response['data']['conversation']['id']
        ]);
    }

    public function testCreateNewConversationMissingRequiredField()
    {
        $sender = User::factory()->create();
        $token = auth('api')->login($sender);

        // This header is needed so that the we can assert the JSON structure
        $response = $this
            ->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token])
            ->postJson(
                '/api/conversations',
                [
                    'user_ids' => []
                ]
            );

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('user_ids')
            ->assertExactJsonStructure([
                'errors' => [
                    '0' => 'user_ids',
                ],
                'message',
            ]);
    }

    public function testCreateNewConversationMissingPartialRequiredField()
    {

        $sender = User::factory()->create();
        $token = auth('api')->login($sender);

        $response = $this
            ->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token])
            ->postJson(
                '/api/conversations',
                [
                    'user_ids' => [$sender->id]
                ]
            );

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('user_ids')
            ->assertExactJsonStructure([
                'errors' => [
                    '0' => 'user_ids',
                ],
                'message',
            ]);
    }


    public function testCreateNewConversationIncorrectDataType()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $token = auth('api')->login($sender);

        $response = $this
            ->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token])
            ->postJson(
                '/api/conversations',
                [
                    'user_ids' => ['1', '2']
                ]
            );

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['user_ids.0'])
            ->assertJsonValidationErrors(['user_ids.1'])
            ->assertExactJsonStructure([
                'errors' => [
                    '0' => 'user_ids.0',
                    '1' => 'user_ids.1'
                ],
                'message',
            ]);
    }

    public function testCreateNewConversationValueOutOfRange()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $token = auth('api')->login($sender);

        $response = $this
            ->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token])
            ->postJson(
                '/api/conversations',
                [
                    'user_ids' => [$sender->id, $receiver->id + 1]
                ]
            );

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['user_ids.1'])
            ->assertExactJsonStructure([
                'errors' => [
                    '0' => 'user_ids.1',
                ],
                'message',
            ]);
    }

    public function testUserProfileUnauthorized()
    {
        // This header is needed so that the we can assert the JSON structure
        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->post('api/conversations');

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
