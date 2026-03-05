<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TableStatus extends Enum
{
    public const AVAILABLE = 0;     // Có sẵn
    public const IN_USE = 1;        // Đang sử dụng
    public const MAINTENANCE = 2;   // Bảo trì

    public static function getLabel($value): string
    {
        switch ($value) {
            case self::AVAILABLE:
                return 'Có sẵn';
            case self::IN_USE:
                return 'Đang sử dụng';
            case self::MAINTENANCE:
                return 'Bảo trì';
            default:
                return self::getInvalidLabel();
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
