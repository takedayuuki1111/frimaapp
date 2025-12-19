<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Condition;
use App\Models\Category;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ConditionSeeder::class);
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    public function test_item_list_screen_can_be_rendered()
    {
        $user = User::factory()->create();
        Item::factory()->create(['user_id' => $user->id]);

        $response = $this->get('/');
        $response->assertStatus(200);

    }

    public function test_item_detail_screen_can_be_rendered()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $response = $this->get('/item/' . $item->id);
        $response->assertStatus(200);
        $response->assertSee($item->name);
    }
    public function test_user_can_like_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post(route('item.like', $item->id));
        $response->assertRedirect(); 

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->post(route('item.like', $item->id)); 
    }

    public function test_guest_cannot_like_item()
    {
        $item = Item::factory()->create();

        $response = $this->post(route('item.like', $item->id));
        $this->assertGuest();
    }
}