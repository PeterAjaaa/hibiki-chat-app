<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

use App\Models\{
    Conversation,
    User
};

final class MessageTest extends TestCase
{

    use RefreshDatabase;

    public function testMessageSending()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $token = auth('api')->login($sender);

        $conversation = Conversation::create();
        $conversation->users()->attach([$sender->id, $receiver->id]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(
                'api/conversations/' . $conversation->id . '/messages',
                [
                    'content' => 'Hello from MessageTest test suite!',
                ]
            );

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJsonStructure([
                'success',
                'message',
                'data' => [
                    'content' => [
                        'user_id',
                        'content',
                        'conversation_id',
                        'created_at',
                        'id',
                        'updated_at',
                    ]
                ]
            ]);
    }
}
