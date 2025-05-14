<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('is_admin', false)->take(5)->get();

        foreach ($users as $user) {
            $orderCount = rand(2, 4);

            for ($i = 0; $i < $orderCount; $i++) {
                $status = $this->getRandomStatus();

                $products = Product::inRandomOrder()->take(rand(1, 3))->get();

                $subtotal = 0;
                $orderItems = [];

                foreach ($products as $product) {
                    $useVariation = (bool)rand(0, 1);
                    $variation = null;

                    if ($useVariation && $product->variations->count() > 0) {
                        $variation = $product->variations->random();
                    }

                    $quantity = rand(1, 3);
                    $price = $product->price;
                    $itemSubtotal = $price * $quantity;
                    $subtotal += $itemSubtotal;

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'product_variation_id' => $variation ? $variation->id : null,
                        'quantity' => $quantity,
                        'price' => $price,
                    ];
                }

                $shipping = $this->calculateShipping($subtotal);

                $discount = 0;
                $couponCode = null;

                if (rand(1, 100) <= 30) {
                    $couponCode = $this->getRandomCouponCode();
                    $discount = $this->calculateDiscount($subtotal, $couponCode);
                }

                $total = $subtotal + $shipping - $discount;

                $order = Order::create([
                    'user_id' => $user->id,
                    'subtotal' => $subtotal,
                    'shipping' => $shipping,
                    'discount' => $discount,
                    'total' => $total,
                    'coupon_code' => $couponCode,
                    'status' => $status,
                    'cep' => $this->getRandomCep(),
                    'address' => 'Rua de Exemplo, ' . rand(100, 999),
                    'number' => (string)rand(1, 999),
                    'complement' => rand(0, 1) ? 'Apto ' . rand(1, 100) : null,
                    'neighborhood' => 'Bairro Exemplo',
                    'city' => $this->getRandomCity(),
                    'state' => $this->getRandomState(),
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);

                foreach ($orderItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_variation_id' => $item['product_variation_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
            }
        }
    }

    private function getRandomStatus()
    {
        $statuses = ['pending', 'processing', 'completed', 'canceled'];
        $weights = [40, 30, 25, 5];

        return $this->getRandomWeighted($statuses, $weights);
    }

    private function getRandomCouponCode()
    {
        $coupons = ['DESCONTO10', 'DESCONTO15', 'MENOS30REAIS', 'FRETEGRATIS'];
        return $coupons[array_rand($coupons)];
    }

    private function calculateShipping($subtotal)
    {
        if ($subtotal >= 200) {
            return 0;
        } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15;
        }

        return 20;
    }

    private function calculateDiscount($subtotal, $couponCode)
    {
        switch ($couponCode) {
            case 'DESCONTO10':
                return $subtotal >= 100 ? $subtotal * 0.1 : 0;
            case 'DESCONTO15':
                return $subtotal >= 200 ? $subtotal * 0.15 : 0;
            case 'MENOS30REAIS':
                return $subtotal >= 150 ? 30 : 0;
            case 'FRETEGRATIS':
                return $subtotal >= 120 ? 20 : 0;
            default:
                return 0;
        }
    }

    private function getRandomCep()
    {
        $ceps = [
            '01001000',
            '20050000',
            '30170010',
            '80010010',
            '90010000',
            '70070010',
            '50050530',
            '41830020',
        ];

        return $ceps[array_rand($ceps)];
    }

    private function getRandomCity()
    {
        $cities = [
            'São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba',
            'Porto Alegre', 'Brasília', 'Recife', 'Salvador', 'Fortaleza',
            'Manaus', 'Florianópolis', 'Goiânia', 'Belém', 'Campinas',
        ];

        return $cities[array_rand($cities)];
    }

    private function getRandomState()
    {
        $states = [
            'SP', 'RJ', 'MG', 'PR', 'RS', 'DF', 'PE', 'BA', 'CE',
            'AM', 'SC', 'GO', 'PA', 'ES', 'MA',
        ];

        return $states[array_rand($states)];
    }

    private function getRandomWeighted($items, $weights)
    {
        $sum = array_sum($weights);
        $rand = mt_rand(1, $sum);

        foreach ($items as $i => $item) {
            $rand -= $weights[$i];
            if ($rand <= 0) {
                return $item;
            }
        }

        return $items[0];
    }
}
