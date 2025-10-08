<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\ConstExpr;

use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\NodeAttributes;
class ConstExprTrueNode implements ConstExprNode
{
    use NodeAttributes;
    public function __toString() : string
    {
        return 'true';
    }
}
