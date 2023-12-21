<?php

declare(strict_types=1);

namespace App\Tests\Doubles\Tool\Http\Client;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

final class PSRClientException extends Exception implements ClientExceptionInterface
{
}
