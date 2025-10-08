<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type;

use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Node;
use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ArrayShapeUnsealedTypeNode implements Node
{
    use NodeAttributes;
    /**
     * @var \Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $valueType;
    /**
     * @var \Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type\TypeNode|null
     */
    public $keyType;
    public function __construct(TypeNode $valueType, ?TypeNode $keyType)
    {
        $this->valueType = $valueType;
        $this->keyType = $keyType;
    }
    public function __toString() : string
    {
        if ($this->keyType !== null) {
            return sprintf('<%s, %s>', $this->keyType, $this->valueType);
        }
        return sprintf('<%s>', $this->valueType);
    }
}
