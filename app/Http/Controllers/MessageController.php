<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\{
    Conversation,
    Message
};

class MessageController extends Controller
{
    /**
     * Send a new message in a specific conversation.
     */
    public function sendMessage(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);
        $user = auth('api')->user();
        $conversation = Conversation::findOrFail($id);

        if (!$conversation->users->contains($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'error' => [
                    'error_details' => 'Unauthorized during message sending'
                ]
            ], 401);
        } else {
            $content = Message::create([
                'user_id' => $user->id,
                'conversation_id' => $conversation->id,
                'content' => $validated['content']
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'content' => $content
                ]
            ], 200);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $messages)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Message $messages)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $messages)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $messages)
    {
        //
    }
}
