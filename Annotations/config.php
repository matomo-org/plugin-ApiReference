<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

return [
    'virtualPathTemplate' => '/index.php?module=API&method={plugin}.{method}',

    'defaultParamRefs' => [
        '#/components/parameters/formatOptional',
    ],

    'defaultErrorResponseRefs' => [
        [ 'code' => 400, 'ref' => '#/components/responses/BadRequest' ],
        [ 'code' => 401, 'ref' => '#/components/responses/Unauthorized' ],
        [ 'code' => 403, 'ref' => '#/components/responses/Forbidden' ],
        [ 'code' => 404, 'ref' => '#/components/responses/NotFound' ],
        [ 'code' => 500, 'ref' => '#/components/responses/ServerError' ],
        [ 'code' => 'default', 'ref' => '#/components/responses/DefaultError' ],
    ],
];
