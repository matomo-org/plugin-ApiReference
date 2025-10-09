<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type;

use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\NodeAttributes;
use Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use function implode;
class CallableTypeNode implements TypeNode
{
    use NodeAttributes;
    /**
     * @var \Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode
     */
    public $identifier;
    /** @var TemplateTagValueNode[] */
    public $templateTypes;
    /** @var CallableTypeParameterNode[] */
    public $parameters;
    /**
     * @var \Matomo\Dependencies\OpenApiDocs\PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $returnType;
    /**
     * @param CallableTypeParameterNode[] $parameters
     * @param TemplateTagValueNode[]  $templateTypes
     */
    public function __construct(IdentifierTypeNode $identifier, array $parameters, TypeNode $returnType, array $templateTypes)
    {
        $this->identifier = $identifier;
        $this->parameters = $parameters;
        $this->returnType = $returnType;
        $this->templateTypes = $templateTypes;
    }
    public function __toString() : string
    {
        $returnType = $this->returnType;
        if ($returnType instanceof self) {
            $returnType = "({$returnType})";
        }
        $template = $this->templateTypes !== [] ? '<' . implode(', ', $this->templateTypes) . '>' : '';
        $parameters = implode(', ', $this->parameters);
        return "{$this->identifier}{$template}({$parameters}): {$returnType}";
    }
}
