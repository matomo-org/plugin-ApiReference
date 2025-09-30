<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\OpenApiDocs\OpenApi;

trait GeneratorAwareTrait
{
    /**
     * @var \Matomo\Dependencies\OpenApiDocs\OpenApi\Generator|null
     */
    protected $generator;
    public function setGenerator(Generator $generator)
    {
        $this->generator = $generator;
        return $this;
    }
}
