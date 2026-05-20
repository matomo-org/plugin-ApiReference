<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PhpParser\ErrorHandler;

use Matomo\Dependencies\ApiReference\PhpParser\Error;
use Matomo\Dependencies\ApiReference\PhpParser\ErrorHandler;
/**
 * Error handler that handles all errors by throwing them.
 *
 * This is the default strategy used by all components.
 */
class Throwing implements ErrorHandler
{
    public function handleError(Error $error) : void
    {
        throw $error;
    }
}
