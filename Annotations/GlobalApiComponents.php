<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs\Annotations;

/**
 * Global components for generating OpenAPI specs for the Matomo Reporting API.
 */

/**
 * @OA\OpenApi(
 *     openapi="3.1.0",
 *     security={{"MatomoToken": {}}},
 *     @OA\ExternalDocumentation(
 *         description="Matomo Reporting API developer page",
 *         url="https://developer.matomo.org/api-reference/reporting-api/"
 *     )
 * )
 *
 * @OA\Info(
 *     title="Matomo Reporting API",
 *     version="1.0.0"
 * )
 *
 * @OA\Tag(
 *     name="matomo",
 *     description="Matomo API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="MatomoToken",
 *     type="apiKey",
 *     in="query",
 *     name="token_auth",
 *     description="Matomo API token passed as the 'token_auth' query parameter."
 * )
 *
 * @OA\Server(
 *     url=LOCAL_MATOMO_SERVER_URL,
 *     description="Current Matomo instance"
 * )
 *
 * @OA\Server(
 *     url="https://demo.matomo.cloud/",
 *     description="Matomo demo server"
 * )
 *
 * Generic Error object
 * @OA\Schema(
 *     schema="GenericSuccess",
 *     type="object",
 *     description="Generic Matomo success payload.",
 *     required={"result","message"},
 *     additionalProperties=true,
 *     @OA\Property(property="result", type="string", enum={"success"}, example="success"),
 *     @OA\Property(property="message", type="string", example="ok"),
 *     @OA\Property(property="code", type="integer", example="200")
 * )
 *
 * Generic Error object
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     description="Generic Matomo error payload.",
 *     required={"result","message"},
 *     additionalProperties=true,
 *     @OA\Property(property="result", type="string", enum={"error"}, example="error"),
 *     @OA\Property(property="message", type="string", example="There was an error"),
 *     @OA\Property(property="code", type="integer")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorXml",
 *     type="object",
 *     description="Generic Matomo error payload in XML.",
 *     @OA\Xml(
 *         name="result"
 *     ),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Xml(name="error"),
 *         @OA\Property(
 *             property="message",
 *             type="string",
 *             xml=@OA\Xml(attribute=true),
 *             example="There was an error"
 *         )
 *     )
 * )
 *
 * Common responses which should be used by each API endpoint
 * @OA\Response(
 *     response="BadRequest",
 *     description="Bad request (validation or missing parameters).",
 *     @OA\JsonContent(ref="#/components/schemas/Error"),
 *     @OA\XmlContent(ref="#/components/schemas/ErrorXml"),
 *     @OA\MediaType(mediaType="text/plain", @OA\Schema(type="string"), example="Error: There was an error."),
 *     @OA\MediaType(mediaType="text/html",  @OA\Schema(type="string"), example="There was an error.")
 * )
 *
 * @OA\Response(
 *     response="Unauthorized",
 *     description="Authentication failed or missing token.",
 *     @OA\JsonContent(ref="#/components/schemas/Error"),
 *     @OA\XmlContent(ref="#/components/schemas/ErrorXml"),
 *     @OA\MediaType(mediaType="text/plain", @OA\Schema(type="string"), example="Error: You must be logged in to access this functionality."),
 *     @OA\MediaType(mediaType="text/html",  @OA\Schema(type="string"), example="You must be logged in to access this functionality.")
 * )
 *
 * @OA\Response(
 *     response="Forbidden",
 *     description="Authenticated but not allowed to access the resource.",
 *     @OA\JsonContent(ref="#/components/schemas/Error"),
 *     @OA\XmlContent(ref="#/components/schemas/ErrorXml"),
 *     @OA\MediaType(mediaType="text/plain", @OA\Schema(type="string"), example="Error: Not authorised."),
 *     @OA\MediaType(mediaType="text/html",  @OA\Schema(type="string"), example="Not authorised.")
 * )
 *
 * @OA\Response(
 *     response="NotFound",
 *     description="Resource not found.",
 *     @OA\JsonContent(ref="#/components/schemas/Error"),
 *     @OA\XmlContent(ref="#/components/schemas/ErrorXml"),
 *     @OA\MediaType(mediaType="text/plain", @OA\Schema(type="string"), example="Error: The method is not available."),
 *     @OA\MediaType(mediaType="text/html",  @OA\Schema(type="string"), example="The method is not available.")
 * )
 *
 * @OA\Response(
 *     response="ServerError",
 *     description="Unexpected server error.",
 *     @OA\JsonContent(ref="#/components/schemas/Error"),
 *     @OA\XmlContent(ref="#/components/schemas/ErrorXml"),
 *     @OA\MediaType(mediaType="text/plain", @OA\Schema(type="string"), example="Error: There was an error."),
 *     @OA\MediaType(mediaType="text/html",  @OA\Schema(type="string"), example="There was an error.")
 * )
 *
 * @OA\Response(
 *     response="DefaultError",
 *     description="Default error response (any non-2xx).",
 *     @OA\JsonContent(ref="#/components/schemas/Error"),
 *     @OA\XmlContent(ref="#/components/schemas/ErrorXml"),
 *     @OA\MediaType(mediaType="text/plain", @OA\Schema(type="string"), example="Error: There was an error."),
 *     @OA\MediaType(mediaType="text/html",  @OA\Schema(type="string"), example="There was an error.")
 * )
 *
 * Generic responses which can be used by endpoints
 * @OA\Response(
 *     response="GenericSuccessNoBody",
 *     description="Generic 200 response with no body"
 * )
 *
 * Generic responses which can be used by endpoints
 * @OA\Response(
 *     response="GenericSuccess",
 *     description="Generic 200 response",
 *     @OA\JsonContent(ref="#/components/schemas/GenericSuccess"),
 *     @OA\XmlContent(ref="#/components/schemas/GenericSuccess"),
 *     @OA\MediaType(mediaType="text/plain", @OA\Schema(type="string"), example="Result: success"),
 *     @OA\MediaType(mediaType="text/html",  @OA\Schema(type="string"), example="success")
 * )
 *
 * @OA\Response(
 *     response="GenericString",
 *     description="Generic 200 response with only a string body",
 *     @OA\JsonContent(type="string"),
 *     @OA\XmlContent(type="string"),
 *     @OA\MediaType(mediaType="text/plain", @OA\Schema(type="string"), example="Result: success"),
 *     @OA\MediaType(mediaType="text/html",  @OA\Schema(type="string"), example="success")
 * )
 *
 * @OA\Response(
 *     response="GenericBoolean",
 *     description="Generic 200 response with only true or false as the body",
 *     @OA\JsonContent(type="boolean"),
 *     @OA\XmlContent(type="boolean"),
 *     @OA\MediaType(mediaType="text/plain", @OA\Schema(type="boolean")),
 *     @OA\MediaType(mediaType="text/html",  @OA\Schema(type="boolean"))
 * )
 *
 * @OA\Response(
 *     response="GenericInteger",
 *     description="Generic 200 response with only an integer as the body",
 *     @OA\JsonContent(type="integer"),
 *     @OA\XmlContent(type="integer"),
 *     @OA\MediaType(mediaType="text/plain", @OA\Schema(type="integer")),
 *     @OA\MediaType(mediaType="text/html",  @OA\Schema(type="integer"))
 * )
 *
 * @OA\Response(
 *      response="GenericArray",
 *      description="Generic 200 response with array body",
 *      @OA\JsonContent(type="array", @OA\Items({})),
 *      @OA\XmlContent(type="array", @OA\Items({})),
 *      @OA\MediaType(mediaType="text/plain", @OA\Schema(type="string")),
 *      @OA\MediaType(mediaType="text/html",  @OA\Schema(type="string"))
 *  )
 *
 * Global parameters which apply to pretty much every API endpoint. If there are parameters not required by every
 * endpoint, it will be declared as both required and optional so that each endpoint can specify the correct one.
 * @OA\Parameter(
 *     parameter="moduleRequired",
 *     name="module",
 *     in="query",
 *     description="Always `API` for Reporting API requests.",
 *     required=true,
 *     @OA\Schema(type="string", default="API")
 * )
 *
 * @OA\Parameter(
 *     parameter="methodRequired",
 *     name="method",
 *     in="query",
 *     description="API method, e.g. `VisitsSummary.get` or `CustomAlerts.getAlert`.",
 *     required=true,
 *     @OA\Schema(type="string", example="CustomAlerts.getAlert")
 * )
 *
 * @OA\Parameter(
 *      parameter="formatRequired",
 *      name="format",
 *      in="query",
 *      description="Response format such as `xml` or `json`. Use `original` to get the original PHP data structure.",
 *      required=true,
 *      @OA\Schema(
 *          type="string",
 *          enum={"xml","json","csv","tsv","html","rss","original"},
 *          default="xml"
 *      )
 *  )
 *
 * @OA\Parameter(
 *     parameter="formatOptional",
 *     name="format",
 *     in="query",
 *     description="Response format. Defaults to `xml`. Use `original` to get the original PHP data structure.",
 *     required=false,
 *     @OA\Schema(
 *         type="string",
 *         enum={"xml","json","csv","tsv","html","rss","original"},
 *         default="xml"
 *     )
 * )
 *
 * Commonly used parameters. If there are parameters not required by every endpoint, it will be declared as both
 * required and optional so that each endpoint can specify the correct one.
 * @OA\Parameter(
 *     parameter="idSiteRequired",
 *     name="idSite",
 *     in="query",
 *     description="Matomo site ID.",
 *     required=true,
 *     @OA\Schema(type="integer", example=1)
 * )
 *
 * @OA\Parameter(
 *     parameter="idSiteOptional",
 *     name="idSite",
 *     in="query",
 *     description="Matomo site ID.",
 *     required=false,
 *     @OA\Schema(type="integer", example=1)
 * )
 *
 * @OA\Parameter(
 *     parameter="periodRequired",
 *     name="period",
 *     in="query",
 *     description="Reporting period.",
 *     required=true,
 *     @OA\Schema(type="string", enum={"day","week","month","year","range"}, example="day")
 * )
 *
 * @OA\Parameter(
 *     parameter="periodOptional",
 *     name="period",
 *     in="query",
 *     description="Reporting period.",
 *     required=false,
 *     @OA\Schema(type="string", enum={"day","week","month","year","range"}, example="day")
 * )
 *
 * @OA\Parameter(
 *     parameter="dateRequired",
 *     name="date",
 *     in="query",
 *     description="Date or range (e.g. `2025-08-01`, `yesterday`, `last30`, or `2025-08-01,2025-08-11`).",
 *     required=true,
 *     @OA\Schema(type="string", example="today")
 * )
 *
 * @OA\Parameter(
 *     parameter="dateOptional",
 *     name="date",
 *     in="query",
 *     description="Date or range (e.g. `2025-08-01`, `yesterday`, `last30`, or `2025-08-01,2025-08-11`).",
 *     required=false,
 *     @OA\Schema(type="string", example="today")
 * )
 *
 * @OA\Parameter(
 *     parameter="segmentRequired",
 *     name="segment",
 *     in="query",
 *     description="Segment expression; see `API.getSegmentDimensionMetadata`.",
 *     required=true,
 *     @OA\Schema(type="string")
 * )
 *
 * @OA\Parameter(
 *     parameter="segmentOptional",
 *     name="segment",
 *     in="query",
 *     description="Segment expression; see `API.getSegmentDimensionMetadata`.",
 *     required=false,
 *     @OA\Schema(type="string")
 * )
 *
 * Parameters specific to DataTables and Views
 * @OA\Parameter(parameter="expandedOptional", name="expanded", in="query",
 *     description="If true, loads all subtables.", required=false,
 *     @OA\Schema(type="integer", enum={0,1}, example=0, default=0))
 *
 * @OA\Parameter(parameter="idSubtableOptional", name="idSubtable", in="query",
 *     description="An in-database subtable ID.", required=false,
 *     @OA\Schema(type="integer"))
 *
 * Parameters specific to DataTables and Views
 * @OA\Parameter(parameter="flatOptional", name="flat", in="query",
 *     description="Flatten subtables into the parent table.", required=false,
 *     @OA\Schema(type="integer", enum={0,1}, example=0))
 *
 * @OA\Parameter(parameter="filter_patternOptional", name="filter_pattern", in="query",
 *     description="Regex to keep matching rows.", required=false, @OA\Schema(type="string"))
 *
 * @OA\Parameter(parameter="filter_columnOptional", name="filter_column", in="query",
 *     description="Column to apply the regex to (e.g., `label`).", required=false,
 *     @OA\Schema(type="string", example="label"))
 *
 * @OA\Parameter(parameter="filter_pattern_recursiveOptional", name="filter_pattern_recursive", in="query",
 *     description="Recursive regex filter.", required=false, @OA\Schema(type="string"))
 *
 * @OA\Parameter(parameter="filter_column_recursiveOptional", name="filter_column_recursive", in="query",
 *     description="Column for the recursive regex filter.", required=false, @OA\Schema(type="string"))
 *
 * @OA\Parameter(parameter="filter_excludelowpopOptional", name="filter_excludelowpop", in="query",
 *     description="Column to threshold and exclude low values.", required=false, @OA\Schema(type="string"))
 *
 * @OA\Parameter(parameter="filter_excludelowpop_valueOptional", name="filter_excludelowpop_value", in="query",
 *     description="Minimum value threshold for `filter_excludelowpop`.", required=false,
 *     @OA\Schema(type="number", example=0))
 *
 * @OA\Parameter(parameter="filter_sort_columnOptional", name="filter_sort_column", in="query",
 *     description="Column to sort by.", required=false, @OA\Schema(type="string"))
 *
 * @OA\Parameter(parameter="filter_sort_orderOptional", name="filter_sort_order", in="query",
 *     description="Sort direction.", required=false,
 *     @OA\Schema(type="string", enum={"asc","desc"}, example="desc"))
 *
 * @OA\Parameter(parameter="filter_truncateOptional", name="filter_truncate", in="query",
 *     description="Row index after which rows are removed.", required=false, @OA\Schema(type="integer"))
 *
 * @OA\Parameter(parameter="filter_limitOptional", name="filter_limit", in="query",
 *     description="Maximum number of rows to return.", required=false, @OA\Schema(type="integer"))
 *
 * @OA\Parameter(parameter="filter_offsetOptional", name="filter_offset", in="query",
 *     description="Row offset.", required=false, @OA\Schema(type="integer"))
 *
 * @OA\Parameter(parameter="keep_summary_rowOptional", name="keep_summary_row", in="query",
 *     description="Keep the summary row.", required=false,
 *     @OA\Schema(type="integer", enum={0,1}, example=1))
 *
 * @OA\Parameter(parameter="disable_generic_filtersOptional", name="disable_generic_filters", in="query",
 *     description="Disable generic filters (those above).", required=false,
 *     @OA\Schema(type="integer", enum={0,1}, example=0))
 *
 * @OA\Parameter(parameter="disable_queued_filtersOptional", name="disable_queued_filters", in="query",
 *     description="Skip queued filters.", required=false,
 *     @OA\Schema(type="integer", enum={0,1}, example=0))
 *
 * @OA\Parameter(parameter="hideColumnsOptional", name="hideColumns", in="query",
 *     description="Comma-separated list of columns to hide.", required=false, @OA\Schema(type="string"))
 *
 * @OA\Parameter(parameter="showColumnsOptional", name="showColumns", in="query",
 *     description="Comma-separated list of columns to include.", required=false, @OA\Schema(type="string"))
 *
 * @OA\Parameter(parameter="labelOptional", name="label", in="query",
 *     description="Keep only rows with these label(s). Supports path via '>' and arrays.", required=false, @OA\Schema(type="string"))
 *
 * @OA\Parameter(parameter="idGoalRequired", name="idGoal", in="query",
 *     description="The ID of a configured goal.", required=true, @OA\Schema(type="integer"))
 *
 * @OA\Parameter(parameter="idGoalOptional", name="idGoal", in="query",
 *     description="The ID of a configured goal.", required=false, @OA\Schema(type="integer"))
 */
class GlobalApiComponents
{
}
