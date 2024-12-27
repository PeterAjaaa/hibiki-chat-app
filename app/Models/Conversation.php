<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_users', 'conversation_id', 'user_id')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
