<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TournamentStatus extends Enum
{
    public const DRAFT = 1;
    public const OPEN = 2;
    public const ONGOING = 3;
    public const COMPLETED = 4;
    public const CANCELLED = 5;

    public static function getLabel($value): string
    {
        switch ((int) $value) {
            case self::DRAFT:
                return 'Bản nháp';
            case self::OPEN:
                return 'Mở đăng ký';
            case self::ONGOING:
                return 'Đang diễn ra';
            case self::COMPLETED:
                return 'Đã kết thúc';
            case self::CANCELLED:
                return 'Đã hủy';
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
