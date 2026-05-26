## FAQ

__Why is the OpenAPI specification file not generated?__

This can happen if the API Reference plugin is disabled, the Matomo cache has not been cleared, or the API specification generation process encountered an error.

Try the following:

* Ensure the API Reference plugin is installed and activated.
* Clear the Matomo cache.
* Check your server logs for PHP or permission errors.
* Verify that the Matomo instance can write generated files to the configured directory.
* Ensure all required plugins and dependencies are enabled.

The specification files are generated daily using a scheduled Matomo task. To check when this task is scheduled to run next, use the TasksTimetable plugin.

You can manually generate the specification files using the following command:

```bash
core:run-scheduled-tasks "Piwik\Plugins\ApiReference\Tasks.generateConfiguredPluginSpecs"
```

The initial generation process may take some time to complete, depending on the number of installed plugins.

__Can I still see the legacy API reference page?__

Yes. The legacy API reference remains available for compatibility and migration purposes while transitioning to the new API documentation.

__Why do some request or response schemas appear incomplete?__

The API Reference plugin generates request and response examples using data available in your Matomo instance. As a result, some response examples may appear incomplete if there is limited or no matching data available for a specific API request.

Not all API methods include response examples. Response examples are primarily generated for API methods that retrieve data from Matomo.

__Does the API Reference plugin replace existing Matomo APIs?__

No. The plugin modernises the API documentation experience while remaining compatible with existing Matomo APIs and integrations.

Existing API endpoints and integrations continue to function normally.
