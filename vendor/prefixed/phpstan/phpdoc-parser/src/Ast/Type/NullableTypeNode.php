<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type;

use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\NodeAttributes;
class NullableTypeNode implements TypeNode
{
    use NodeAttributes;
    /**
     * @var \Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $type;
    public function __construct(TypeNode $type)
    {
        $this->type = $type;
    }
    public function __toString() : string
    {
        return '?' . $this->type;
    }
}
