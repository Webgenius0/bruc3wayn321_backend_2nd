<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Subscription;
use App\Models\SupportIssue;
use App\Models\User;
use App\Models\UserDestination;
use App\Models\UserReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryAndSeederTest extends TestCase
{
    use RefreshDatabase;
    public function test_all_factories_can_create_models(): void
    {
        $user = User::factory()->create();
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $post = Post::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);

        $comment = PostComment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('post_comments', ['id' => $comment->id]);

        $destination = UserDestination::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('user_destinations', ['id' => $destination->id]);

        $support = SupportIssue::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('support_issues', ['id' => $support->id]);

        $chat = Chat::factory()->create(['created_by' => $user->id]);
        $this->assertDatabaseHas('chats', ['id' => $chat->id]);

        $chatMsg = ChatMessage::factory()->create(['chat_id' => $chat->id, 'sender_id' => $user->id]);
        $this->assertDatabaseHas('chat_messages', ['id' => $chatMsg->id]);

        $aiConv = AiConversation::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('ai_conversations', ['id' => $aiConv->id]);

        $aiMsg = AiMessage::factory()->create(['ai_conversation_id' => $aiConv->id]);
        $this->assertDatabaseHas('ai_messages', ['id' => $aiMsg->id]);

        $user2 = User::factory()->create();
        $report = UserReport::factory()->create([
            'reporter_id' => $user->id,
            'reported_user_id' => $user2->id,
        ]);
        $this->assertDatabaseHas('user_reports', ['id' => $report->id]);

        // Clean up test created records
        $report->delete();
        $aiMsg->delete();
        $aiConv->delete();
        $chatMsg->delete();
        $chat->delete();
        $support->delete();
        $destination->delete();
        $comment->delete();
        $post->delete();
        $user2->delete();
        $user->delete();
    }
}
