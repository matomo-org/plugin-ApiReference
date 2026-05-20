<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PhpParser\Node\Expr\AssignOp;

use Matomo\Dependencies\ApiReference\PhpParser\Node\Expr\AssignOp;
class BitwiseAnd extends AssignOp
{
    public function getType() : string
    {
        return 'Expr_AssignOp_BitwiseAnd';
    }
}
