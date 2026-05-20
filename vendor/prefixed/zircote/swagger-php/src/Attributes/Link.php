<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\ApiReference\OpenApi\Attributes;

use Matomo\Dependencies\ApiReference\OpenApi\Generator;
use Matomo\Dependencies\ApiReference\OpenApi\Annotations as OA;
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Link extends OA\Link
{
    /**
     * @param string|class-string|object|null $ref
     * @param array<string,mixed>             $parameters
     * @param array<string,mixed>|null        $x
     * @param Attachable[]|null               $attachables
     * @param mixed $requestBody
     */
    public function __construct(
        ?string $link = null,
        ?string $operationRef = null,
        $ref = null,
        ?string $operationId = null,
        ?array $parameters = null,
        $requestBody = null,
        ?string $description = null,
        ?Server $server = null,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    )
    {
        parent::__construct(['link' => $link ?? Generator::UNDEFINED, 'operationRef' => $operationRef ?? Generator::UNDEFINED, 'ref' => $ref ?? Generator::UNDEFINED, 'operationId' => $operationId ?? Generator::UNDEFINED, 'parameters' => $parameters ?? Generator::UNDEFINED, 'requestBody' => $requestBody ?? Generator::UNDEFINED, 'description' => $description ?? Generator::UNDEFINED, 'x' => $x ?? Generator::UNDEFINED, 'attachables' => $attachables ?? Generator::UNDEFINED, 'value' => $this->combine($server)]);
    }
}
