<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PhpParser\Node\Expr\BinaryOp;

use Matomo\Dependencies\ApiReference\PhpParser\Node\Expr\BinaryOp;
class LogicalXor extends BinaryOp
{
    public function getOperatorSigil() : string
    {
        return 'xor';
    }
    public function getType() : string
    {
        return 'Expr_BinaryOp_LogicalXor';
    }
}
