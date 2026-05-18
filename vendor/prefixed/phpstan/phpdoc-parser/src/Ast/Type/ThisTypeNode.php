<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type;

use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\NodeAttributes;
class ThisTypeNode implements TypeNode
{
    use NodeAttributes;
    public function __toString() : string
    {
        return '$this';
    }
}
