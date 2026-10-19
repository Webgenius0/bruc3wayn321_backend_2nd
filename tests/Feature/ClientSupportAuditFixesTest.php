<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Post;
use App\Models\User;
use App\Models\UsageLimit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientSupportAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_messages_of_another_users_chat_idor_protection()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $intruder = User::factory()->create();

        $chat = Chat::create([
            'is_group' => false,
            'created_by' => $user1->id,
        ]);
        $chat->users()->attach([$user1->id, $user2->id]);

        ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $user1->id,
            'message' => 'Secret private conversation',
        ]);

        // Intruder tries to access messages
        $response = $this->actingAs($intruder, 'sanctum')
            ->getJson("/api/chat/messages/{$chat->id}");

        $response->assertStatus(403);

        // Legitimate participant accesses messages
        $validResponse = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/chat/messages/{$chat->id}");

        $validResponse->assertStatus(200);
    }

    public function test_user_cannot_access_chat_attachments_or_links_without_being_participant()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $intruder = User::factory()->create();

        $chat = Chat::create([
            'is_group' => false,
            'created_by' => $user1->id,
        ]);
        $chat->users()->attach([$user1->id, $user2->id]);

        $responseAttach = $this->actingAs($intruder, 'sanctum')
            ->getJson("/api/v1/user/chat/{$chat->id}/attachment/list");

        $responseAttach->assertStatus(403);

        $responseLinks = $this->actingAs($intruder, 'sanctum')
            ->getJson("/api/v1/user/chat/{$chat->id}/links/list");

        $responseLinks->assertStatus(403);

        // Legitimate participant accesses attachments and links
        $validAttach = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/v1/user/chat/{$chat->id}/attachment/list");
        $validAttach->assertStatus(200);

        $validLinks = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/v1/user/chat/{$chat->id}/links/list");
        $validLinks->assertStatus(200);
    }

    public function test_post_owner_can_delete_post_and_non_owner_gets_403()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $post = Post::create([
            'user_id' => $owner->id,
            'content' => 'My test post',
            'slug' => 'my-test-post',
            'is_draft' => false,
            'is_published' => true,
            'visibility' => true,
        ]);

        // Non-owner attempts to delete
        $intruderResponse = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/v1/posts/{$post->slug}");

        $intruderResponse->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);

        // Owner deletes post
        $ownerResponse = $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/v1/posts/{$post->slug}");

        $ownerResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_ai_voice_chat_rejects_when_quota_limit_exceeded()
    {
        $user = User::factory()->create();

        \App\Models\Subscription::create([
            'user_id' => $user->id,
            'type' => 'paid',
            'store' => 'INTERNAL',
            'product_id' => 'premium_monthly',
            'entitlement' => 'premium',
            'status' => 'active',
            'purchase_date' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'will_renew' => true,
        ]);

        UsageLimit::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'ai_voice', 'period_start' => now()->format('Y-m-01')],
            ['limit' => 5, 'current_count' => 5]
        );

        $fakeAudio = \Illuminate\Http\UploadedFile::fake()->create('audio.mp3', 500, 'audio/mpeg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/ai-concierge/voice-chat', [
                'audio' => $fakeAudio,
            ]);

        // Should be rejected with 426 (upgrade required) or 429 before executing Whisper or TTS
        $this->assertContains($response->status(), [426, 429]);
        $response->assertJson(['success' => false]);
    }
}
