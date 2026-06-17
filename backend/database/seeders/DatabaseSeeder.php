<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $brands = $this->seedBrands();
        $categories = $this->seedCategories();
        $this->seedProducts($brands, $categories);
        $this->seedCoupons();
    }

    private function seedUsers(): void
    {
        User::create([
            'first_name' => 'Super', 'last_name' => 'Admin', 'name' => 'Super Admin',
            'email' => 'admin@shoehub.test', 'mobile' => '9000000001',
            'password' => Hash::make('password'), 'role' => 'super_admin', 'status' => 'active',
        ]);

        User::create([
            'first_name' => 'Demo', 'last_name' => 'Customer', 'name' => 'Demo Customer',
            'email' => 'customer@shoehub.test', 'mobile' => '9000000002',
            'password' => Hash::make('password'), 'role' => 'customer', 'status' => 'active',
        ]);
    }

    private function seedBrands(): array
    {
        $names = ['Nike', 'Adidas', 'Puma', 'Reebok', 'New Balance', 'Skechers', 'Converse', 'Vans'];
        $brands = [];
        foreach ($names as $i => $name) {
            $brands[] = Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'logo' => "https://picsum.photos/seed/brand{$i}/200/120",
                'description' => "{$name} — performance and lifestyle footwear.",
                'status' => 'active',
            ]);
        }

        return $brands;
    }

    private function seedCategories(): array
    {
        $tree = [
            'Running' => ['Road Running', 'Trail Running'],
            'Sports' => ['Football', 'Basketball', 'Training'],
            'Casual' => ['Sneakers', 'Loafers'],
            'Formal' => [],
            'Sandals & Slides' => [],
        ];

        $categories = [];
        foreach ($tree as $parentName => $children) {
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'image' => 'https://picsum.photos/seed/cat'.Str::slug($parentName).'/400/300',
                'status' => 'active',
            ]);
            $categories[] = $parent;
            foreach ($children as $childName) {
                $categories[] = Category::create([
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'parent_id' => $parent->id,
                    'status' => 'active',
                ]);
            }
        }

        return $categories;
    }

    private function seedProducts(array $brands, array $categories): void
    {
        $models = [
            'Air Zoom Pegasus', 'Ultraboost Light', 'RS-X Reinvention', 'Floatride Energy',
            'Fresh Foam 1080', 'Max Cushioning Arch', 'Chuck Taylor All Star', 'Old Skool Pro',
            'Pulse Runner', 'Velocity Trainer', 'Cloud Strider', 'Court Classic',
            'Trail Blazer GTX', 'Metro Walk', 'Storm Hiker', 'Daily Jogger',
            'Studio Flex', 'Urban Slip-On', 'Marathon Elite', 'Street Knit',
            'Aero Glide', 'Power Court', 'Comfort Stride', 'Retro 84',
        ];
        $colors = [
            ['Black', '#111111'], ['White', '#f5f5f5'], ['Red', '#e11d48'],
            ['Blue', '#2563eb'], ['Grey', '#6b7280'], ['Green', '#16a34a'],
        ];
        $sizes = ['6', '7', '8', '9', '10', '11'];
        $genders = ['men', 'women', 'unisex'];

        foreach ($models as $i => $name) {
            $brand = $brands[$i % count($brands)];
            $category = $categories[$i % count($categories)];
            $price = 1499 + ($i % 8) * 750;
            $onSale = $i % 3 === 0;
            $salePrice = $onSale ? round($price * 0.78, 2) : null;

            $product = Product::create([
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => "{$brand->name} {$name}",
                'slug' => Str::slug("{$brand->name} {$name} {$i}"),
                'sku' => 'SKU-'.strtoupper(Str::random(8)),
                'gender' => $genders[$i % count($genders)],
                'short_description' => 'Lightweight, breathable, and built for all-day comfort.',
                'description' => "The {$brand->name} {$name} blends responsive cushioning with a durable outsole. Engineered mesh upper keeps your feet cool while the foam midsole returns energy with every stride.",
                'price' => $price,
                'sale_price' => $salePrice,
                'weight' => 0.6 + ($i % 4) * 0.1,
                'is_featured' => $i % 4 === 0,
                'is_trending' => $i % 5 === 0,
                'is_new_arrival' => $i % 3 === 1,
                'is_best_seller' => $i % 6 === 0,
                'sold_count' => ($i * 13) % 500,
                'status' => 'active',
            ]);

            for ($img = 0; $img < 3; $img++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $this->shoeImageUrl($i * 3 + $img),
                    'is_primary' => $img === 0,
                    'sort_order' => $img,
                ]);
            }

            $productColors = array_slice($colors, $i % 2, 3);
            foreach ($productColors as $color) {
                foreach ($sizes as $size) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'size' => $size,
                        'color' => $color[0],
                        'color_hex' => $color[1],
                        'sku' => $product->sku.'-'.$color[0][0].$size,
                        'stock' => ($i + (int) $size) % 7 === 0 ? 0 : rand(3, 25),
                    ]);
                }
            }
        }

        $this->seedReviews();
    }

    private function seedReviews(): void
    {
        $customer = User::where('email', 'customer@shoehub.test')->first();
        $bodies = [
            'Super comfortable, true to size!',
            'Great grip and cushioning. Highly recommend.',
            'Looks even better in person. Love it.',
            'Decent shoe but runs slightly narrow.',
        ];

        Product::query()->inRandomOrder()->limit(12)->get()->each(function (Product $product, $idx) use ($customer, $bodies) {
            $rating = 4 + ($idx % 2);
            Review::create([
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'rating' => $rating,
                'title' => 'Worth it',
                'review' => $bodies[$idx % count($bodies)],
                'status' => 'approved',
            ]);
            $product->update(['rating' => $rating, 'reviews_count' => 1]);
        });
    }

    private function shoeImageUrl(int $seed): string
    {
        $photos = [
            '1542291026-7eec264c27ff',
            '1606107557195-0e29a4b5b4aa',
            '1560769629-975ec94e6a86',
            '1595950653106-6c9ebd614d3a',
            '1525966222134-fcfa99b8ae77',
            '1584735175315-9d5df23be7d0',
            '1600185365926-3a2ce3cdb9eb',
            '1491553895911-0055eca6402d',
            '1518894781321-630e638d0742',
            '1543163521-1bf539c55dd2',
            '1581067721837-88ad7cf43ab7',
            '1549298916-b41d501d3772',
            '1551107696-a4b0c5a0d9a2',
            '1539185441755-769473a23570',
            '1460353581641-37baddab0fa2',
            '1547592180-85f173990554',
            '1556906781-9a412961a28c',
            '1608231387042-66d1773070a5',
            '1582588678413-dbf45f4823e9',
            '1554735490-5974588cbc4a',
        ];

        return 'https://images.unsplash.com/photo-'.$photos[$seed % count($photos)].'?w=800&h=800&fit=crop&auto=format';
    }

    private function seedCoupons(): void
    {
        Coupon::create([
            'code' => 'WELCOME10', 'type' => 'percentage', 'discount_value' => 10,
            'min_order_amount' => 1000, 'max_discount' => 500,
            'usage_limit' => 1000, 'status' => 'active',
        ]);
        Coupon::create([
            'code' => 'FLAT200', 'type' => 'fixed', 'discount_value' => 200,
            'min_order_amount' => 1500, 'status' => 'active',
        ]);
    }
}
