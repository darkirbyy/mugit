<?php

declare(strict_types=1);

namespace App\Extension;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFilter;

/**
 * App specific twig filters.
 */
class TwigFilter
{
    public const array UNITS = ['Kio', 'Mio', 'Gio', 'Tio'];

    public function __construct(#[Autowire('%core.public_hostname%')] private string $corePublicHostname) {}

    /**
     * Tranform a repository name into a full URL ready to by copy-paste for cloning the repositoory.
     */
    #[AsTwigFilter(name: 'repo_name_to_clone_URL')]
    public function repoNameToCloneURL(string $name): string
    {
        return 'git@' . $this->corePublicHostname . ':' . $name . '.git';
    }

    /**
     * Transform a repository size in Kio into the closest readable unit.
     */
    #[AsTwigFilter(name: 'repo_size_to_human_readable')]
    public function repoSizeToHumanReadable(int $size): string
    {
        $unitIndex = 0;
        while ($size >= 1024 && $unitIndex < count(self::UNITS) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . self::UNITS[$unitIndex];
    }

    /**
     * Replnce the middle part of a full key with an ellipsis.
     */
    #[AsTwigFilter(name: 'user_keys_replace_ellipsis')]
    public function userKeysReplaceEllipsis(string $key, int $start, int $end): string
    {
        return substr($key, 0, $start) . '...' . substr($key, strlen($key) - $end, $end);
    }
}
