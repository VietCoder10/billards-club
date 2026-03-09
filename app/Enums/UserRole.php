<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserRole extends Enum
{
    public const ADMIN = 1;
    public const USER = 2;

    public static function getLabel($value): string
    {
        switch ($value) {
            case self::ADMIN:
                return 'Quản trị viên';
            case self::USER:
                return 'Nhân viên';
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
