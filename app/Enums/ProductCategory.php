<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class ProductCategory extends Enum
{
    public const FOOD = 1;        // Đồ ăn
    public const DRINK = 2;       // Đồ uống
    public const CUE_RENTAL = 3;  // Thuê Gậy

    public static function getLabel($value): string
    {
        switch ($value) {
            case self::FOOD:
                return 'Đồ ăn';
            case self::DRINK:
                return 'Đồ uống';
            case self::CUE_RENTAL:
                return 'Thuê Gậy';
            default:
                return 'Khác';
        }
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::getValues() as $value) {
            $options[] = [
                'label' => self::getLabel($value),
                'value' => $value,
            ];
        }
        return $options;
    }
}
