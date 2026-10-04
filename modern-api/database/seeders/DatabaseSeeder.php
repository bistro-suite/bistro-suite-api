<?php

namespace Database\Seeders;

use App\Models\Bistro;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $bistro = Bistro::firstOrCreate(
            ['slug' => 'demo-bistro'],
            [
                'name' => 'Bistró de muestra', 'is_active' => true, 'is_demo' => true,
                'delivery_enabled' => true, 'delivery_fee_cop' => 5000,
                'delivery_neighborhoods' => ['Centro', 'Caobos', 'La Riviera'],
                'pickup_enabled' => true, 'pickup_address' => 'Dirección de muestra, Cúcuta',
            ],
        );

        $adminEmail = env('DEMO_ADMIN_EMAIL');
        $adminPassword = env('DEMO_ADMIN_PASSWORD');
        if (is_string($adminEmail) && $adminEmail !== '' && is_string($adminPassword) && $adminPassword !== '') {
            User::updateOrCreate(
                ['email' => mb_strtolower($adminEmail)],
                ['bistro_id' => $bistro->id, 'name' => 'Admin de muestra', 'password' => $adminPassword, 'is_active' => true],
            );
        }

        $products = [
            ['pastel-garbanzo', 'Pastel de garbanzo', 'Masa dorada y crocante rellena de garbanzo guisado, un clásico cucuteño.', 'Para empezar', 7000, true, '/images/menu/cucuta/pastel-garbanzo.webp', '🥟', 'Tradición cucuteña'],
            ['hayaca-cucutena', 'Hayaca nortesantandereana', 'Masa de maíz aliñada, guiso de carnes y vegetales, envuelta en hoja de plátano.', 'Para empezar', 16000, true, '/images/menu/cucuta/hayaca-nortesantandereana.webp', '🫔', 'Receta de la región'],
            ['cortado-leche-cabra', 'Cortado de leche de cabra', 'Dulce artesanal de leche de cabra cocida lentamente hasta quedar firme y acaramelada.', 'Algo dulce', 9000, true, '/images/menu/cucuta/cortado-leche-cabra.webp', '🍬', 'Dulce nortesantandereano'],
            ['mute-santandereano', 'Mute santandereano', 'Sopa espesa de maíz, garbanzo, papa y carnes, servida con arroz y limón.', 'Sopas y platos fuertes', 24000, true, '/images/menu/cucuta/mute-santandereano.webp', '🍲', 'Plato de la región'],
            ['cabrito-nortesantandereano', 'Cabrito con yuca y arepa', 'Cabrito asado acompañado de yuca, arepa de maíz y guiso de la casa.', 'Platos principales', 38000, true, '/images/menu/cucuta/cabrito.webp', '🍖', 'Tradición regional'],
            ['pollo-horno', 'Pollo al horno', 'Pollo asado con papas doradas, limón y jugos de cocción.', 'Platos principales', 34000, true, null, '🍗', 'Sin picante'],
            ['pasta-huerta', 'Pasta de la huerta', 'Pasta corta con vegetales asados, tomate y queso parmesano.', 'Platos principales', 30000, true, null, '🍝', 'Vegetariano'],
            ['trucha-plancha', 'Trucha a la plancha', 'Trucha con mantequilla de hierbas, puré rústico y ensalada.', 'Platos principales', 39000, true, null, '🐟', 'Recomendado'],
            ['bowl-criollo', 'Bowl criollo', 'Arroz, aguacate, fríjol, plátano maduro y vegetales frescos.', 'Platos principales', 29000, true, null, '🥑', 'Sin carne'],
            ['tarta-chocolate', 'Tarta de chocolate', 'Tarta de chocolate intenso con crema batida.', 'Algo dulce', 16000, true, null, '🍫', 'Postre'],
            ['flan-vainilla', 'Flan de vainilla', 'Flan suave con caramelo hecho en casa.', 'Algo dulce', 12000, true, null, '🍮', 'Postre'],
            ['cheesecake-guayaba', 'Cheesecake de guayaba', 'Cheesecake horneado con una capa de guayaba.', 'Algo dulce', 17000, true, null, '🍰', 'De temporada'],
            ['limonada-casa', 'Limonada de la casa', 'Limón recién exprimido. Elige con o sin azúcar.', 'Bebidas', 9000, true, null, '🍋', 'Bebida'],
            ['soda-frutos-rojos', 'Soda de frutos rojos', 'Soda artesanal con frutos rojos y limón.', 'Bebidas', 11000, true, null, '🫐', 'Bebida'],
            ['cafe-origen', 'Café de origen', 'Café colombiano filtrado, servido caliente.', 'Bebidas', 7000, true, null, '☕', 'Caliente'],
            ['masato-cucuteno', 'Masato de arroz', 'Bebida fría tradicional de arroz, ligeramente fermentada y aromatizada con canela.', 'Bebidas', 8000, true, '/images/menu/cucuta/masato.webp', '🥛', 'Bebida tradicional'],
            ['sopa-dia', 'Sopa del día', 'Sopa de temporada. Hoy no está disponible.', 'Para empezar', 15000, false, null, '🍲', 'No disponible'],
        ];

        foreach ($products as $order => [$slug, $name, $description, $category, $price, $available, $image, $illustration, $tag]) {
            $bistro->products()->firstOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $description, 'category' => $category,
                'price_cop' => $price, 'available' => $available, 'image_url' => $image,
                'illustration' => $illustration, 'tag' => $tag, 'display_order' => $order + 1,
            ]);
        }

        if ($bistro->is_demo) {
            $this->seedDemoOrders($bistro);
        }
    }

    private function seedDemoOrders(Bistro $bistro): void
    {
        $catalog = $bistro->products()->get()->keyBy('slug');
        $samples = [
            ['BS-DEMO0001', 'pending', 'delivery', 'Valentina Demo', '3000000001', 'valentina@example.test', 'Centro', 'Dirección ficticia · Centro', 2, [['pastel-garbanzo', 2, 'Sin picante'], ['limonada-casa', 1, null]]],
            ['BS-DEMO0002', 'pending', 'pickup', 'Mateo Prueba', '3000000002', 'mateo@example.test', null, null, 5, [['hayaca-cucutena', 2, null], ['cafe-origen', 2, 'Una sin azúcar']]],
            ['BS-DEMO0003', 'pending', 'delivery', 'Sara Muestra', '3000000003', null, 'Caobos', 'Dirección ficticia · Caobos', 9, [['mute-santandereano', 1, null], ['masato-cucuteno', 2, null]]],
            ['BS-DEMO0004', 'confirmed', 'delivery', 'Nicolás Ejemplo', '3000000004', 'nicolas@example.test', 'La Riviera', 'Dirección ficticia · La Riviera', 26, [['cabrito-nortesantandereano', 1, 'Arepa aparte'], ['soda-frutos-rojos', 2, null]]],
            ['BS-DEMO0005', 'confirmed', 'pickup', 'Laura Demo', '3000000005', 'laura@example.test', null, null, 31, [['pasta-huerta', 1, 'Sin queso'], ['cheesecake-guayaba', 1, null]]],
            ['BS-DEMO0006', 'delivered', 'delivery', 'Samuel Prueba', '3000000006', null, 'Centro', 'Dirección ficticia · Centro', 52, [['pollo-horno', 2, null], ['limonada-casa', 2, null]]],
            ['BS-DEMO0007', 'delivered', 'pickup', 'Isabela Muestra', '3000000007', 'isabela@example.test', null, null, 78, [['trucha-plancha', 1, null], ['flan-vainilla', 2, null]]],
            ['BS-DEMO0008', 'cancelled', 'delivery', 'Tomás Ejemplo', '3000000008', null, 'Caobos', 'Dirección ficticia · Caobos', 106, [['bowl-criollo', 1, null], ['soda-frutos-rojos', 1, 'Sin hielo']]],
        ];

        foreach ($samples as [$reference, $status, $method, $name, $phone, $email, $neighborhood, $address, $ageHours, $lines]) {
            $items = [];
            $subtotal = 0;
            foreach ($lines as [$slug, $quantity, $note]) {
                $product = $catalog->get($slug);
                if ($product === null) {
                    continue;
                }
                $lineTotal = $product->price_cop * $quantity;
                $subtotal += $lineTotal;
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price_cop' => $product->price_cop,
                    'quantity' => $quantity,
                    'note' => $note,
                    'line_total_cop' => $lineTotal,
                ];
            }

            if ($items === []) {
                continue;
            }

            $createdAt = now()->subHours($ageHours);
            $deliveryFee = $method === 'delivery' ? $bistro->delivery_fee_cop : 0;
            $order = $bistro->orders()->firstOrCreate(
                ['reference' => $reference],
                [
                    'idempotency_key' => sprintf('00000000-0000-4000-8000-%012d', (int) substr($reference, -4)),
                    'status' => $status,
                    'fulfillment_method' => $method,
                    'customer_name' => $name,
                    'customer_phone' => $phone,
                    'customer_email' => $email,
                    'neighborhood' => $neighborhood,
                    'delivery_address' => $address,
                    'pickup_address' => $method === 'pickup' ? $bistro->pickup_address : null,
                    'subtotal_cop' => $subtotal,
                    'delivery_fee_cop' => $deliveryFee,
                    'total_cop' => $subtotal + $deliveryFee,
                ],
            );

            if ($order->wasRecentlyCreated) {
                $order->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt]);
                $order->timestamps = false;
                $order->save();
                $order->items()->createMany($items);
            }
        }
    }
}
