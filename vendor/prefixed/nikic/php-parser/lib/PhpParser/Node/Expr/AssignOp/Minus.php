<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\PhpParser\Node\Expr\AssignOp;

use Matomo\Dependencies\OpenApiDocs\PhpParser\Node\Expr\AssignOp;
class Minus extends AssignOp
{
    public function getType() : string
    {
        return 'Expr_AssignOp_Minus';
    }
}
