<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type;

use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\NodeAttributes;
class ConstTypeNode implements TypeNode
{
    use NodeAttributes;
    /**
     * @var \Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
     */
    public $constExpr;
    public function __construct(ConstExprNode $constExpr)
    {
        $this->constExpr = $constExpr;
    }
    public function __toString() : string
    {
        return $this->constExpr->__toString();
    }
}
