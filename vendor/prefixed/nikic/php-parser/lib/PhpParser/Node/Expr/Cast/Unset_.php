<?php

declare (strict_types=1);
namespace Matomo\Dependencies\ApiReference\PhpParser\Node\Expr\Cast;

use Matomo\Dependencies\ApiReference\PhpParser\Node\Expr\Cast;
class Unset_ extends Cast
{
    public function getType() : string
    {
        return 'Expr_Cast_Unset';
    }
}
