<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\DocBlock\Tags\Factory;

use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\DocBlock\Tag;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\DocBlock\Tags\TemplateExtends;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\TypeResolver;
use Matomo\Dependencies\ApiReference\phpDocumentor\Reflection\Types\Context;
use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\PhpDoc\ExtendsTagValueNode;
use Matomo\Dependencies\ApiReference\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Matomo\Dependencies\ApiReference\Webmozart\Assert\Assert;
use function is_string;
/**
 * @internal This class is not part of the BC promise of this library.
 */
final class TemplateExtendsFactory implements PHPStanFactory
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
    public function supports(PhpDocTagNode $node, Context $context) : bool
    {
        return $node->value instanceof ExtendsTagValueNode && $node->name === '@template-extends';
    }
    public function create(PhpDocTagNode $node, Context $context) : Tag
    {
        $tagValue = $node->value;
        Assert::isInstanceOf($tagValue, ExtendsTagValueNode::class);
        $description = $tagValue->getAttribute('description');
        if (is_string($description) === \false) {
            $description = $tagValue->description;
        }
        return new TemplateExtends($this->typeResolver->createType($tagValue->type, $context), $this->descriptionFactory->create($description, $context));
    }
}
