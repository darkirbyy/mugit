<?php

declare(strict_types=1);

namespace App\DTO;

class RepoInfo
{
    const UNITS = ['Ko', 'Mo', 'Go', 'To'];

    public function __construct(private string $name, private int $size) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getCloneUrl(): string
    {
        //todo : add default_uri, how ?
        return ':' . $this->name . 'git';
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getSizeHumanReadable(): string
    {
        $size = $this->size;
        $unitIndex = 0;
        while ($size >= 1024 && $unitIndex < count(self::UNITS) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size,2) . self::UNITS[$unitIndex];
    }
}
