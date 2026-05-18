<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\ApiReference\OpenApi\Analysers;

use Matomo\Dependencies\ApiReference\OpenApi\Annotations as OA;
use Matomo\Dependencies\ApiReference\OpenApi\Context;
use Matomo\Dependencies\ApiReference\OpenApi\GeneratorAwareInterface;
interface AnnotationFactoryInterface extends GeneratorAwareInterface
{
    /**
     * Checks if this factory is supported by the current runtime.
     */
    public function isSupported() : bool;
    /**
     * @return array<OA\AbstractAnnotation> top level annotations
     */
    public function build(\Reflector $reflector, Context $context) : array;
}
