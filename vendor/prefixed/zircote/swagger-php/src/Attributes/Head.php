<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\ApiReference\OpenApi\Attributes;

use Matomo\Dependencies\ApiReference\OpenApi\Annotations as OA;
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Head extends OA\Head
{
    use OperationTrait;
}
