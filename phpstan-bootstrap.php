<?php

// Load either the plugin vendor or the Matomo root vendor autoloader.
foreach ([__DIR__ . '/vendor/autoload.php', __DIR__ . '/../../vendor/autoload.php'] as $autoload) {
    if (is_file($autoload)) {
        require_once $autoload;
        break;
    }
}

/**
 * Map a real class/interface/trait to the scoped name used in source,
 * so PHPStan can discover symbols under the scoped namespace.
 */
function oad_alias(string $orig, string $scoped): void
{
    if (class_exists($orig) && !class_exists($scoped, false)) {
        class_alias($orig, $scoped);
    }
    if (interface_exists($orig) && !interface_exists($scoped, false)) {
        class_alias($orig, $scoped);
    }
    if (trait_exists($orig) && !trait_exists($scoped, false)) {
        class_alias($orig, $scoped);
    }
}

oad_alias(
    'phpDocumentor\\Reflection\\DocBlockFactory',
    'Matomo\\Dependencies\\OpenApiDocs\\phpDocumentor\\Reflection\\DocBlockFactory'
);
oad_alias(
    'phpDocumentor\\Reflection\\DocBlock\\Tags\\Param',
    'Matomo\\Dependencies\\OpenApiDocs\\phpDocumentor\\Reflection\\DocBlock\\Tags\\Param'
);
oad_alias(
    'phpDocumentor\\Reflection\\DocBlock\\Tags\\TagWithType',
    'Matomo\\Dependencies\\OpenApiDocs\\phpDocumentor\\Reflection\\DocBlock\\Tags\\TagWithType'
);
