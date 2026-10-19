<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\ApiReference\OpenApi\Processors;

use Matomo\Dependencies\ApiReference\OpenApi\Analysis;
use Matomo\Dependencies\ApiReference\OpenApi\Annotations as OA;
use Matomo\Dependencies\ApiReference\OpenApi\Context;
/**
 * Use the Schema context to extract useful information and inject that into the annotation.
 *
 * Merges properties.
 */
class AugmentItems
{
    public function __invoke(Analysis $analysis) : void
    {
        $schemas = $analysis->getAnnotationsOfType(OA\Schema::class);
        foreach ($schemas as $schema) {
            if ($schema->items instanceof OA\Items) {
                $schema->type = 'array';
            }
        }
    }
}
