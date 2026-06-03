<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TournamentMatchStatus extends Enum
{
    public const UPCOMING = 1;
    public const PLAYING = 2;
    public const COMPLETED = 3;

    public static function getLabel($value): string
    {
        switch ((int) $value) {
            case self::UPCOMING:
                return 'Sắp diễn ra';
            case self::PLAYING:
                return 'Đang đấu';
            case self::COMPLETED:
                return 'Đã xong';
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
