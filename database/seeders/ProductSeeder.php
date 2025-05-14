<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Stock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createElectronicsProducts();
        $this->createClothingProducts();
        $this->createFootwearProducts();
        $this->createAccessoriesProducts();
        $this->createHomeProducts();
    }

    private function createElectronicsProducts()
    {
        $category = Category::where('slug', 'eletronicos')->first();

        $smartphone = Product::create([
            'name' => 'Smartphone XYZ Última Geração',
            'price' => 1999.90,
            'description' => 'Smartphone com tecnologia de ponta, tela AMOLED de 6.5", 128GB de armazenamento e 8GB de RAM.',
            'category_id' => $category->id,
            'active' => true,
        ]);

        $colors = ['Preto', 'Branco', 'Azul'];
        $storages = ['128GB', '256GB'];

        $smartphoneTotal = 15;
        $smartphoneVariations = [];

        foreach ($colors as $color) {
            foreach ($storages as $storage) {
                if ($storage === '256GB' && $color !== 'Preto') {
                    continue;
                }
                $smartphoneVariations[] = ["$color - $storage", 0];
            }
        }

        $this->distributeStock($smartphoneVariations, $smartphoneTotal);

        Stock::create([
            'product_id' => $smartphone->id,
            'quantity' => $smartphoneTotal,
        ]);

        foreach ($smartphoneVariations as $variation) {
            $productVariation = ProductVariation::create([
                'product_id' => $smartphone->id,
                'name' => $variation[0],
            ]);

            Stock::create([
                'product_id' => $smartphone->id,
                'product_variation_id' => $productVariation->id,
                'quantity' => $variation[1],
            ]);
        }

        $notebook = Product::create([
            'name' => 'Notebook Profissional 15"',
            'price' => 3899.90,
            'description' => 'Notebook com processador de última geração, tela Full HD de 15", SSD de 512GB e 16GB de RAM.',
            'category_id' => $category->id,
            'active' => true,
        ]);

        $processors = ['Intel i5', 'Intel i7', 'AMD Ryzen 7'];
        $notebookTotal = 8;
        $notebookVariations = [];

        foreach ($processors as $processor) {
            $notebookVariations[] = [$processor, 0];
        }

        $this->distributeStock($notebookVariations, $notebookTotal);

        Stock::create([
            'product_id' => $notebook->id,
            'quantity' => $notebookTotal,
        ]);

        foreach ($notebookVariations as $variation) {
            $productVariation = ProductVariation::create([
                'product_id' => $notebook->id,
                'name' => $variation[0],
            ]);

            Stock::create([
                'product_id' => $notebook->id,
                'product_variation_id' => $productVariation->id,
                'quantity' => $variation[1],
            ]);
        }

        $headphone = Product::create([
            'name' => 'Fone de Ouvido Bluetooth',
            'price' => 199.90,
            'description' => 'Fone de ouvido sem fio com cancelamento de ruído, bateria de longa duração e qualidade de áudio premium.',
            'category_id' => $category->id,
            'active' => true,
        ]);

        $colors = ['Preto', 'Branco', 'Vermelho'];
        $headphoneTotal = 30;
        $headphoneVariations = [];

        foreach ($colors as $color) {
            $headphoneVariations[] = [$color, 0];
        }

        $this->distributeStock($headphoneVariations, $headphoneTotal);

        Stock::create([
            'product_id' => $headphone->id,
            'quantity' => $headphoneTotal,
        ]);

        foreach ($headphoneVariations as $variation) {
            $productVariation = ProductVariation::create([
                'product_id' => $headphone->id,
                'name' => $variation[0],
            ]);

            Stock::create([
                'product_id' => $headphone->id,
                'product_variation_id' => $productVariation->id,
                'quantity' => $variation[1],
            ]);
        }
    }

    private function createClothingProducts()
    {
        $category = Category::where('slug', 'roupas')->first();

        $tshirt = Product::create([
            'name' => 'Camiseta Básica Premium',
            'price' => 59.90,
            'description' => 'Camiseta de algodão de alta qualidade, corte moderno e confortável para o dia a dia.',
            'category_id' => $category->id,
            'active' => true,
        ]);

        $colors = ['Preta', 'Branca', 'Azul', 'Vermelha'];
        $sizes = ['P', 'M', 'G', 'GG'];
        $tshirtTotal = 50;
        $tshirtVariations = [];

        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $tshirtVariations[] = ["$color - Tamanho $size", 0];
            }
        }

        $this->distributeStock($tshirtVariations, $tshirtTotal);

        Stock::create([
            'product_id' => $tshirt->id,
            'quantity' => $tshirtTotal,
        ]);

        foreach ($tshirtVariations as $variation) {
            $productVariation = ProductVariation::create([
                'product_id' => $tshirt->id,
                'name' => $variation[0],
            ]);

            Stock::create([
                'product_id' => $tshirt->id,
                'product_variation_id' => $productVariation->id,
                'quantity' => $variation[1],
            ]);
        }

        $jacket = Product::create([
            'name' => 'Jaqueta Jeans Moderna',
            'price' => 149.90,
            'description' => 'Jaqueta jeans de alta qualidade com lavagem especial, estilo moderno e confortável.',
            'category_id' => $category->id,
            'active' => true,
        ]);

        $washes = ['Clara', 'Média', 'Escura'];
        $sizes = ['P', 'M', 'G', 'GG'];
        $jacketTotal = 25;
        $jacketVariations = [];

        foreach ($washes as $wash) {
            foreach ($sizes as $size) {
                $jacketVariations[] = ["Lavagem $wash - Tamanho $size", 0];
            }
        }

        $this->distributeStock($jacketVariations, $jacketTotal);

        Stock::create([
            'product_id' => $jacket->id,
            'quantity' => $jacketTotal,
        ]);

        foreach ($jacketVariations as $variation) {
            $productVariation = ProductVariation::create([
                'product_id' => $jacket->id,
                'name' => $variation[0],
            ]);

            Stock::create([
                'product_id' => $jacket->id,
                'product_variation_id' => $productVariation->id,
                'quantity' => $variation[1],
            ]);
        }
    }

    private function createFootwearProducts()
    {
        $category = Category::where('slug', 'calcados')->first();

        $sneakers = Product::create([
            'name' => 'Tênis Esportivo Confortável',
            'price' => 229.90,
            'description' => 'Tênis esportivo com tecnologia de amortecimento, respirável e ultra confortável para atividades físicas.',
            'category_id' => $category->id,
            'active' => true,
        ]);

        $colors = ['Preto', 'Branco', 'Cinza'];
        $sizes = ['38', '39', '40', '41', '42', '43'];
        $sneakersTotal = 30;
        $sneakersVariations = [];

        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $sneakersVariations[] = ["$color - Tamanho $size", 0];
            }
        }

        $this->distributeStock($sneakersVariations, $sneakersTotal);

        Stock::create([
            'product_id' => $sneakers->id,
            'quantity' => $sneakersTotal,
        ]);

        foreach ($sneakersVariations as $variation) {
            $productVariation = ProductVariation::create([
                'product_id' => $sneakers->id,
                'name' => $variation[0],
            ]);

            Stock::create([
                'product_id' => $sneakers->id,
                'product_variation_id' => $productVariation->id,
                'quantity' => $variation[1],
            ]);
        }
    }

    private function createAccessoriesProducts()
    {
        $category = Category::where('slug', 'acessorios')->first();

        $watch = Product::create([
            'name' => 'Relógio Multifuncional Elegante',
            'price' => 299.90,
            'description' => 'Relógio moderno com diversas funções, resistente à água e design elegante para ocasiões formais e casuais.',
            'category_id' => $category->id,
            'active' => true,
        ]);

        $colors = ['Preto', 'Prata', 'Dourado'];
        $watchTotal = 15;
        $watchVariations = [];

        foreach ($colors as $color) {
            $watchVariations[] = [$color, 0];
        }

        $this->distributeStock($watchVariations, $watchTotal);

        Stock::create([
            'product_id' => $watch->id,
            'quantity' => $watchTotal,
        ]);

        foreach ($watchVariations as $variation) {
            $productVariation = ProductVariation::create([
                'product_id' => $watch->id,
                'name' => $variation[0],
            ]);

            Stock::create([
                'product_id' => $watch->id,
                'product_variation_id' => $productVariation->id,
                'quantity' => $variation[1],
            ]);
        }

        $bag = Product::create([
            'name' => 'Bolsa Transversal Moderna',
            'price' => 139.90,
            'description' => 'Bolsa transversal versátil, ideal para o dia a dia, com compartimentos internos e acabamento premium.',
            'category_id' => $category->id,
            'active' => true,
        ]);

        $colors = ['Preto', 'Marrom', 'Bege'];
        $bagTotal = 12;
        $bagVariations = [];

        foreach ($colors as $color) {
            $bagVariations[] = [$color, 0];
        }

        $this->distributeStock($bagVariations, $bagTotal);

        Stock::create([
            'product_id' => $bag->id,
            'quantity' => $bagTotal,
        ]);

        foreach ($bagVariations as $variation) {
            $productVariation = ProductVariation::create([
                'product_id' => $bag->id,
                'name' => $variation[0],
            ]);

            Stock::create([
                'product_id' => $bag->id,
                'product_variation_id' => $productVariation->id,
                'quantity' => $variation[1],
            ]);
        }
    }

    private function createHomeProducts()
    {
        $category = Category::where('slug', 'casa-decoracao')->first();

        $lamp = Product::create([
            'name' => 'Luminária de Mesa Moderna',
            'price' => 89.90,
            'description' => 'Luminária de mesa com design moderno, LED de alta eficiência e ajuste de intensidade.',
            'category_id' => $category->id,
            'active' => true,
        ]);

        $colors = ['Preta', 'Branca', 'Prata'];
        $lampTotal = 18;
        $lampVariations = [];

        foreach ($colors as $color) {
            $lampVariations[] = [$color, 0];
        }

        $this->distributeStock($lampVariations, $lampTotal);

        Stock::create([
            'product_id' => $lamp->id,
            'quantity' => $lampTotal,
        ]);

        foreach ($lampVariations as $variation) {
            $productVariation = ProductVariation::create([
                'product_id' => $lamp->id,
                'name' => $variation[0],
            ]);

            Stock::create([
                'product_id' => $lamp->id,
                'product_variation_id' => $productVariation->id,
                'quantity' => $variation[1],
            ]);
        }

        $glasses = Product::create([
            'name' => 'Conjunto de Copos 6 Peças',
            'price' => 49.90,
            'description' => 'Conjunto com 6 copos de vidro de alta qualidade, design elegante e resistente para uso diário.',
            'category_id' => $category->id,
            'active' => true,
        ]);

        $types = ['Água', 'Vinho', 'Whisky'];
        $glassesTotal = 25;
        $glassesVariations = [];

        foreach ($types as $type) {
            $glassesVariations[] = ["Copos para $type", 0];
        }

        $this->distributeStock($glassesVariations, $glassesTotal);

        Stock::create([
            'product_id' => $glasses->id,
            'quantity' => $glassesTotal,
        ]);

        foreach ($glassesVariations as $variation) {
            $productVariation = ProductVariation::create([
                'product_id' => $glasses->id,
                'name' => $variation[0],
            ]);

            Stock::create([
                'product_id' => $glasses->id,
                'product_variation_id' => $productVariation->id,
                'quantity' => $variation[1],
            ]);
        }
    }

    private function distributeStock(array &$variations, int $totalStock): void
    {
        $variationCount = count($variations);

        if ($variationCount === 0) {
            return;
        }

        $baseAmount = floor($totalStock / $variationCount);
        $remaining = $totalStock - ($baseAmount * $variationCount);

        for ($i = 0; $i < $variationCount; $i++) {
            $variations[$i][1] = $baseAmount;

            if ($remaining > 0) {
                $variations[$i][1]++;
                $remaining--;
            }
        }
    }
}
