<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\phpDocumentor\Reflection\PseudoTypes;

use Matomo\Dependencies\OpenApiDocs\phpDocumentor\Reflection\PseudoType;
use Matomo\Dependencies\OpenApiDocs\phpDocumentor\Reflection\Type;
use Matomo\Dependencies\OpenApiDocs\phpDocumentor\Reflection\Types\Object_;
use function implode;
/** @psalm-immutable */
final class ObjectShape implements PseudoType
{
    /** @var ObjectShapeItem[] */
    private $items;
    public function __construct(ObjectShapeItem ...$items)
    {
        $this->items = $items;
    }
    /**
     * @return ObjectShapeItem[]
     */
    public function getItems() : array
    {
        return $this->items;
    }
    public function underlyingType() : Type
    {
        return new Object_();
    }
    public function __toString() : string
    {
        return 'object{' . implode(', ', $this->items) . '}';
    }
}
