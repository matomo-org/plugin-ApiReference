# API Reference Plugin for Matomo

## Description

The API Reference plugin generates an OpenAPI (Swagger) specification from Matomo API definitions and supported plugin metadata. 

As APIs and plugins expose additional metadata, developers can browse available API endpoints, explore request parameters and response formats, and test API requests directly from the documentation interface.

### Features include:

* Interactive Swagger/OpenAPI-based API documentation
* Request and response schema documentation
* Parameter descriptions and example requests
* Improved navigation and API discoverability
* Support for modern API documentation standards

The API Reference plugin improves API discoverability while supporting modern API documentation standards and tooling.

## Access the API Reference

Navigate to:

```text
Administration → System → API
```

The Swagger UI page will display API documentation for installed plugins.

## Generate OpenAPI Specifications

OpenAPI specifications are generated daily using a scheduled Matomo task.

To manually regenerate specifications:

```bash
./console core:run-scheduled-tasks "Piwik\Plugins\ApiReference\Tasks.generateConfiguredPluginSpecs"
```

This is useful after installing, enabling, or updating plugins.

**NOTE:** The initial generation process may take some time to complete, depending on the number of installed plugins.

## Read Specs Via the Reporting API

This plugin exposes a Reporting API method for reading generated OpenAPI specs directly.

To read a previously generated spec for a plugin:

```text
index.php?module=API&method=ApiReference.getOpenApiSpec&pluginName=Login&format=json
```

This returns the JSON spec file for the requested plugin.

If you are calling these endpoints outside the Matomo UI, include your usual authentication parameters such as `token_auth`.

## Authorise API Requests

To test API endpoints from Swagger UI:

1. Create or copy your Matomo API token from your user settings
2. Open the Swagger page
3. Click **Authorize**
4. Enter your API token
5. Confirm authorisation

Swagger UI will then execute requests using your Matomo permissions.

## Try an API Endpoint

1. Expand an endpoint in Swagger UI
2. Review available parameters
3. Click **Try it out**
4. Update request values if needed
5. Click **Execute**

Responses are returned directly from your Matomo instance.

## Technical Details

### Frontend Assets

Swagger UI is managed via npm inside `vue/package.json`.

The plugin runtime does not load Swagger UI directly from `node_modules`. Instead, the required distributable files are synced into:

```text
vue/lib/swagger-ui/
```

Typical workflow:

```bash
cd plugins/ApiReference/vue

npm install
npm run sync-swagger-ui

ddev matomo:console vue:build ApiReference
```

Plugin-specific Swagger UI overrides are located in:

```text
vue/src/SwaggerPage/swagger-ui-overrides.css
```

### Dependencies

This plugin uses vendored dependencies scoped with `matomo scoper` to avoid conflicts with dependencies used by other plugins.

If updating dependencies:

1. Run `composer install`
2. Re-scope dependencies using the plugin scoper configuration
3. Run Rector to ensure compatibility with Matomo's minimum supported PHP version

Example Rector configuration:

```php
<?php

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    // Matomo requires PHP >= 7.2.5, but PHP 7.3 is close enough.
    $rectorConfig->sets([
        \Rector\Set\ValueObject\DowngradeLevelSetList::DOWN_TO_PHP_73
    ]);

    $rectorConfig->skip([
        \Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector::class,

        // Skip downgrading Token for php-parser since it already provides a polyfill
        \Rector\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector::class => [
            '*/vendor/prefixed/nikic/php-parser/*',
        ],
    ]);
};
```

Run Rector using:

```bash
vendor/bin/rector process {path_to_this_plugin/vendor/prefixed} --config={path_to_config_file}
```

> NOTE: Internal Matomo development environments may include the `DevPluginCommands` plugin, which provides commands to automate dependency scoping and Rector processing. See the `SearchEngineKeywordsPerformance` plugin README for additional details.
