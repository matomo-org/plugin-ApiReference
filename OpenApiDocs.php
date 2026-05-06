<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs;

class OpenApiDocs extends \Piwik\Plugin
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
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    public function getStylesheetFiles(&$stylesheets): void
    {
        $stylesheets[] = 'plugins/OpenApiDocs/vue/lib/swagger-ui/swagger-ui.css';
        $stylesheets[] = 'plugins/OpenApiDocs/vue/src/SwaggerPage/swagger-ui-overrides.css';
    }

    public function getJsFiles(&$jsFiles): void
    {
        $jsFiles[] = 'plugins/OpenApiDocs/vue/lib/swagger-ui/swagger-ui-bundle.js';
    }

    public function getClientSideTranslationKeys(&$translationKeys): void
    {
        $translationKeys[] = 'CoreHome_LearnMoreFullStop';
        $translationKeys[] = 'OpenApiDocs_ReportingApiMoreInformation';
        $translationKeys[] = 'OpenApiDocs_ReportingApiReference';
        $translationKeys[] = 'OpenApiDocs_ReportingApiSummary';
        $translationKeys[] = 'OpenApiDocs_SwaggerApi';
        $translationKeys[] = 'OpenApiDocs_SwaggerPagePluginEmpty';
        $translationKeys[] = 'OpenApiDocs_SwaggerPageRequestFailed';
        $translationKeys[] = 'OpenApiDocs_SwaggerPageSpecLoadFailed';
        $translationKeys[] = 'OpenApiDocs_SwaggerPageSearchNoResults';
        $translationKeys[] = 'OpenApiDocs_SwaggerPageSearchPlaceholder';
        $translationKeys[] = 'OpenApiDocs_UserAuthentication';
        $translationKeys[] = 'OpenApiDocs_UserAuthenticationManageTokens';
        $translationKeys[] = 'OpenApiDocs_UserAuthenticationUsingTokenAuth';
    }
}
