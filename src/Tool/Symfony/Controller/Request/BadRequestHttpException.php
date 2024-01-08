<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Controller\Request;

use Exception;

/**
 * @deprecated
 */
final class BadRequestHttpException extends Exception
{
    /**
     * @param array<string, string[]> $messages
     */
    public function __construct(public array $messages)
    {
        parent::__construct();
    }
}
