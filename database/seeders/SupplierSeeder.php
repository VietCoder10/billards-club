<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['supplier_name' => 'Công ty TNHH Nước Giải Khát Coca-Cola Việt Nam', 'phone' => '02838961000', 'email' => 'contact@cocacola.com.vn'],
            ['supplier_name' => 'Công ty Cổ phần Suntory PepsiCo Việt Nam', 'phone' => '02838219437', 'email' => 'info@pepsico.com.vn'],
            ['supplier_name' => 'Đại lý Bia & Nước ngọt Hoàng Gia', 'phone' => '0908123456', 'email' => 'hoanggia.drinks@gmail.com'],
            ['supplier_name' => 'Nhà phân phối Thực phẩm sạch Organica', 'phone' => '02866737373', 'email' => 'sales@organica.vn'],
            ['supplier_name' => 'Công ty Cổ phần Thực phẩm Kinh Đô', 'phone' => '02838270838', 'email' => 'customer.service@kinhdo.vn'],
            ['supplier_name' => 'Cửa hàng dụng cụ Billiards Thanh Sơn', 'phone' => '0903334455', 'email' => 'thanhson.billiards@gmail.com'],
            ['supplier_name' => 'Nhà cung cấp phụ kiện Adam & Fury', 'phone' => '0912345678', 'email' => 'accessories@adamfury.vn'],
            ['supplier_name' => 'Công ty TNHH MTV Thực phẩm Masan', 'phone' => '02862563862', 'email' => 'masan.food@masangroup.com'],
            ['supplier_name' => 'Nhà máy nước khoáng La Vie', 'phone' => '02439446666', 'email' => 'lavie.hn@nestle.com'],
            ['supplier_name' => 'Hợp tác xã rau củ quả Đà Lạt', 'phone' => '02633822123', 'email' => 'dalat.veggi@lamdong.gov.vn'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create(array_merge($supplier, [
                'address' => 'Vietnam',
                'note' => 'Nhà cung cấp uy tín phục vụ lâu dài.',
            ]));
        }
    }
}
