<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PaymentMethod extends Enum
{
    public const CASH = 1;
    public const BANK_TRANSFER = 2;

    public static function getLabel($value): string
    {
        switch ($value) {
            case self::CASH:
                return 'Tiền mặt';
            case self::BANK_TRANSFER:
                return 'Chuyển khoản';
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
