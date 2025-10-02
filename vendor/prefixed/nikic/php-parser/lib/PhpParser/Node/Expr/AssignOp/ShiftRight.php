<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\PhpParser\Node\Expr\AssignOp;

use Matomo\Dependencies\OpenApiDocs\PhpParser\Node\Expr\AssignOp;
class ShiftRight extends AssignOp
{
    public function getType() : string
    {
        return 'Expr_AssignOp_ShiftRight';
    }
}
