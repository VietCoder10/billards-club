<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'supplier_name' => 'Công ty TNHH Bi-a ' . $this->faker->unique()->numberBetween(1, 200),

            'contact_person' => $this->faker->randomElement([
                'Nguyễn Văn An',
                'Trần Minh Tuấn',
                'Lê Hoàng Nam',
                'Phạm Quốc Bảo',
                'Hoàng Gia Huy',
                'Võ Thành Đạt',
                'Đỗ Minh Quân',
                'Bùi Thanh Tùng',
                'Ngô Văn Khánh',
                'Phan Đức Anh'
            ]),

            'email' => 'nhacungcap' . $this->faker->unique()->numberBetween(1, 9999) . '@gmail.com',

            'phone' => '09' . $this->faker->numberBetween(10000000, 99999999),

            'address' => 'Số ' . $this->faker->numberBetween(1, 500)
                . ' Đường ' . $this->faker->randomElement([
                    'Lê Lợi',
                    'Nguyễn Huệ',
                    'Trần Hưng Đạo',
                    'Phạm Văn Đồng',
                    'Cách Mạng Tháng 8',
                    'Nguyễn Trãi',
                    'Hai Bà Trưng'
                ])
                . ', Quận ' . $this->faker->numberBetween(1, 12)
                . ', TP.HCM',

            'note' => 'Chuyên cung cấp bàn, bi, gậy và phụ kiện bi-a',

            'status' => 1,
        ];
    }
}
