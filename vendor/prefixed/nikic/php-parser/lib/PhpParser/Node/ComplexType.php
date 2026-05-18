<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PhpParser\Node;

use Matomo\Dependencies\ApiReference\PhpParser\NodeAbstract;
/**
 * This is a base class for complex types, including nullable types and union types.
 *
 * It does not provide any shared behavior and exists only for type-checking purposes.
 */
abstract class ComplexType extends NodeAbstract
{
}
