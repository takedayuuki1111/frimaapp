<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\SoldItem;
use Database\Seeders\ConditionSeeder;
use Database\Seeders\CategorySeeder;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(ConditionSeeder::class);
        $this->seed(CategorySeeder::class);
    }

    public function test_purchase_flow_updates_status_and_profile()
    {
        $seller = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();

        $response = $this->actingAs($buyer)->post(route('purchase.store', $item->id), [
            'payment_method' => 'konbini', 
            'item_id' => $item->id,
        ]);

        $this->assertDatabaseHas('sold_items', [
            'item_id' => $item->id,
            'user_id' => $buyer->id,
        ]);
    
        $responseIndex = $this->get('/');
        $responseIndex->assertSee('SOLD');

        $responseProfile = $this->actingAs($buyer)->get('/mypage?page=buy');
        $responseProfile->assertSee($item->name);
    }

    public function test_address_change_reflects_on_purchase_screen()
    {
        $seller = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create([
            'postal_code' => '000-0000',
            'address' => 'Old Address',
        ]);

        $newAddressData = [
            'postal_code' => '1234567',
            'address' => 'New Address City',
            'building_name' => 'New Building 101',
        ];

        $response = $this->actingAs($buyer)->post(route('purchase.address.update', $item->id), $newAddressData);

        $response->assertRedirect(route('purchase.create', ['item_id' => $item->id]));

        $responsePurchase = $this->actingAs($buyer)->get(route('purchase.create', ['item_id' => $item->id]));
        $responsePurchase->assertSee('123-4567');
        $responsePurchase->assertSee('New Address City');
        $responsePurchase->assertSee('New Building 101');
    }

    public function test_seller_cannot_buy_own_item()
    {
        $seller = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $response = $this->actingAs($seller)->get(route('purchase.create', $item->id));
        $response->assertRedirect('/'); 
    }

    public function test_cannot_buy_sold_item()
    {
        $seller = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        SoldItem::create(['user_id' => User::factory()->create()->id, 'item_id' => $item->id]);

        $buyer = User::factory()->create();
        $response = $this->actingAs($buyer)->get(route('purchase.create', $item->id));
        $response->assertRedirect('/');
    }
}