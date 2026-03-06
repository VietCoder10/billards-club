<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class OrderStatus extends Enum
{
    public const PENDING = 1;        // Đang chờ
    public const COMPLETED = 2;      // Hoàn thành
    public const CANCELLED = 3;      // Đã hủy

    public static function getLabel($value): string
    {
        switch ($value) {
            case self::PENDING:
                return 'Đang phục vụ';
            case self::COMPLETED:
                return 'Hoàn thành';
            case self::CANCELLED:
                return 'Đã hủy';
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
