<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type;

use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ConditionalTypeNode implements TypeNode
{
    use NodeAttributes;
    /**
     * @var \Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $subjectType;
    /**
     * @var \Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $targetType;
    /**
     * @var \Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $if;
    /**
     * @var \Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $else;
    /**
     * @var bool
     */
    public $negated;
    public function __construct(TypeNode $subjectType, TypeNode $targetType, TypeNode $if, TypeNode $else, bool $negated)
    {
        $this->subjectType = $subjectType;
        $this->targetType = $targetType;
        $this->if = $if;
        $this->else = $else;
        $this->negated = $negated;
    }
    public function __toString() : string
    {
        return sprintf('(%s %s %s ? %s : %s)', $this->subjectType, $this->negated ? 'is not' : 'is', $this->targetType, $this->if, $this->else);
    }
}
