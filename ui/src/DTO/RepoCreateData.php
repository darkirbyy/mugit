<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RepoCreateData
{
    public function __construct(
        #[Assert\NotBlank(message: 'repo.create.empty')] #[Assert\Regex('/' . CoreInteractInterface::REGEX_NAME . '/', message: 'repo.create.invalid')] public ?string $name,
    ) {}
}
