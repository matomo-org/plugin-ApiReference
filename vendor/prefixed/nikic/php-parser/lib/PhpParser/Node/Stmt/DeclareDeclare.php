<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PhpParser\Node\Stmt;

use Matomo\Dependencies\ApiReference\PhpParser\Node\DeclareItem;
require __DIR__ . '/../DeclareItem.php';
if (\false) {
    /**
     * For classmap-authoritative support.
     *
     * @deprecated use \PhpParser\Node\DeclareItem instead.
     */
    class DeclareDeclare extends DeclareItem
    {
    }
}
