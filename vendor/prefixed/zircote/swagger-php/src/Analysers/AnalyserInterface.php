<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\ApiReference\OpenApi\Analysers;

use Matomo\Dependencies\ApiReference\OpenApi\Analysis;
use Matomo\Dependencies\ApiReference\OpenApi\Context;
use Matomo\Dependencies\ApiReference\OpenApi\GeneratorAwareInterface;
interface AnalyserInterface extends GeneratorAwareInterface
{
    public function fromFile(string $filename, Context $context) : Analysis;
}
