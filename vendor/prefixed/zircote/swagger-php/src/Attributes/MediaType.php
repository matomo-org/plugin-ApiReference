<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\OpenApiDocs\OpenApi\Attributes;

use Matomo\Dependencies\OpenApiDocs\OpenApi\Generator;
use Matomo\Dependencies\OpenApiDocs\OpenApi\Annotations as OA;
#[\Attribute(\Attribute::TARGET_CLASS)]
class MediaType extends OA\MediaType
{
    /**
     * @param array<Examples>          $examples
     * @param array<string,mixed>      $encoding
     * @param array<string,mixed>|null $x
     * @param Attachable[]|null        $attachables
     * @param mixed $example
     */
    public function __construct(
        ?string $mediaType = null,
        ?Schema $schema = null,
        $example = Generator::UNDEFINED,
        ?array $examples = null,
        ?array $encoding = null,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    )
    {
        parent::__construct(['mediaType' => $mediaType ?? Generator::UNDEFINED, 'example' => $example, 'encoding' => $encoding ?? Generator::UNDEFINED, 'x' => $x ?? Generator::UNDEFINED, 'attachables' => $attachables ?? Generator::UNDEFINED, 'value' => $this->combine($schema, $examples)]);
    }
}
