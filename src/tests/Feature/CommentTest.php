<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Database\Seeders\ConditionSeeder;
use Database\Seeders\CategorySeeder;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(ConditionSeeder::class);
        $this->seed(CategorySeeder::class);
    }

    public function test_logged_in_user_can_comment()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post(route('item.comment.store', $item->id), [
            'comment' => 'This is a test comment.',
        ]);

        $response->assertRedirect();
        $responseDetail = $this->get(route('item.show', $item->id));
        $responseDetail->assertSee('This is a test comment.');
        $responseDetail->assertSee($user->name);
    }

    public function test_guest_cannot_comment()
    {
        $item = Item::factory()->create();
        $response = $this->post(route('item.comment.store', $item->id), [
            'comment' => 'Guest comment',
        ]);

        $this->assertGuest();
    }

    public function test_comment_validation_length()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $longComment = str_repeat('a', 256); 

        $response = $this->actingAs($user)->post(route('item.comment.store', $item->id), [
            'comment' => $longComment,
        ]);

        $response->assertSessionHasErrors(['comment']);
    }
}