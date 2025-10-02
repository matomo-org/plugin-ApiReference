<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\OpenApiDocs\OpenApi\Attributes;

use Matomo\Dependencies\OpenApiDocs\OpenApi\Annotations as OA;
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Delete extends OA\Delete
{
    use OperationTrait;
}
