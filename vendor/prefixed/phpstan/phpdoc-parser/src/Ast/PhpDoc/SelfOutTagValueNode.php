<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\PhpDoc;

use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\NodeAttributes;
use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class SelfOutTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;
    /**
     * @var \Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $type;
    /** @var string (may be empty) */
    public $description;
    public function __construct(TypeNode $type, string $description)
    {
        $this->type = $type;
        $this->description = $description;
    }
    public function __toString() : string
    {
        return trim($this->type . ' ' . $this->description);
    }
}
