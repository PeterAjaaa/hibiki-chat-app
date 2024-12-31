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


        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->get('api/user');

        $response = $this->postJson(
            '/api/conversations',
            [
                'user_ids' => [$sender->id, $receiver->id]
            ]
        );


        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
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
}
