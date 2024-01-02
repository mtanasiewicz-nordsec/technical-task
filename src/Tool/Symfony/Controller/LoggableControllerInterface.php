<?php

declare(strict_types=1);

namespace App\Tool\Symfony\Controller;

/**
 * Marking interface
 */
interface LoggableControllerInterface extends LoggableRequestControllerInterface, LoggableResponseControllerInterface
{
}
