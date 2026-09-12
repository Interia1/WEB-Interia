<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case ContentManager = 'content_manager';
    case Administrator = 'administrator';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Zákazník',
            self::ContentManager => 'Pracovník obsahu',
            self::Administrator => 'Administrátor',
        };
    }

    public function isInternal(): bool
    {
        return $this !== self::Customer;
    }
}
