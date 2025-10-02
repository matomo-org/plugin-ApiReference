<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\OpenApiDocs\OpenApi\Processors;

use Matomo\Dependencies\OpenApiDocs\OpenApi\Analysis;
use Matomo\Dependencies\OpenApiDocs\OpenApi\Annotations as OA;
class CleanUnmerged
{
    public function __invoke(Analysis $analysis) : void
    {
        $split = $analysis->split();
        $merged = $split->merged->annotations;
        $unmerged = $split->unmerged->annotations;
        /** @var OA\AbstractAnnotation $annotation */
        foreach ($analysis->annotations as $annotation) {
            if (property_exists($annotation, '_unmerged')) {
                foreach ($annotation->_unmerged as $ii => $item) {
                    if ($merged->contains($item)) {
                        unset($annotation->_unmerged[$ii]);
                        // Property was merged
                    }
                }
            }
        }
        $analysis->openapi->_unmerged = [];
        foreach ($unmerged as $annotation) {
            $analysis->openapi->_unmerged[] = $annotation;
        }
    }
}
