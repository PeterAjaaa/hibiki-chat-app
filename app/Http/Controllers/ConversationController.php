<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * Get all conversations for the logged in user
     */
    public function getAllConversation()
    {
        $user = auth('api')->user();
        $conversations = $user->conversations()->with('users')->latest('updated_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'Conversations found successfully',
            'data' => [
                'conversations' => $conversations
            ]
        ], 200);
    }


    /**
     *  Create a new conversation
     */
    public function createNewConversation(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:2',
            'user_ids.*' => 'required|exists:users,id'
        ]);

        $conversation = Conversation::create();
        $conversation->users()->attach($request->user_ids);

        return response()->json([
            'success' => true,
            'message' => 'Conversation created successfully',
            'data' => [
                'conversation' => $conversation->load('users')
            ]
        ], 200);
    }

    public function addParticipants(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = auth('api')->user();

        if (!$conversation->users->contains($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'error' => [
                    'error_details' => 'Unauthorized during participant addition'
                ]
            ], 401);
        } else {
            $validated = $request->validate([
                'user_ids' => 'required|exists:users,id',
            ]);

            $conversation->users()->attach($validated['user_ids']);

            return response()->json([
                'success' => true,
                'message' => 'Participants added successfully',
                'data' => [
                    'conversation' => $conversation->load('users')
                ]
            ], 200);
        }
    }

    public function removeParticipant(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = auth('api')->user();

        if (!$conversation->users->contains($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'error' => [
                    'error_details' => 'Unauthorized during participant removal'
                ]
            ], 401);
        } else {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $conversation->users()->detach($validated['user_id']);

            return response()->json([
                'success' => true,
                'message' => 'Participant removed successfully',
                'data' => [
                    'conversation' => $conversation->load('users')
                ]
            ], 200);
        }
    }

    /**
     * Get all messages from a single conversation
     */
    public function getAllMessagesFromConversation($id)
    {
        $conversation = Conversation::with(['users', 'messages'])->findOrFail($id);
        $user = auth('api')->user();

        if (!$conversation->users->contains($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'error' => [
                    'error_details' => 'Unauthorized during message retrieval'
                ]
            ], 401);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Messages found successfully',
                'data' => [
                    'conversation' => $conversation
                ]
            ], 200);
        }
    }

    /**
     * Delete a conversation
     */
    public function destroyConversation($id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = auth('api')->user();

        if (!$conversation->users->contains($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'error' => [
                    'error_details' => 'Unauthorized during conversation deletion'
                ]
            ], 401);
        } else {
            $conversation->delete();
            return response()->json([
                'success' => true,
                'message' => 'Conversation deleted successfully'
            ], 200);
        }
    }
}
