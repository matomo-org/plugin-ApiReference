<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\DocBlock\Tags\Factory;

use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\DocBlock\Tag;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\DocBlock\Tags\Method;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\DocBlock\Tags\MethodParameter;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\Type;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\TypeResolver;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\Types\Context;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\Types\Mixed_;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\Types\Void_;
use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Matomo\Dependencies\ApiReference\Webmozart\Assert\Assert;
use function array_map;
use function trim;
/**
 * @internal This class is not part of the BC promise of this library.
 */
final class MethodFactory implements PHPStanFactory
{
    /**
     * @var \Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\DocBlock\DescriptionFactory
     */
    private $descriptionFactory;
    /**
     * @var \Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\TypeResolver
     */
    private $typeResolver;
    public function __construct(TypeResolver $typeResolver, DescriptionFactory $descriptionFactory)
    {
        $this->descriptionFactory = $descriptionFactory;
        $this->typeResolver = $typeResolver;
    }
    public function create(PhpDocTagNode $node, Context $context) : Tag
    {
        $tagValue = $node->value;
        Assert::isInstanceOf($tagValue, MethodTagValueNode::class);
        return new Method($tagValue->methodName, [], $this->createReturnType($tagValue, $context), $tagValue->isStatic, $this->descriptionFactory->create($tagValue->description, $context), \false, array_map(function (MethodTagValueParameterNode $param) use($context) {
            return new MethodParameter(trim($param->parameterName, '$'), $param->type === null ? new Mixed_() : $this->typeResolver->createType($param->type, $context), $param->isReference, $param->isVariadic, $param->defaultValue === null ? MethodParameter::NO_DEFAULT_VALUE : (string) $param->defaultValue);
        }, $tagValue->parameters));
    }
    public function supports(PhpDocTagNode $node, Context $context) : bool
    {
        return $node->value instanceof MethodTagValueNode;
    }
    private function createReturnType(MethodTagValueNode $tagValue, Context $context) : Type
    {
        if ($tagValue->returnType === null) {
            return new Void_();
        }
        return $this->typeResolver->createType($tagValue->returnType, $context);
    }
}
