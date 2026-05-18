<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type;

use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\NodeAttributes;
class OffsetAccessTypeNode implements TypeNode
{
    use NodeAttributes;
    /**
     * @var \Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $type;
    /**
     * @var \Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $offset;
    public function __construct(TypeNode $type, TypeNode $offset)
    {
        $this->type = $type;
        $this->offset = $offset;
    }
    public function __toString() : string
    {
        if ($this->type instanceof CallableTypeNode || $this->type instanceof NullableTypeNode) {
            return '(' . $this->type . ')[' . $this->offset . ']';
        }
        return $this->type . '[' . $this->offset . ']';
    }
}
