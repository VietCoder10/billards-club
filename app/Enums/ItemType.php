<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class ItemType extends Enum
{
    public const PRODUCT = 1; // Đồ ăn, thức uống (từ bảng products)
    public const SERVICE = 2;  // Tiền giờ, phí dịch vụ (từ logic hoặc tables)
    public const COMBO = 3;      // Các gói combo (sau này)

    public static function getLabel($value): string
    {
        switch ($value) {
            case self::PRODUCT:
                return 'Sản phẩm';
            case self::SERVICE:
                return 'Dịch vụ';
            case self::COMBO:
                return 'Combo';
            default:
                return 'Khác';
        }
    }
}
