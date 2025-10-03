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
    public const GENERATED_ANNOTATIONS_PATH = '/tmp/annotations/';
    public const EXAMPLE_RESPONSES_PATH = '/tmp/responses/';
    public const GENERATED_SPECS_PATH = '/tmp/specs/';
    public const AVAILABLE_PROPERTY_TYPES = ['string', 'number', 'integer', 'boolean', 'array', 'object', 'null'];

    public function registerEvents()
    {
        return [];
    }
}
