<?php

use App\Models\Bistro;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    private array $products = [
        'rollitos-primavera' => [
            'slug' => 'pastel-garbanzo', 'name' => 'Pastel de garbanzo',
            'description' => 'Masa dorada y crocante rellena de garbanzo guisado, un clásico cucuteño.',
            'category' => 'Para empezar', 'price_cop' => 7000, 'image_url' => '/images/menu/cucuta/pastel-garbanzo.webp',
            'illustration' => '🥟', 'tag' => 'Tradición cucuteña',
        ],
        'arepitas-hogao' => [
            'slug' => 'hayaca-cucutena', 'name' => 'Hayaca nortesantandereana',
            'description' => 'Masa de maíz aliñada, guiso de carnes y vegetales, envuelta en hoja de plátano.',
            'category' => 'Para empezar', 'price_cop' => 16000, 'image_url' => '/images/menu/cucuta/hayaca-nortesantandereana.webp',
            'illustration' => '🫔', 'tag' => 'Receta de la región',
        ],
        'croquetas-yuca' => [
            'slug' => 'cortado-leche-cabra', 'name' => 'Cortado de leche de cabra',
            'description' => 'Dulce artesanal de leche de cabra cocida lentamente hasta quedar firme y acaramelada.',
            'category' => 'Algo dulce', 'price_cop' => 9000, 'image_url' => '/images/menu/cucuta/cortado-leche-cabra.webp',
            'illustration' => '🍬', 'tag' => 'Dulce nortesantandereano',
        ],
        'ensalada-burrata' => [
            'slug' => 'mute-santandereano', 'name' => 'Mute santandereano',
            'description' => 'Sopa espesa de maíz, garbanzo, papa y carnes, servida con arroz y limón.',
            'category' => 'Sopas y platos fuertes', 'price_cop' => 24000, 'image_url' => '/images/menu/cucuta/mute-santandereano.webp',
            'illustration' => '🍲', 'tag' => 'Plato de la región',
        ],
        'arroz-setas' => [
            'slug' => 'cabrito-nortesantandereano', 'name' => 'Cabrito con yuca y arepa',
            'description' => 'Cabrito asado acompañado de yuca, arepa de maíz y guiso de la casa.',
            'category' => 'Platos principales', 'price_cop' => 38000, 'image_url' => '/images/menu/cucuta/cabrito.webp',
            'illustration' => '🍖', 'tag' => 'Tradición regional',
        ],
        'te-maracuya' => [
            'slug' => 'masato-cucuteno', 'name' => 'Masato de arroz',
            'description' => 'Bebida fría tradicional de arroz, ligeramente fermentada y aromatizada con canela.',
            'category' => 'Bebidas', 'price_cop' => 8000, 'image_url' => '/images/menu/cucuta/masato.webp',
            'illustration' => '🥛', 'tag' => 'Bebida tradicional',
        ],
    ];

    public function up(): void
    {
        $bistro = Bistro::query()->where('slug', 'demo-bistro')->where('is_demo', true)->first();
        if ($bistro === null) {
            return;
        }

        foreach ($this->products as $oldSlug => $attributes) {
            $bistro->products()->where('slug', $oldSlug)->update($attributes);
        }
    }

    public function down(): void
    {
        $bistro = Bistro::query()->where('slug', 'demo-bistro')->where('is_demo', true)->first();
        if ($bistro === null) {
            return;
        }

        $oldData = [
            'pastel-garbanzo' => ['rollitos-primavera', 'Rollitos de primavera', 'Rollitos crujientes rellenos de vegetales, con salsa agridulce.', 'Para empezar', 18000, '/images/menu/legacy-rollitos-primavera-demo.jpg', '🥟', 'Receta de muestra'],
            'hayaca-cucutena' => ['arepitas-hogao', 'Arepitas con hogao', 'Arepitas doradas de maíz blanco con hogao casero.', 'Para empezar', 14000, null, '🫓', 'Para compartir'],
            'cortado-leche-cabra' => ['croquetas-yuca', 'Croquetas de yuca', 'Bocados de yuca con centro suave y alioli de cilantro.', 'Para empezar', 17000, null, '🥔', 'Favorito'],
            'mute-santandereano' => ['ensalada-burrata', 'Ensalada de burrata', 'Tomate de temporada, hojas frescas, burrata y aceite de albahaca.', 'Para empezar', 26000, null, '🥗', 'Vegetariano'],
            'cabrito-nortesantandereano' => ['arroz-setas', 'Arroz cremoso de setas', 'Arroz cocido lentamente con setas salteadas y queso curado.', 'Platos principales', 32000, null, '🍄', 'Favorito de la casa'],
            'masato-cucuteno' => ['te-maracuya', 'Té frío de maracuyá', 'Té negro frío con pulpa natural de maracuyá.', 'Bebidas', 8500, null, '🧋', 'Frío'],
        ];

        foreach ($oldData as $newSlug => [$slug, $name, $description, $category, $price, $image, $illustration, $tag]) {
            $bistro->products()->where('slug', $newSlug)->update([
                'slug' => $slug, 'name' => $name, 'description' => $description, 'category' => $category,
                'price_cop' => $price, 'image_url' => $image, 'illustration' => $illustration, 'tag' => $tag,
            ]);
        }
    }
};
