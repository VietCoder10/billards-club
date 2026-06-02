<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Status extends Enum
{
    public const ACTIVE = 1;
    public const INACTIVE = 2;
    public static function getLabel($value): string
    {
        switch ($value) {
            case self::ACTIVE:
                return 'Hoạt động';
            case self::INACTIVE:
                return 'Không hoạt động';
            default:
                return '';
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
