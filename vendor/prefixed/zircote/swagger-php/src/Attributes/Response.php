<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\ApiReference\OpenApi\Attributes;

use Matomo\Dependencies\ApiReference\OpenApi\Annotations as OA;
use Matomo\Dependencies\ApiReference\OpenApi\Generator;
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Response extends OA\Response
{
    /**
     * @param string|class-string|object|null                                                                $ref
     * @param Header[]                                                                                       $headers
     * @param MediaType|JsonContent|XmlContent|Attachable|array<MediaType|JsonContent|XmlContent|Attachable> $content
     * @param Link[]                                                                                         $links
     * @param array<string,mixed>|null                                                                       $x
     * @param Attachable[]|null                                                                              $attachables
     * @param int|string|null $response
     */
    public function __construct(
        $ref = null,
        $response = null,
        ?string $description = null,
        ?array $headers = null,
        $content = null,
        ?array $links = null,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    )
    {
        parent::__construct(['ref' => $ref ?? Generator::UNDEFINED, 'response' => $response ?? Generator::UNDEFINED, 'description' => $description ?? Generator::UNDEFINED, 'x' => $x ?? Generator::UNDEFINED, 'attachables' => $attachables ?? Generator::UNDEFINED, 'value' => $this->combine($headers, $content, $links)]);
    }
}
