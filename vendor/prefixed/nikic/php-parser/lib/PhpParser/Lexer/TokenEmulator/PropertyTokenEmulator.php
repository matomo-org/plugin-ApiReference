<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PhpParser\Lexer\TokenEmulator;

use Matomo\Dependencies\ApiReference\PhpParser\PhpVersion;
final class PropertyTokenEmulator extends KeywordEmulator
{
    public function getPhpVersion() : PhpVersion
    {
        return PhpVersion::fromComponents(8, 4);
    }
    public function getKeywordString() : string
    {
        return '__property__';
    }
    public function getKeywordToken() : int
    {
        return \Matomo\Dependencies\ApiReference\T_PROPERTY_C;
    }
}
