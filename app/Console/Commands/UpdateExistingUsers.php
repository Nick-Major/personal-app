<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateExistingUsers extends Command
{
    protected $signature = 'users:update-existing';
    protected $description = 'Add missing fields to existing users';

    public function handle()
    {
        $users = User::all();
        
        $this->info("Updating {$users->count()} users...");
        
        foreach ($users as $user) {
            // Если фамилия пустая, попробуем разбить name
            if (empty($user->surname) && !empty($user->name)) {
                $nameParts = explode(' ', $user->name);
                if (count($nameParts) >= 2) {
                    $user->surname = $nameParts[0];
                    $user->name = $nameParts[1] ?? '';
                    $user->patronymic = $nameParts[2] ?? '';
                }
            }
            
            $user->save();
        }
        
        $this->info('Users updated successfully!');
        
        // Покажем статистику
        $this->info("\nStatistics:");
        $this->info("Total users: " . User::count());
        $this->info("Users with surname: " . User::whereNotNull('surname')->count());
        $this->info("Always brigadiers: " . User::where('is_always_brigadier', true)->count());
    }
}
