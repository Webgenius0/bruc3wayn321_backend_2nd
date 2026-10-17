<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $david = User::where('email', 'user@gmail.com')->first();
        $kenji = User::where('email', 'kenji@example.com')->first();
        $elena = User::where('email', 'elena@example.com')->first();
        $sarah = User::where('email', 'sarah@example.com')->first();
        $lucas = User::where('email', 'lucas@example.com')->first();

        if (!$david || !$kenji || !$elena) {
            return;
        }

        // Group 1: Tokyo Foodies & Explorers
        $groupTokyo = Chat::create([
            'name' => 'Tokyo Foodies & Explorers 🍜',
            'image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=300&q=80',
            'is_group' => true,
            'created_by' => $kenji->id,
        ]);

        $tokyoMembers = [$kenji, $david, $sarah ?? $admin];
        foreach ($tokyoMembers as $member) {
            ChatUser::create([
                'chat_id' => $groupTokyo->id,
                'user_id' => $member->id,
                'is_admin' => $member->id === $kenji->id,
            ]);
        }

        $tokyoMessages = [
            [$kenji->id, 'Konnichiwa everyone! Welcome to the Tokyo food and travel circle! Any special cravings this week?'],
            [$david->id, 'Hey Kenji! I just landed in Tokyo today. Staying near Shinjuku station. Where should I go for dinner?'],
            [$kenji->id, 'You have to check out Fuunji near the South Exit for tsukemen (dipping noodles). Get there by 6 PM to beat the line!'],
            [$sarah ? $sarah->id : $admin->id, 'Fuunji is amazing! Also don\'t miss the yakitori alley in Omoide Yokocho right afterwards!'],
            [$david->id, 'Awesome, heading out right now! Thanks for the tips 🙌'],
        ];

        foreach ($tokyoMessages as $msg) {
            ChatMessage::create([
                'chat_id' => $groupTokyo->id,
                'sender_id' => $msg[0],
                'message' => $msg[1],
                'type' => 'text',
                'is_read' => true,
            ]);
        }

        // Group 2: Lisbon Remote Workers Hub
        $groupLisbon = Chat::create([
            'name' => 'Lisbon Remote Workers Hub 💻',
            'image' => 'https://images.unsplash.com/photo-1513581166391-887a96ddeafd?auto=format&fit=crop&w=300&q=80',
            'is_group' => true,
            'created_by' => $elena->id,
        ]);

        $lisbonMembers = [$elena, $david, $lucas ?? $admin];
        foreach ($lisbonMembers as $member) {
            ChatUser::create([
                'chat_id' => $groupLisbon->id,
                'user_id' => $member->id,
                'is_admin' => $member->id === $elena->id,
            ]);
        }

        $lisbonMessages = [
            [$elena->id, 'Olá team! Anyone planning to work from Second Home Mercado da Ribeira tomorrow?'],
            [$david->id, 'I was thinking about checking out Dear Breakfast in Chiado first, then heading over!'],
            [$lucas ? $lucas->id : $admin->id, 'I will join around 2 PM after my surf session in Costa da Caparica! 🏄‍♂️'],
            [$elena->id, 'Perfect! Let\'s grab pastel de nata and coffee on the terrace at 4 PM ☕'],
        ];

        foreach ($lisbonMessages as $msg) {
            ChatMessage::create([
                'chat_id' => $groupLisbon->id,
                'sender_id' => $msg[0],
                'message' => $msg[1],
                'type' => 'text',
                'is_read' => true,
            ]);
        }

        // Direct 1-on-1 Chat: David & Kenji
        $directChat = Chat::create([
            'name' => null,
            'image' => null,
            'is_group' => false,
            'created_by' => $david->id,
        ]);

        ChatUser::create(['chat_id' => $directChat->id, 'user_id' => $david->id, 'is_admin' => false]);
        ChatUser::create(['chat_id' => $directChat->id, 'user_id' => $kenji->id, 'is_admin' => false]);

        $dmMessages = [
            [$david->id, 'Hey Kenji, are you free for a coffee in Shibuya tomorrow morning?'],
            [$kenji->id, 'Hey David! Yes, around 10 AM works great for me. Let’s meet at About Life Coffee Brewers near Dogenzaka.'],
            [$david->id, 'Sounds like a plan! See you there at 10 👍'],
        ];

        foreach ($dmMessages as $msg) {
            ChatMessage::create([
                'chat_id' => $directChat->id,
                'sender_id' => $msg[0],
                'message' => $msg[1],
                'type' => 'text',
                'is_read' => true,
            ]);
        }
    }
}
