<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PosDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@coffeeshop.test'],
            [
                'name' => 'Admin Coffeeshop',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        $kasir = User::query()->updateOrCreate(
            ['email' => 'kasir@coffeeshop.test'],
            [
                'name' => 'Kasir Coffeeshop',
                'password' => Hash::make('password'),
                'role' => User::ROLE_KASIR,
                'email_verified_at' => now(),
            ]
        );

        $categories = [
            'Coffee' => Category::query()->updateOrCreate(['name' => 'Coffee'], ['is_active' => true]),
            'Non-Coffee' => Category::query()->updateOrCreate(['name' => 'Non-Coffee'], ['is_active' => true]),
            'Snack' => Category::query()->updateOrCreate(['name' => 'Snack'], ['is_active' => true]),
        ];

        $products = [
            ['name' => 'Americano', 'sku' => 'CF-AMR', 'category' => 'Coffee', 'price' => 22000, 'cost_price' => 11000, 'track_stock' => true, 'stock_qty' => 100, 'is_active' => true],
            ['name' => 'Latte', 'sku' => 'CF-LAT', 'category' => 'Coffee', 'price' => 28000, 'cost_price' => 14500, 'track_stock' => true, 'stock_qty' => 100, 'is_active' => true],
            ['name' => 'Cappuccino', 'sku' => 'CF-CAP', 'category' => 'Coffee', 'price' => 29000, 'cost_price' => 15000, 'track_stock' => true, 'stock_qty' => 100, 'is_active' => true],
            ['name' => 'Es Kopi Gula Aren', 'sku' => 'CF-EGA', 'category' => 'Coffee', 'price' => 30000, 'cost_price' => 16000, 'track_stock' => true, 'stock_qty' => 100, 'is_active' => true],
            ['name' => 'Mocha', 'sku' => 'CF-MOC', 'category' => 'Coffee', 'price' => 32000, 'cost_price' => 17500, 'track_stock' => true, 'stock_qty' => 100, 'is_active' => true],
            ['name' => 'Matcha Latte', 'sku' => 'NC-MAT', 'category' => 'Non-Coffee', 'price' => 33000, 'cost_price' => 18500, 'track_stock' => true, 'stock_qty' => 100, 'is_active' => true],
            ['name' => 'Chocolate', 'sku' => 'NC-CHO', 'category' => 'Non-Coffee', 'price' => 29000, 'cost_price' => 16000, 'track_stock' => true, 'stock_qty' => 100, 'is_active' => true],
            ['name' => 'Lemon Tea', 'sku' => 'NC-LMT', 'category' => 'Non-Coffee', 'price' => 22000, 'cost_price' => 10000, 'track_stock' => true, 'stock_qty' => 100, 'is_active' => true],
            ['name' => 'Croissant', 'sku' => 'SN-CRS', 'category' => 'Snack', 'price' => 19000, 'cost_price' => 11000, 'track_stock' => true, 'stock_qty' => 100, 'is_active' => true],
            ['name' => 'Cookies', 'sku' => 'SN-CKS', 'category' => 'Snack', 'price' => 15000, 'cost_price' => 9000, 'track_stock' => true, 'stock_qty' => 100, 'is_active' => true],
        ];

        $variantTargets = ['Latte', 'Cappuccino', 'Mocha', 'Matcha Latte', 'Chocolate'];

        foreach ($products as $data) {
            $product = Product::query()->updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'category_id' => $categories[$data['category']]->id,
                    'price' => $data['price'],
                    'cost_price' => $data['cost_price'],
                    'track_stock' => $data['track_stock'],
                    'stock_qty' => $data['stock_qty'],
                    'is_active' => $data['is_active'],
                    'image_path' => null,
                ]
            );

            if (in_array($data['name'], $variantTargets, true)) {
                $variants = [
                    ['name' => 'Small', 'price_delta' => 0],
                    ['name' => 'Medium', 'price_delta' => 3000],
                    ['name' => 'Large', 'price_delta' => 6000],
                ];

                foreach ($variants as $variant) {
                    ProductVariant::query()->updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'name' => $variant['name'],
                        ],
                        [
                            'price' => null,
                            'price_delta' => $variant['price_delta'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        $addons = [
            ['name' => 'Extra Shot', 'price' => 7000],
            ['name' => 'Oat Milk', 'price' => 6000],
            ['name' => 'Caramel Syrup', 'price' => 5000],
            ['name' => 'Vanilla Syrup', 'price' => 5000],
            ['name' => 'Whipped Cream', 'price' => 4000],
        ];

        foreach ($addons as $addon) {
            Addon::query()->updateOrCreate(
                ['name' => $addon['name']],
                [
                    'price' => $addon['price'],
                    'is_active' => true,
                ]
            );
        }

        $methods = [
            ['name' => 'Cash', 'code' => 'cash'],
            ['name' => 'QRIS', 'code' => 'qris'],
            ['name' => 'Debit', 'code' => 'debit'],
            ['name' => 'E-Wallet', 'code' => 'ewallet'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['code' => $method['code']],
                [
                    'name' => $method['name'],
                    'is_active' => true,
                ]
            );
        }

        unset($admin, $kasir);
    }
}
