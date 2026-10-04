<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Realistic development catalogue. Specifications are plain key/value pairs
     * so the same JSON column serves laptops, monitors, phones and accessories.
     *
     * @var array<int, array{
     *     category: string,
     *     name: string,
     *     brand: string,
     *     price: int,
     *     stock: int,
     *     warranty_months: int,
     *     specs: array<string, string>
     * }>
     */
    private const PRODUCTS = [
        [
            'category' => 'laptop',
            'name' => 'Asus ROG Zephyrus G14 (2025)',
            'brand' => 'Asus',
            'price' => 24999000,
            'stock' => 8,
            'warranty_months' => 24,
            'specs' => [
                'Processor' => 'AMD Ryzen 9 8945HS',
                'RAM' => '16 GB LPDDR5X 6400 MHz',
                'Penyimpanan' => '1 TB NVMe PCIe 4.0 SSD',
                'Layar' => '14 inci QHD+ 120 Hz OLED',
                'Kartu Grafis' => 'NVIDIA GeForce RTX 4060 8 GB',
                'Berat' => '1,5 kg',
                'Sistem Operasi' => 'Windows 11 Home',
            ],
        ],
        [
            'category' => 'laptop',
            'name' => 'Lenovo ThinkPad X1 Carbon Gen 12',
            'brand' => 'Lenovo',
            'price' => 28500000,
            'stock' => 5,
            'warranty_months' => 36,
            'specs' => [
                'Processor' => 'Intel Core Ultra 7 155U',
                'RAM' => '32 GB LPDDR5X',
                'Penyimpanan' => '1 TB NVMe SSD',
                'Layar' => '14 inci WUXGA IPS anti-glare',
                'Baterai' => '57 Wh dengan fast charging 65 W',
                'Berat' => '1,09 kg',
                'Sistem Operasi' => 'Windows 11 Pro',
            ],
        ],
        [
            'category' => 'laptop',
            'name' => 'Apple MacBook Air 13 M3',
            'brand' => 'Apple',
            'price' => 16999000,
            'stock' => 12,
            'warranty_months' => 12,
            'specs' => [
                'Chip' => 'Apple M3 (8 core CPU, 8 core GPU)',
                'Memori' => '8 GB unified memory',
                'Penyimpanan' => '256 GB SSD',
                'Layar' => '13,6 inci Liquid Retina',
                'Baterai' => 'Hingga 18 jam pemakaian',
                'Berat' => '1,24 kg',
                'Sistem Operasi' => 'macOS Sonoma',
            ],
        ],
        [
            'category' => 'monitor',
            'name' => 'LG UltraGear 27GR75Q',
            'brand' => 'LG',
            'price' => 4299000,
            'stock' => 15,
            'warranty_months' => 12,
            'specs' => [
                'Ukuran' => '27 inci',
                'Resolusi' => 'QHD 2560 x 1440',
                'Panel' => 'IPS',
                'Refresh Rate' => '165 Hz',
                'Response Time' => '1 ms (GtG)',
                'Konektivitas' => '2x HDMI 2.0, 1x DisplayPort 1.4',
            ],
        ],
        [
            'category' => 'monitor',
            'name' => 'Samsung Odyssey G5 32"',
            'brand' => 'Samsung',
            'price' => 5199000,
            'stock' => 9,
            'warranty_months' => 12,
            'specs' => [
                'Ukuran' => '32 inci',
                'Resolusi' => 'QHD 2560 x 1440',
                'Panel' => 'VA',
                'Refresh Rate' => '165 Hz',
                'Kelengkungan' => '1000R',
                'HDR' => 'HDR10',
            ],
        ],
        [
            'category' => 'monitor',
            'name' => 'Dell UltraSharp U2723QE',
            'brand' => 'Dell',
            'price' => 8750000,
            'stock' => 7,
            'warranty_months' => 36,
            'specs' => [
                'Ukuran' => '27 inci',
                'Resolusi' => '4K UHD 3840 x 2160',
                'Panel' => 'IPS Black',
                'Cakupan Warna' => '98% DCI-P3',
                'Konektivitas' => 'USB-C 90 W, HDMI, DisplayPort, RJ45',
                'Fitur' => 'USB hub dan KVM switch',
            ],
        ],
        [
            'category' => 'smartphone',
            'name' => 'Samsung Galaxy S24',
            'brand' => 'Samsung',
            'price' => 12999000,
            'stock' => 20,
            'warranty_months' => 12,
            'specs' => [
                'Layar' => '6,2 inci Dynamic AMOLED 2X 120 Hz',
                'Chipset' => 'Exynos 2400',
                'RAM' => '8 GB',
                'Penyimpanan' => '256 GB',
                'Kamera Utama' => '50 MP + 12 MP ultrawide + 10 MP telephoto',
                'Baterai' => '4000 mAh, 25 W',
                'Sistem Operasi' => 'Android 14, One UI 6.1',
            ],
        ],
        [
            'category' => 'smartphone',
            'name' => 'Xiaomi 14T Pro',
            'brand' => 'Xiaomi',
            'price' => 8499000,
            'stock' => 18,
            'warranty_months' => 12,
            'specs' => [
                'Layar' => '6,67 inci AMOLED 144 Hz',
                'Chipset' => 'MediaTek Dimensity 9300+',
                'RAM' => '12 GB',
                'Penyimpanan' => '512 GB',
                'Kamera Utama' => '50 MP Leica + 50 MP telephoto + 12 MP ultrawide',
                'Baterai' => '5000 mAh, 120 W HyperCharge',
                'Sistem Operasi' => 'Android 14, HyperOS',
            ],
        ],
        [
            'category' => 'smartphone',
            'name' => 'Oppo Reno 12 Pro',
            'brand' => 'Oppo',
            'price' => 7499000,
            'stock' => 14,
            'warranty_months' => 12,
            'specs' => [
                'Layar' => '6,7 inci AMOLED 120 Hz',
                'Chipset' => 'MediaTek Dimensity 7300 Energy',
                'RAM' => '12 GB',
                'Penyimpanan' => '256 GB',
                'Kamera Utama' => '50 MP + 50 MP telephoto + 8 MP ultrawide',
                'Baterai' => '5000 mAh, 80 W SuperVOOC',
                'Sistem Operasi' => 'Android 14, ColorOS 14.1',
            ],
        ],
        [
            'category' => 'keyboard',
            'name' => 'Keychron K2 Pro',
            'brand' => 'Keychron',
            'price' => 1499000,
            'stock' => 6,
            'warranty_months' => 12,
            'specs' => [
                'Layout' => '75% (84 tombol)',
                'Switch' => 'Keychron K Pro Brown',
                'Konektivitas' => 'Bluetooth 5.1 dan USB-C',
                'Baterai' => '4000 mAh',
                'Hot-swappable' => 'Ya',
                'Backlight' => 'RGB',
            ],
        ],
        [
            'category' => 'keyboard',
            'name' => 'Logitech MX Keys S',
            'brand' => 'Logitech',
            'price' => 1650000,
            'stock' => 11,
            'warranty_months' => 12,
            'specs' => [
                'Layout' => 'Full size dengan numpad',
                'Konektivitas' => 'Bluetooth dan Logi Bolt',
                'Baterai' => 'Hingga 10 hari dengan backlight aktif',
                'Backlight' => 'Smart backlight adaptif',
                'Kompatibilitas' => 'Windows, macOS, Linux, iPadOS',
                'Fitur' => 'Multi-device hingga 3 perangkat',
            ],
        ],
        [
            'category' => 'mouse',
            'name' => 'Logitech MX Master 3S',
            'brand' => 'Logitech',
            'price' => 1299000,
            'stock' => 16,
            'warranty_months' => 12,
            'specs' => [
                'Sensor' => 'Darkfield 8000 DPI',
                'Tombol' => '7 tombol dapat dikustomisasi',
                'Konektivitas' => 'Bluetooth dan Logi Bolt',
                'Baterai' => 'Hingga 70 hari',
                'Klik' => 'Quiet click',
                'Fitur' => 'Multi-device hingga 3 perangkat',
            ],
        ],
        [
            'category' => 'mouse',
            'name' => 'Razer DeathAdder V3',
            'brand' => 'Razer',
            'price' => 899000,
            'stock' => 0,
            'warranty_months' => 24,
            'specs' => [
                'Sensor' => 'Razer Focus Pro 30K DPI',
                'Tombol' => '6 tombol',
                'Berat' => '59 gram',
                'Konektivitas' => 'USB-C wired',
                'Polling Rate' => 'Hingga 8000 Hz',
                'Switch' => 'Razer Optical Gen-3',
            ],
        ],
    ];

    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');

        foreach (self::PRODUCTS as $product) {
            $categoryId = $categories[$product['category']] ?? null;

            if ($categoryId === null) {
                continue;
            }

            Product::query()->updateOrCreate(
                ['slug' => str($product['name'])->slug()->value()],
                [
                    'category_id' => $categoryId,
                    'name' => $product['name'],
                    'brand' => $product['brand'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'warranty_months' => $product['warranty_months'],
                    'specs' => $product['specs'],
                    'is_active' => true,
                ],
            );
        }
    }
}
