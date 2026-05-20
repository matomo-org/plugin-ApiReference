<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\ApiReference\OpenApi;

trait GeneratorAwareTrait
{
    /**
     * @var \Matomo\Dependencies\ApiReference\OpenApi\Generator|null
     */
    protected $generator;
    public function setGenerator(Generator $generator)
    {
        $this->generator = $generator;
        return $this;
    }
}
