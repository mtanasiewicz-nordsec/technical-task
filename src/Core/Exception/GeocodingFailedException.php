<?php

declare(strict_types=1);

namespace App\Core\Exception;

use Exception;
use Throwable;

final class GeocodingFailedException extends Exception
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message = '',
        public readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
