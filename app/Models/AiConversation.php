<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiConversation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
    ];

    /**
     * Get the user that owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all messages in this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message for preview purposes.
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(AiMessage::class)->latestOfMany();
    }
}
