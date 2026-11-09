<?php

namespace App\Enums;

/** Where a post goes up. See GLOSSARY.md. */
enum Platform: string
{
    case TikTok = 'tiktok';
    case Instagram = 'instagram';
    case YouTube = 'youtube';

    public function label(): string
    {
        return match ($this) {
            self::TikTok => 'TikTok',
            self::Instagram => 'Instagram',
            self::YouTube => 'YouTube',
        };
    }
}
