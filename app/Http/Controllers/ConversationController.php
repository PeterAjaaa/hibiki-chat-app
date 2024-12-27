<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\User;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('api')->user();
        $conversations = $user->conversations()->with('users')->latest('updated_at')->get();

        return response()->json([
            'conversations' => $conversations
        ], 200);
    }


    /**
     *  Create a new conversation
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:2',
            'user_ids.*' => 'required|exists:users,id'
        ]);


        $conversation = Conversation::create();
        $conversation->users()->attach($request->user_ids);

        return response()->json([
            'message' => 'Conversation created successfully',
            'conversation' => $conversation->load('users')
        ]);
    }

    public function addParticipants(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = auth('api')->user();

        if (!$conversation->users->contains($user->id)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        } else {
            $validated = $request->validate([
                'user_ids' => 'required|exists:users,id',
            ]);

            $conversation->users()->attach($validated['user_ids']);

            return response()->json([
                'message' => 'Participants added successfully',
                'conversation' => $conversation->load('users')
            ], 200);
        }
    }

    public function removeParticipant(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = auth('api')->user();

        if (!$conversation->users->contains($user->id)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        } else {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $conversation->users()->detach($validated['user_id']);

            return response()->json([
                'message' => 'Participant removed successfully',
                'conversation' => $conversation->load('users')
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $conversation = Conversation::with(['users', 'messages'])->findOrFail($id);
        $user = auth('api')->user();

        if (!$conversation->users->contains($user->id)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        } else {
            return response()->json([
                'conversation' => $conversation
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = auth('api')->user();

        if (!$conversation->users->contains($user->id)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        } else {
            $conversation->delete();
            return response()->json([
                'message' => 'Conversation deleted successfully'
            ], 200);
        }
    }
}
