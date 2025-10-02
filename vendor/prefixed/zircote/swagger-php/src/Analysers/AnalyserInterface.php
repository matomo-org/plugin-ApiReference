<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\OpenApiDocs\OpenApi\Analysers;

use Matomo\Dependencies\OpenApiDocs\OpenApi\Analysis;
use Matomo\Dependencies\OpenApiDocs\OpenApi\Context;
use Matomo\Dependencies\OpenApiDocs\OpenApi\GeneratorAwareInterface;
interface AnalyserInterface extends GeneratorAwareInterface
{
    public function fromFile(string $filename, Context $context) : Analysis;
}
