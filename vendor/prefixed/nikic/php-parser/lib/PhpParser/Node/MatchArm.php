<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PhpParser\Node;

use Matomo\Dependencies\ApiReference\PhpParser\Node;
use Matomo\Dependencies\ApiReference\PhpParser\NodeAbstract;
class MatchArm extends NodeAbstract
{
    /** @var null|list<Node\Expr> */
    public $conds;
    /**
     * @var \Matomo\Dependencies\ApiReference\PhpParser\Node\Expr
     */
    public $body;
    /**
     * @param null|list<Node\Expr> $conds
     */
    public function __construct(?array $conds, Node\Expr $body, array $attributes = [])
    {
        $this->conds = $conds;
        $this->body = $body;
        $this->attributes = $attributes;
    }
    public function getSubNodeNames() : array
    {
        return ['conds', 'body'];
    }
    public function getType() : string
    {
        return 'MatchArm';
    }
}
