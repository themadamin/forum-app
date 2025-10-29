<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         $users = User::factory(20)->create();

         $posts = Post::factory(200)->recycle($users)->create();

         $comments = Comment::factory(200)->recycle([$users, $posts])->create();

        User::factory()->has(Post::factory(30))
            ->has(Comment::factory(100)->recycle($posts))
            ->create([
            'name' => 'themadamin',
            'email' => 'madaminme@gmail.com',
        ]);
    }
}
