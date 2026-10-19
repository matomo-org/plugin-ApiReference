<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\ApiReference\OpenApi\Processors;

use Matomo\Dependencies\ApiReference\OpenApi\Analysis;
use Matomo\Dependencies\ApiReference\OpenApi\Annotations as OA;
use Matomo\Dependencies\ApiReference\OpenApi\Generator;
use Matomo\Dependencies\ApiReference\OpenApi\OpenApiException;
/**
 * Augment media type encodings.
 */
class AugmentMediaType
{
    public function __invoke(Analysis $analysis) : void
    {
        $mediaTypes = $analysis->getAnnotationsOfType(OA\MediaType::class);
        foreach ($mediaTypes as $mediaType) {
            $schema = $mediaType->schema;
            if ($schema instanceof OA\Schema) {
                if (!Generator::isDefault($schema->properties)) {
                    $this->mergePropertyEncodings($mediaType, $schema->properties);
                } elseif (!Generator::isDefault($schema->ref)) {
                    try {
                        $refSchema = $analysis->openapi->ref($schema->ref);
                    } catch (OpenApiException $openApiException) {
                        // ignore
                        $refSchema = null;
                    }
                    if ($refSchema instanceof OA\Schema && !Generator::isDefault($refSchema->properties)) {
                        $this->mergePropertyEncodings($mediaType, $refSchema->properties);
                    }
                }
            }
        }
    }
    /**
     * @param array<OA\Property> $properties
     */
    protected function mergePropertyEncodings(OA\MediaType $mediaType, array $properties) : void
    {
        foreach ($properties as $property) {
            if ($property->encoding instanceof OA\Encoding) {
                $mediaType->merge([$property->encoding], \true);
            }
        }
    }
}
