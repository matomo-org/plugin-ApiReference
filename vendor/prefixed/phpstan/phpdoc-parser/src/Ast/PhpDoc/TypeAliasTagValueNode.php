<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\PhpDoc;

use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\NodeAttributes;
use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class TypeAliasTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;
    /**
     * @var string
     */
    public $alias;
    /**
     * @var \Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $type;
    public function __construct(string $alias, TypeNode $type)
    {
        $this->alias = $alias;
        $this->type = $type;
    }
    public function __toString() : string
    {
        return trim("{$this->alias} {$this->type}");
    }
}
