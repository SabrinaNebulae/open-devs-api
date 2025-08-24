<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserProfileSkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Logs
        Log::info('Start seeding Profiles in database');

        // Admin creation
        $admin = User::updateOrCreate([
            'email' => 'admin@open-devs.com'
        ],
        [
            'name' => 'admin',
            'password' => Hash::make('password'),
        ]);

        // Users creation
        User::factory()->count(12)->create();

        // Skills creation
        Skill::factory()->count(40)->create();

        // Profiles for each user
        $users = User::query()->except($admin)->get();
        foreach ($users as $user) {
            Profile::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        // Skills for each profile
        $profiles = Profile::all();
        $skills = Skill::all();
        foreach ($profiles as $profile) {
            $profile->skills()->sync($skills->random(3)->pluck('id'));
        }

        // Logs
        Log::info('End seeding Profiles in database');
    }
}
