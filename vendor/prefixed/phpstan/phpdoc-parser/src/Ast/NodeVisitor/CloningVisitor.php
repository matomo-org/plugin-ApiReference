<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\NodeVisitor;

use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\AbstractNodeVisitor;
use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Attribute;
use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\Node;
final class CloningVisitor extends AbstractNodeVisitor
{
    public function enterNode(Node $originalNode) : Node
    {
        $node = clone $originalNode;
        $node->setAttribute(Attribute::ORIGINAL_NODE, $originalNode);
        return $node;
    }
}
