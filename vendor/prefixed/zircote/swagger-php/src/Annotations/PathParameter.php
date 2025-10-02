<?php

declare (strict_types=1);
/**
 * @license Apache 2.0
 */
namespace Matomo\Dependencies\OpenApiDocs\OpenApi\Annotations;

use Matomo\Dependencies\OpenApiDocs\OpenApi\Annotations as OA;
/**
 * A <code>@OA\Request</code> path parameter.
 *
 * @Annotation
 */
class PathParameter extends Parameter
{
    /**
     * @inheritdoc
     * This takes 'path' as the default location.
     */
    public $in = 'path';
    /**
     * @inheritdoc
     */
    public $required = \true;
    /**
     * @inheritdoc
     */
    public static $_required = ['name'];
}
