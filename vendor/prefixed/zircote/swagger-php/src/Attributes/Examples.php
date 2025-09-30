<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\OpenApiDocs\OpenApi\Attributes;

use Matomo\Dependencies\OpenApiDocs\OpenApi\Generator;
use Matomo\Dependencies\OpenApiDocs\OpenApi\Annotations as OA;
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Examples extends OA\Examples
{
    /**
     * @param string|class-string|object|null $ref
     * @param array<string,mixed>|null        $x
     * @param Attachable[]|null               $attachables
     * @param int|string|mixed[]|null $value
     */
    public function __construct(
        ?string $example = null,
        ?string $summary = null,
        ?string $description = null,
        $value = null,
        ?string $externalValue = null,
        $ref = null,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    )
    {
        parent::__construct(['example' => $example ?? Generator::UNDEFINED, 'summary' => $summary ?? Generator::UNDEFINED, 'description' => $description ?? Generator::UNDEFINED, 'value' => $value ?? Generator::UNDEFINED, 'externalValue' => $externalValue ?? Generator::UNDEFINED, 'ref' => $ref ?? Generator::UNDEFINED, 'x' => $x ?? Generator::UNDEFINED, 'attachables' => $attachables ?? Generator::UNDEFINED]);
    }
}
