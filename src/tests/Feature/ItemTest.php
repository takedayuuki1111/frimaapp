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
}