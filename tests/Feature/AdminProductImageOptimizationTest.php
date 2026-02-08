<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_upload_raster_otomatis_disimpan_teroptimasi(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Cold Brew',
            'sku' => 'CB-001',
            'category_id' => $category->id,
            'price' => 32000,
            'cost_price' => 15000,
            'track_stock' => '1',
            'stock_qty' => 100,
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('cold-brew.png', 2400, 1600),
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::query()->firstOrFail();
        $this->assertNotNull($product->image_path);
        $extension = strtolower((string) pathinfo((string) $product->image_path, PATHINFO_EXTENSION));
        $this->assertContains($extension, ['webp', 'jpg']);

        Storage::disk('public')->assertExists((string) $product->image_path);
    }

    public function test_admin_upload_svg_otomatis_dimampatkan(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $svgContent = <<<'SVG'
<?xml version="1.0" encoding="UTF-8"?>
<!-- komentar -->
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="200" onload="alert('xss')">
    <script>alert('xss')</script>
    <rect x="0" y="0" width="300" height="200" fill="#6F4E37"></rect>
</svg>
SVG;

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'SVG Drink',
            'sku' => 'SVG-001',
            'category_id' => $category->id,
            'price' => 20000,
            'cost_price' => 9000,
            'track_stock' => '1',
            'stock_qty' => 100,
            'is_active' => '1',
            'image' => UploadedFile::fake()->createWithContent('drink.svg', $svgContent),
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::query()->firstOrFail();
        $this->assertStringEndsWith('.svg', (string) $product->image_path);
        Storage::disk('public')->assertExists((string) $product->image_path);

        $storedContent = Storage::disk('public')->get((string) $product->image_path);
        $this->assertStringNotContainsString('<!--', $storedContent);
        $this->assertStringNotContainsString('<script', strtolower($storedContent));
        $this->assertStringNotContainsString('onload=', strtolower($storedContent));
    }
}

