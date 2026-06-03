<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TournamentParticipantStatus extends Enum
{
    public const PENDING = 1;
    public const APPROVED = 2;
    public const REJECTED = 3;

    public static function getLabel($value): string
    {
        switch ((int) $value) {
            case self::PENDING:
                return 'Chờ duyệt';
            case self::APPROVED:
                return 'Đã duyệt';
            case self::REJECTED:
                return 'Từ chối';
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
