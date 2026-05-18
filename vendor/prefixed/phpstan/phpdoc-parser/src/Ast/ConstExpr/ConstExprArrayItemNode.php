<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\ConstExpr;

use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ConstExprArrayItemNode implements ConstExprNode
{
    use NodeAttributes;
    /**
     * @var \Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode|null
     */
    public $key;
    /**
     * @var \Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
     */
    public $value;
    public function __construct(?ConstExprNode $key, ConstExprNode $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
    public function __toString() : string
    {
        if ($this->key !== null) {
            return sprintf('%s => %s', $this->key, $this->value);
        }
        return (string) $this->value;
    }
}
