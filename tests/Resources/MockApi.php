<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ApiReference\tests\Resources;

use Piwik\DataTable;

/**
 * Mock API class with some mock methods and frames of methods copied from other plugins to test the annotation
 * generator.
 */
class MockApi extends \Piwik\Plugin\API
{
    /**
     * Test method marked as 'internal' so that it will be ignored by the documentation.
     *
     * @internal
     * @return void
     */
    public function internalApiMethod()
    {
    }

    /**
     * Test method marked as 'hide' so that it will be ignored by the documentation.
     *
     * @hide
     * @return void
     */
    public function hiddenApiMethod()
    {
    }

    /**
     * Test method which is private so that it will be ignored by the documentation.
     *
     * @return void
     */
    public function privateApiMethod()
    {
    }

    /**
     * Test method which is protected so that it will be ignored by the documentation.
     *
     * @return void
     */
    public function protectedApiMethod()
    {
    }

    // TODO - Try to replace the below methods with more generic ones instead of copying from existing plugins
    /**
     * Fetch a report for the given idDimension. Only reports for active dimensions can be fetched. Requires at least
     * view access.
     *
     * @param int $idDimension
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|false $segment
     * @param bool|false $expanded
     * @param bool|false $flat
     * @param int|false $idSubtable
     * @return DataTable|DataTable\Map
     * @throws \Exception
     */
    public function getCustomDimension($idDimension, $idSite, $period, $date, $segment = false, $expanded = false, $flat = false, $idSubtable = false)
    {
        return new DataTable();
    }

    /**
     * Configures a new Custom Dimension. Note that Custom Dimensions cannot be deleted, be careful when creating one
     * as you might run quickly out of available Custom Dimension slots. Requires at least Admin access for the
     * specified website. A current list of available `$scopes` can be fetched via the API method
     * `CustomDimensions.getAvailableScopes()`. This method will also contain information whether actually Custom
     * Dimension slots are available or whether they are all already in use.
     *
     * @param int $idSite    The idSite the dimension shall belong to
     * @param string $name   The name of the dimension
     * @param string $scope  Either 'visit' or 'action'. To get an up to date list of availabe scopes fetch the
     *                       API method `CustomDimensions.getAvailableScopes`
     * @param int $active  '0' if dimension should be inactive, '1' if dimension should be active
     * @param array $extractions    Either an empty array or if extractions shall be used one or multiple extractions
     *                              the format array(array('dimension' => 'url', 'pattern' => 'index_(.+).html'), array('dimension' => 'urlparam', 'pattern' => '...'))
     *                              Supported dimensions are  eg 'url', 'urlparam' and 'action_name'. To get an up to date list of
     *                              supported dimensions request the API method `CustomDimensions.getAvailableExtractionDimensions`.
     *                              Note: Extractions can be only set for dimensions in scope 'action'.
     * @param int|bool $caseSensitive  '0' if extractions should be applied case insensitive, '1' if extractions should be applied case sensitive
     * @return int Returns the ID of the configured dimension. Note that the same idDimension will be used for different websites.
     * @throws \Exception
     */
    public function configureNewCustomDimension($idSite, $name, $scope, $active, $extractions = array(), $caseSensitive = true)
    {
        return 1;
    }

    /**
     * Updates an existing Custom Dimension. This method updates all values, you need to pass existing values of the
     * dimension if you do not want to reset any value. Requires at least Admin access for the specified website.
     *
     * @param int $idDimension  The id of a Custom Dimension.
     * @param int $idSite       The idSite the dimension belongs to
     * @param string $name      The name of the dimension
     * @param int $active       '0' if dimension should be inactive, '1' if dimension should be active
     * @param array $extractions    Either an empty array or if extractions shall be used one or multiple extractions
     *                              the format array(array('dimension' => 'url', 'pattern' => 'index_(.+).html'), array('dimension' => 'urlparam', 'pattern' => '...'))
     *                              Supported dimensions are  eg 'url', 'urlparam' and 'action_name'. To get an up to date list of
     *                              supported dimensions request the API method `CustomDimensions.getAvailableExtractionDimensions`.
     *                              Note: Extractions can be only set for dimensions in scope 'action'.
     * @param int|bool|null $caseSensitive  '0' if extractions should be applied case insensitive, '1' if extractions should be applied case sensitive, null to keep case sensitive unchanged
     * @throws \Exception
     */
    public function configureExistingCustomDimension($idDimension, $idSite, $name, $active, $extractions = array(), $caseSensitive = null)
    {
    }

    /**
     * Get a list of all configured CustomDimensions for a given website. Requires at least Admin access for the
     * specified website.
     *
     * @param int $idSite
     * @return array
     */
    public function getConfiguredCustomDimensions($idSite)
    {
        return [];
    }

    /**
     * For convenience. Hidden to reduce API surface area.
     * @hide
     */
    public function getConfiguredCustomDimensionsHavingScope($idSite, $scope)
    {
    }

    /**
     * Get a list of all supported scopes that can be used in the API method
     * `CustomDimensions.configureNewCustomDimension`. The response also contains information whether more Custom
     * Dimensions can be created or not. Requires at least Admin access for the specified website.
     *
     * @param int $idSite
     * @return array
     */
    public function getAvailableScopes($idSite)
    {
        return [];
    }

    /**
     * Get a list of all available dimensions that can be used in an extraction. Requires at least Admin access
     * to one website.
     *
     * @return array
     */
    public function getAvailableExtractionDimensions()
    {
        return [];
    }

    /**
     * Copies a specified custom report to one or more sites. If a custom report with the same name already exists, the new custom report
     * will have an automatically adjusted name to make it unique to the assigned site.
     *
     * @param int $idSite
     * @param int $idCustomReport ID of the custom report to duplicate.
     * @param int[] $idDestinationSites Optional array of IDs identifying which site(s) the new custom report is to be
     * assigned to. The default is [idSite] when nothing is provided.
     * @return array
     * @throws \Exception
     */
    public function duplicateCustomReport(int $idSite, int $idCustomReport, array $idDestinationSites = []): array
    {
        return [];
    }

    /**
     * Adds a new custom report
     * @param int $idSite
     * @param string $name  The name of the report.
     * @param string $reportType    The type of report you want to create, for example 'table' or 'evolution'.
     *                              For a list of available reports call 'CustomReports.getAvailableReportTypes'
     * @param string[] $metricIds   A list of metric IDs. For a list of available metrics call 'CustomReports.getAvailableMetrics'
     * @param string $categoryId  By default, the report will be put into a custom report category unless a specific
     *                            categoryId is provided. For a list of available categories call 'CustomReports.getAvailableCategories'.
     * @param string[] $dimensionIds A list of dimension IDs.  For a list of available metrics call 'CustomReports.getAvailableDimensions'
     * @param bool|string $subcategoryId By default, a new reporting page will be created for this report unless you
     *                                   specifiy a specific name or subcategoryID. For a list of available subcategories
     *                                   call 'CustomReports.getAvailableCategories'.
     * @param string $description  An optional description for the report, will be shown in the title help icon of the report.
     * @param string $segmentFilter   An optional segment to filter the report data. Needs to be sent urlencoded.
     * @param string[] $multipleIdSites   An optional list of idsites for which we need to execute the report
     * @return int
     */
    public function addCustomReport($idSite, $name, $reportType, $metricIds, $categoryId = false, $dimensionIds = array(), $subcategoryId = false, $description = '', $segmentFilter = '', $multipleIdSites = [])
    {
        return 4;
    }

    /**
     * Updates an existing custom report. Be aware that if you change metrics, dimensions, the report type or the segment filter,
     * previously processed/archived reports may become unavailable and would need to be re-processed.
     *
     * @param int $idSite
     * @param int $idCustomReport
     * @param string $name  The name of the report.
     * @param string $reportType    The type of report you want to create, for example 'table' or 'evolution'.
     *                              For a list of available reports call 'CustomReports.getAvailableReportTypes'
     * @param string[] $metricIds   A list of metric IDs. For a list of available metrics call 'CustomReports.getAvailableMetrics'
     * @param string $categoryId  By default, the report will be put into a custom report category unless a specific
     *                            categoryId is provided. For a list of available categories call 'CustomReports.getAvailableCategories'.
     * @param string[] $dimensionIds A list of dimension IDs.  For a list of available metrics call 'CustomReports.getAvailableDimensions'
     * @param bool|string $subcategoryId By default, a new reporting page will be created for this report unless you
     *                                   specify a specific name or subcategoryID. For a list of available subcategories
     *                                   call 'CustomReports.getAvailableCategories'.
     * @param string $description  An optional description for the report, will be shown in the title help icon of the report.
     * @param string $segmentFilter   An optional segment to filter the report data. Needs to be sent urlencoded.
     * @param int[] $subCategoryReportIds List of sub report ids mapped to this report
     * @param string[] $multipleIdSites An optional list of idSites for which we need to execute the report
     */
    public function updateCustomReport(
        $idSite,
        $idCustomReport,
        $name,
        $reportType,
        $metricIds,
        $categoryId = false,
        $dimensionIds = [],
        $subcategoryId = false,
        $description = '',
        $segmentFilter = '',
        $subCategoryReportIds = [],
        $multipleIdSites = []
    ): void {
    }

    /**
     * Get all custom report configurations for a specific site.
     *
     * @param int $idSite
     * @param bool $skipCategoryMetadata
     * @return array
     */
    public function getConfiguredReports($idSite, $skipCategoryMetadata = false)
    {
        return [];
    }

    /**
     * Get a specific custom report configuration.
     *
     * @param int $idSite
     * @param int $idCustomReport The ID of the custom report. [@example=1]
     * @return array
     */
    public function getConfiguredReport($idSite, $idCustomReport)
    {
        return [];
    }

    /**
     * Deletes the given custom report.
     *
     * When a custom report is deleted, its report will be no longer available in the API and tracked data for this
     * report might be removed at some point by the system.
     *
     * @param int $idSite
     * @param int $idForm
     */
    public function deleteCustomReport($idSite, $idCustomReport): void
    {
    }

    /**
     * Pauses the given custom report.
     *
     * When a custom report is paused, its report will be no longer be archived
     *
     * @param int $idSite
     * @param int $idCustomReport
     */
    public function pauseCustomReport($idSite, $idCustomReport): void
    {
    }

    /**
     * Resumes the given custom report.
     *
     * When a custom report is resumed, its report will start archiving again
     *
     * @param int $idSite
     * @param int $idCustomReport
     */
    public function resumeCustomReport($idSite, $idCustomReport): void
    {
    }

    /**
     * Get a list of available categories that can be used in custom reports.
     *
     * @param int $idSite
     * @return array
     */
    public function getAvailableCategories($idSite)
    {
        return [];
    }

    /**
     * Get a list of available report types that can be used in custom reports.
     *
     * @return array
     */
    public function getAvailableReportTypes()
    {
        return [];
    }

    /**
     * Get a list of available dimensions that can be used in custom reports.
     *
     * @param int $idSite
     * @return array
     */
    public function getAvailableDimensions($idSite)
    {
        return [];
    }

    /**
     * Get a list of available metrics that can be used in custom reports.
     *
     * @param int $idSite
     * @return array
     */
    public function getAvailableMetrics($idSite)
    {
        return [];
    }

    /**
     * Get report data for a previously created custom report.
     *
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param int $idCustomReport
     * @param bool|string $segment
     * @param bool $expanded
     * @param bool $flat
     * @param int|bool $idSubtable
     * @param string|bool $columns
     * @return DataTable\DataTableInterface
     */
    public function getCustomReport($idSite, $period, $date, $idCustomReport, $segment = false, $expanded = false, $flat = false, $idSubtable = false, $columns = false)
    {
        return new DataTable();
    }

    /**
     * Get summary metrics for a specific funnel like the number of conversions, the conversion rate, the number of
     * entries etc.
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param int $idFunnel  Either idFunnel or idGoal has to be set
     * @param int $idGoal    Either idFunnel or idGoal has to be set. If goal given, will return the latest funnel for that goal. [@example=4]
     * @param string $segment
     *
     * @return DataTable|DataTable\Map
     */
    public function getMetrics($idSite, $period, $date, $idFunnel = false, $idGoal = false, $segment = false)
    {
        return new DataTable();
    }

    /**
     * Get funnel flow information. The returned datatable will include a row for each step within the funnel
     * showing information like how many visits have entered or left the funnel at a certain position, how many
     * have completed a certain step etc.
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param int $idFunnel  Either idFunnel or idGoal has to be set
     * @param int $idGoal    Either idFunnel or idGoal has to be set. If goal given, will return the latest funnel for that goal. [@example=4]
     * @param string $segment
     *
     * @return DataTable
     * @throws \Exception
     */
    public function getFunnelFlow($idSite, $period, $date, $idFunnel = false, $idGoal = false, $segment = false)
    {
        return new DataTable();
    }

    /**
     * Get funnel flow information. The returned datatable will include a row for each step within the funnel
     * showing information like how many visits have entered or left the funnel at a certain position, how many
     * have completed a certain step etc.
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param int $idFunnel  Either idFunnel or idGoal has to be set
     * @param int $idGoal    Either idFunnel or idGoal has to be set. If goal given, will return the latest funnel for that goal. [@example=4]
     * @param string $segment
     *
     * @return DataTable
     * @throws \Exception
     */
    public function getFunnelFlowTable($idSite, $period, $date, $idFunnel = false, $idGoal = false, $segment = false)
    {
        return new DataTable();
    }

    /**
     * Get subTable funnel flow information. The returned datatable will include a row for proceeded, entries, and
     * exists. If they have any values, they'll have a subTable of their own.
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param int $stepPosition The step number to pull the data for. [@example=1]
     * @param int $idFunnel  Either idFunnel or idGoal has to be set
     * @param int $idGoal    Either idFunnel or idGoal has to be set. If goal given, will return the latest funnel for that goal. [@example=4]
     * @param string $segment
     *
     * @return DataTable
     * @throws \Exception
     */
    public function getFunnelStepSubtable($idSite, $period, $date, $stepPosition, $idFunnel = false, $idGoal = false, $segment = false)
    {
        return new DataTable();
    }

    /**
     * Get all entry actions for the given funnel at the given step.
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param int $idFunnel The ID of the funnel for which to get data. [@example=99]
     * @param string $segment
     * @param string $step
     * @param bool $expanded
     * @param int|string $idSubtable
     * @param bool $flat
     *
     * @return DataTable
     */
    public function getFunnelEntries($idSite, $period, $date, $idFunnel, $segment = false, $step = false, $expanded = false, $idSubtable = false, $flat = false)
    {
        return new DataTable();
    }

    /**
     * Get all exit actions for the given funnel at the given step.
     *
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param int $idFunnel The ID of the funnel for which to get data. [@example=99]
     * @param string $segment
     * @param string $step
     *
     * @return DataTable
     */
    public function getFunnelExits($idSite, $period, $date, $idFunnel, $segment = false, $step = false)
    {
        return new DataTable();
    }

    /**
     * Get funnel information for this goal.
     *
     * @param int $idSite
     * @param int $idGoal The ID of the goal for which to get funnel data. [@example=4]
     *
     * @return array|null   Null when no funnel has been configured yet, the funnel otherwise.
     * @throws \Exception
     */
    public function getGoalFunnel($idSite, $idGoal)
    {
        return null;
    }

    /**
     * Get funnel information for this goal.
     *
     * @param int $idSite
     *
     * @return array|null   Null when no funnel has been configured yet, the funnel otherwise.
     * @throws \Exception
     */
    public function getSalesFunnelForSite($idSite)
    {
        return null;
    }

    /**
     * Get funnel information by ID.
     *
     * @param int $idSite
     * @param int $idFunnel The ID of the funnel for which to get data. [@example=99]
     *
     * @return array|null   Null when no funnel has been configured yet, the funnel otherwise.
     * @throws \Exception
     */
    public function getFunnel(int $idSite, int $idFunnel)
    {
        return null;
    }

    /**
     * Get activated funnels for the current site.
     *
     * @param int $idSite
     *
     * @return array
     */
    public function getAllActivatedFunnelsForSite($idSite)
    {
        return [];
    }

    /**
     * @param int $idSite
     *
     * @return bool
     */
    public function hasAnyActivatedFunnelForSite($idSite)
    {
        return true;
    }

    /**
     * Deletes the given goal funnel.
     *
     * @param int $idSite
     * @param int $idGoal The ID of the goal to which the funnel is tied.
     *
     * @throws \Exception
     */
    public function deleteGoalFunnel($idSite, $idGoal): void
    {
    }

    /**
     * Deletes the given goal funnel.
     *
     * @param int $idSite
     * @param int $idFunnel
     *
     * @throws \Exception
     */
    public function deleteNonGoalFunnel(int $idSite, int $idFunnel): void
    {
    }

    /**
     * Sets (overwrites) a funnel for this goal.
     *
     * @param int $idSite
     * @param int $idGoal
     * @param int $isActivated Whether the funnel is activated. E.g. 0 or 1. As soon as a funnel is activated, a report
     * will be generated for this funnel.
     * @param array[] $steps Definitions of each funnel step. If isActivated = true, there has to be at least one step.
     * E.g. [{'position': 1, 'name': 'Step1', 'pattern_type': 'path_contains', 'pattern': 'path/dir', 'required': 0}]
     *
     * @return int   The id of the created or updated funnel
     * @throws \Exception
     */
    public function setGoalFunnel($idSite, $idGoal, $isActivated, $steps = [])
    {
        return 4;
    }

    /**
     * Saves a funnel not tied to a goal.
     *
     * @param int $idSite
     * @param int $idFunnel ID of the funnel since we can't use the idSite and idGoal to identify it
     * @param string $funnelName The name used to identify the funnel since it's not tied to a goal
     * @param array $steps Definitions of each funnel step.Definitions of each funnel step.
     * E.g. [{'position': 1, 'name': 'Step1', 'pattern_type': 'path_contains', 'pattern': 'path/dir', 'required': 0}]
     *
     * @return int   The id of the created or updated funnel
     * @throws \Exception
     */
    public function saveNonGoalFunnel(int $idSite, int $idFunnel, string $funnelName, array $steps): int
    {
        return 4;
    }

    /**
     * Get a list of available pattern types that can be used to configure a funnel step.
     *
     * @return array
     * @throws \Exception
     */
    public function getAvailablePatternMatches()
    {
        return [];
    }

    /**
     * Tests whether a URL matches any of the step patterns.
     *
     * @param string $url A value used to filter funnel flow by. E.g. URL, path, event category, event name, page title,
     * goal ID, ... [@example="https://www.example.com/path/dir"]
     * @param array $steps Definitions of funnel steps.
     * [@example=[{"position": 1, "name": "Step1", "pattern_type": "path_contains", "pattern": "path/dir", "required": 0}]]
     * @return array
     * @throws \Exception
     */
    public function testUrlMatchesSteps($url, $steps)
    {
        $exampleResponse = [
            'url' => 'https://www.example.com/path/dir',
            'tests' => [
                'matches' => true,
                'pattern_type' => 'path_contains',
                'pattern' => 'path\/dir',
            ],
        ];

        return [];
    }
}
