<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ApiReference;

class ApiReference extends \Piwik\Plugin
{
    public const DEFAULT_SPEC_VERSION = '1.0.0';
    public const OA_XML_ATTRIBUTES_TEMP_PROPERTY_NAME = 'oaXmlAttributes';
    public const OA_XML_ATTRIBUTES_DEFAULT_KEY_NAME = 'defaultKeyName';
    public const GENERATED_ANNOTATIONS_PATH = '/tmp/annotations/';
    public const EXAMPLE_RESPONSES_PATH = '/tmp/responses/';
    public const GENERATED_SPECS_PATH = '/tmp/specs/';
    public const AVAILABLE_PROPERTY_TYPES = ['string', 'number', 'integer', 'boolean', 'array', 'object', 'null'];
    public const PLUGIN_BLOCKLIST = ['Billing', 'Cloud', 'ConnectAccounts', 'CDN', 'ProxySite'];

    public function registerEvents()
    {
        return [
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    public function getClientSideTranslationKeys(&$translationKeys): void
    {
        $translationKeys[] = 'General_API';
        $translationKeys[] = 'CoreHome_LearnMoreFullStop';
        $translationKeys[] = 'ApiReference_LookingForLegacyApiReference';
        $translationKeys[] = 'ApiReference_ReportingApiMoreInformation';
        $translationKeys[] = 'ApiReference_ReportingApiReference';
        $translationKeys[] = 'ApiReference_ReportingApiSummary';
        $translationKeys[] = 'ApiReference_SwaggerPagePluginEmpty';
        $translationKeys[] = 'ApiReference_SwaggerPageRequestFailed';
        $translationKeys[] = 'ApiReference_SwaggerPageSpecLoadFailed';
        $translationKeys[] = 'ApiReference_SwaggerPageSearchNoResults';
        $translationKeys[] = 'ApiReference_SwaggerPageSearchPlaceholder';
        $translationKeys[] = 'ApiReference_UserAuthentication';
        $translationKeys[] = 'ApiReference_UserAuthenticationManageTokens';
        $translationKeys[] = 'ApiReference_UserAuthenticationUsingTokenAuth';
    }
}
