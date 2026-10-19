<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $userName = Str::lower($firstName . '.' . $lastName . fake()->numberBetween(10, 99));

        $travelBios = [
            'Solo traveler & landscape photographer currently discovering hidden gems in Southeast Asia 🎒📸',
            'Digital nomad exploring co-working spaces and coffee spots across Europe ☕💻',
            'Passionate foodie on a mission to taste every street food dish in Asia 🍜🥢',
            'Expat based in Tokyo | Sharing practical relocation advice and cultural guides ⛩️🗾',
            'Backpacking on a budget across Latin America | Mountains, beaches & hostels ⛰️🏖️',
            'Architect & history buff documenting ancient European cities and monuments 🏛️',
            'Slow traveler currently living in Lisbon. Sunset chaser & pastel de nata lover 🇵🇹',
            'Adventure seeker into hiking, scuba diving and wild camping ⛺🧗',
            'Remote software engineer traveling the world one visa at a time 🌍✈️',
            'Cultural explorer interested in local traditions, homestays, and off-the-beaten-path routes 🗺️',
        ];

        $avatars = [
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=400&q=80',
        ];

        $coverImages = [
            'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1503220317375-aaad61436b1b?auto=format&fit=crop&w=1200&q=80',
        ];

        $currencies = [
            ['code' => 'USD', 'flag' => '🇺🇸'],
            ['code' => 'EUR', 'flag' => '🇪🇺'],
            ['code' => 'GBP', 'flag' => '🇬🇧'],
            ['code' => 'JPY', 'flag' => '🇯🇵'],
            ['code' => 'AUD', 'flag' => '🇦🇺'],
            ['code' => 'CAD', 'flag' => '🇨🇦'],
        ];
        $currencyChoice = fake()->randomElement($currencies);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'user_name' => $userName,
            'bio' => fake()->randomElement($travelBios),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->e164PhoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('12345678'),
            'avatar' => fake()->randomElement($avatars),
            'cover_image' => fake()->randomElement($coverImages),
            'date_of_birth' => fake()->dateTimeBetween('-40 years', '-18 years')->format('Y-m-d'),
            'currency' => $currencyChoice['code'],
            'currency_flug' => $currencyChoice['flag'],
            'role' => 'user',
            'status' => true,
            'email_visibility' => true,
            'phone_visibility' => false,
            'can_activate_free_trial' => true,
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false,
            'match_notifications' => true,
            'message_notifications' => true,
            'like_notifications' => true,
            'marketing_notifications' => false,
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
