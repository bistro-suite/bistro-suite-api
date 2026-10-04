<?php

namespace Database\Seeders;

use App\Models\Bistro;
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
    }
}
