<?php

namespace App\Enums;

/** Where an idea is on its way to being posted. See GLOSSARY.md. */
enum Stage: string
{
    case Idea = 'idea';
    case Filmed = 'filmed';
    case Edited = 'edited';
    case Posted = 'posted';
    case Reviewed = 'reviewed';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Idea => 'lightbulb',
            self::Filmed => 'camera',
            self::Edited => 'scissors',
            self::Posted => 'send',
            self::Reviewed => 'chart',
        };
    }

    /** The next stage you can move to by hand. Posting and reviewing happen through their own forms. */
    public function next(): ?self
    {
        return match ($this) {
            self::Idea => self::Filmed,
            self::Filmed => self::Edited,
            default => null,
        };
    }

    public function previous(): ?self
    {
        return match ($this) {
            self::Filmed => self::Idea,
            self::Edited => self::Filmed,
            default => null,
        };
    }
}
