<?php

declare(strict_types=1);

$inputDir = $argv[1] ?? '/tmp/openapidocs-ai-output';
$outputFile = $argv[2] ?? '/tmp/openapidocs-ai-output/all_presets.json';
$reportFile = $argv[3] ?? '/tmp/openapidocs-ai-output/all_presets_report.json';

if (!is_dir($inputDir)) {
    fwrite(STDERR, "Input directory not found: {$inputDir}\n");
    exit(1);
}

$files = glob(rtrim($inputDir, '/\\') . '/*_presets.json');
sort($files);

$mergedPresets = [];
$presetSources = [];
$collisionReports = [];
$uncertainReports = [];
$notesByPlugin = [];
$processedPlugins = [];

foreach ($files as $file) {
    $contents = file_get_contents($file);
    if ($contents === false) {
        fwrite(STDERR, "Failed to read file: {$file}\n");
        continue;
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        fwrite(STDERR, "Skipping invalid JSON: {$file}\n");
        continue;
    }

    $plugin = is_string($decoded['plugin'] ?? null) ? $decoded['plugin'] : basename($file, '_presets.json');
    $processedPlugins[] = $plugin;

    $candidatePresets = $decoded['candidate_presets'] ?? [];
    if (is_array($candidatePresets)) {
        foreach ($candidatePresets as $parameter => $value) {
            if (!is_string($parameter) || $parameter === '') {
                continue;
            }

            $existingValue = $mergedPresets[$parameter] ?? null;
            if (array_key_exists($parameter, $mergedPresets) && $existingValue != $value) {
                $collisionReports[] = [
                    'parameter' => $parameter,
                    'existing_value' => $existingValue,
                    'existing_plugin' => $presetSources[$parameter] ?? null,
                    'new_value' => $value,
                    'new_plugin' => $plugin,
                    'type' => 'candidate_presets_conflict',
                ];
            }

            $mergedPresets[$parameter] = $value;
            $presetSources[$parameter] = $plugin;
        }
    }

    $collisions = $decoded['collisions'] ?? [];
    if (is_array($collisions)) {
        foreach ($collisions as $collision) {
            if (!is_array($collision)) {
                continue;
            }

            $collisionReports[] = [
                'plugin' => $plugin,
                'parameter' => $collision['parameter'] ?? null,
                'reason' => $collision['reason'] ?? null,
                'type' => 'ai_flagged_collision',
            ];
        }
    }

    $uncertain = $decoded['uncertain'] ?? [];
    if (is_array($uncertain)) {
        foreach ($uncertain as $item) {
            if (!is_array($item)) {
                continue;
            }

            $uncertainReports[] = [
                'plugin' => $plugin,
                'parameter' => $item['parameter'] ?? null,
                'reason' => $item['reason'] ?? null,
            ];
        }
    }

    $notes = $decoded['notes'] ?? [];
    if (is_array($notes) && $notes !== []) {
        $notesByPlugin[$plugin] = array_values(array_filter($notes, 'is_string'));
    }
}

$mergedOutput = [
    'candidate_presets' => $mergedPresets,
    'sources' => $presetSources,
];

$reportOutput = [
    'processed_plugins' => $processedPlugins,
    'collision_count' => count($collisionReports),
    'uncertain_count' => count($uncertainReports),
    'collisions' => $collisionReports,
    'uncertain' => $uncertainReports,
    'notes_by_plugin' => $notesByPlugin,
];

$outputDir = dirname($outputFile);
if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
    fwrite(STDERR, "Failed to create output directory: {$outputDir}\n");
    exit(1);
}

$reportDir = dirname($reportFile);
if (!is_dir($reportDir) && !mkdir($reportDir, 0777, true) && !is_dir($reportDir)) {
    fwrite(STDERR, "Failed to create report directory: {$reportDir}\n");
    exit(1);
}

$mergedJson = json_encode($mergedOutput, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$reportJson = json_encode($reportOutput, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if ($mergedJson === false || $reportJson === false) {
    fwrite(STDERR, "Failed to encode output JSON.\n");
    exit(1);
}

if (file_put_contents($outputFile, $mergedJson . PHP_EOL) === false) {
    fwrite(STDERR, "Failed to write merged output: {$outputFile}\n");
    exit(1);
}

if (file_put_contents($reportFile, $reportJson . PHP_EOL) === false) {
    fwrite(STDERR, "Failed to write report output: {$reportFile}\n");
    exit(1);
}

fwrite(STDOUT, "Merged " . count($processedPlugins) . " plugin files.\n");
fwrite(STDOUT, "Wrote presets: {$outputFile}\n");
fwrite(STDOUT, "Wrote report: {$reportFile}\n");
