<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PhpParser\Node\Scalar;

use Matomo\Dependencies\ApiReference\PhpParser\Node\InterpolatedStringPart;
require __DIR__ . '/../InterpolatedStringPart.php';
if (\false) {
    /**
     * For classmap-authoritative support.
     *
     * @deprecated use \PhpParser\Node\InterpolatedStringPart instead.
     */
    class EncapsedStringPart extends InterpolatedStringPart
    {
    }
}
