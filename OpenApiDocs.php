<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs;

/**
 * @OA\OpenApi(
 *     openapi="3.1.0",
 *     security={{"api_key": {}}},
 *     @OA\ExternalDocumentation(
 *         description="Matomo Reporting API developer page",
 *         url="https://developer.matomo.org/api-reference/reporting-api/"
 *     )
 * )
 *
 * @OA\Tag(
 *     name="matomo",
 *     description="Matomo API"
 * )
 *
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     name="token_auth",
 *     in="query",
 *     securityScheme="api_key"
 * )
 *
 * @OA\Info(
 *   version="1.0.0",
 *   title="Matomo Reporting API",
 * )
 *
 * @OA\Server(
 *   url=LOCAL_MATOMO_SERVER_URL,
 *   description="Current Matomo instance"
 * )
 *
 * @OA\Server(
 *    url="https://demo.matomo.cloud/",
 *    description="Matomo demo server"
 *  )
 *
 * @OA\Parameter(
 *     parameter="module",
 *     name="module",
 *     in="query",
 *     description="Module to route requests through",
 *     required=true,
 *     @OA\Schema(
 *         type="string",
 *         enum={"API"},
 *         example="API",
 *     )
 * )
 * @OA\Parameter(
 *     parameter="format",
 *     name="format",
 *     in="query",
 *     description="Format to be used for the response",
 *     required=false,
 *     @OA\Schema(
 *         type="string",
 *         enum={"json", "xml", "csv", "tsv", "html", "rss", "original"},
 *         example="json"
 *     )
 * )
 */
class OpenApiDocs extends \Piwik\Plugin
{
    public function registerEvents(): array
    {
        return [];
    }
}
