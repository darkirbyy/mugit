<?php

declare(strict_types=1);

namespace App\DTO;

class RepoInfoData
{
    // todo : use env var for the HOST ?
    public const string HOST = 'localhost';
    public const array UNITS = ['Kio', 'Mio', 'Gio', 'Tio'];

    public function __construct(public string $name, public int $size) {}

    public function getCloneUrl(): string
    {
        return 'git@' . self::HOST . ':' . $this->name . '.git';
    }

    public function getHumanReadableSize(): string
    {
        $size = $this->size;
        $unitIndex = 0;
        while ($size >= 1024 && $unitIndex < count(self::UNITS) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . self::UNITS[$unitIndex];
    }
}
