<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\phpDocumentor\Reflection\DocBlock\Tags\Factory;

use Matomo\Dependencies\OpenApiDocs\phpDocumentor\Reflection\DocBlock\Tag;
use Matomo\Dependencies\OpenApiDocs\phpDocumentor\Reflection\Types\Context;
use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
interface PHPStanFactory
{
    public function create(PhpDocTagNode $node, Context $context) : Tag;
    public function supports(PhpDocTagNode $node, Context $context) : bool;
}
