<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

use Isolated\Symfony\Component\Finder\Finder;

$dependenciesToPrefix = json_decode(getenv('MATOMO_DEPENDENCIES_TO_PREFIX'), true);
$namespacesToPrefix = json_decode(getenv('MATOMO_NAMESPACES_TO_PREFIX'), true);
$isRenamingReferences = getenv('MATOMO_RENAME_REFERENCES') == 1;
$pluginName = getenv('MATOMO_PLUGIN');

$namespacesToExclude = [];
$forceNoGlobalAlias = false;

/**
 * Move PHP 8 attributes onto their own line so the scoped dependencies still parse on our minimum supported PHP.
 *
 * PHP 7 reads `#[` as a comment running to the end of the line, so an attribute sitting alone on a line is inert
 * there while still applying on PHP 8 - the style Matomo core itself uses. Written inline, as vendors commonly do
 * for parameters, it instead comments out the rest of the signature and causes a parse error. Rector is deliberately
 * not allowed to downgrade attributes away, since they are worth keeping on PHP 8, so they are reformatted here.
 *
 * Tokenising rather than matching text keeps strings such as preg_match('#[abc]#', ...) from looking like attributes.
 */
$moveAttributesToOwnLine = static function (string $content): string {
    if (!defined('T_ATTRIBUTE')) {
        // Running on a PHP where attributes are already only comments, so there is nothing to move
        return $content;
    }

    $tokens = token_get_all($content);
    $count = count($tokens);
    $result = '';

    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];

        if (!is_array($token) || $token[0] !== T_ATTRIBUTE) {
            $result .= is_array($token) ? $token[1] : $token;
            continue;
        }

        $lineStart = strrpos($result, "\n");
        preg_match('/^[ \t]*/', $lineStart === false ? $result : substr($result, $lineStart + 1), $matches);
        $indent = $matches[0];

        // Copy the attribute across, counting brackets so that arrays in its arguments do not end it early
        $depth = 0;
        for (; $i < $count; $i++) {
            $inner = $tokens[$i];
            $text = is_array($inner) ? $inner[1] : $inner;
            $result .= $text;

            if ((is_array($inner) && $inner[0] === T_ATTRIBUTE) || $text === '[') {
                $depth++;
            } elseif ($text === ']') {
                $depth--;
                if ($depth === 0) {
                    break;
                }
            }
        }

        // Anything but whitespace before the next newline means the attribute is inline and has to be split
        $isInline = false;
        for ($j = $i + 1; $j < $count; $j++) {
            $text = is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];
            $newline = strpos($text, "\n");

            if ($newline !== false) {
                $isInline = trim(substr($text, 0, $newline)) !== '';
                break;
            }
            if (trim($text) !== '') {
                $isInline = true;
                break;
            }
        }

        if (!$isInline) {
            continue;
        }

        $result .= "\n" . $indent . '    ';

        // Drop the space that used to separate the attribute from what followed it
        if (
            isset($tokens[$i + 1]) && is_array($tokens[$i + 1])
            && $tokens[$i + 1][0] === T_WHITESPACE && strpos($tokens[$i + 1][1], "\n") === false
        ) {
            $tokens[$i + 1][1] = '';
        }
    }

    return $result;
};

if ($isRenamingReferences) {
    $finders = [
        Finder::create()
            ->files()
            ->in(__DIR__)
            ->exclude('vendor')
            ->exclude('node_modules')
            ->exclude('lang')
            ->exclude('javascripts')
            ->exclude('vue')
            ->notName(['scoper.inc.php'])
            ->filter(function (\SplFileInfo $file) {
                return !($file->isLink() && $file->isDir());
            })
            ->filter(function (\SplFileInfo $file) {
                return !($file->isLink() && !$file->getRealPath());
            }),
    ];
} else {
    $finders = array_map(function ($dependency) {
        return Finder::create()
            ->files()
            ->in($dependency);
    }, $dependenciesToPrefix);
}

$namespacesToIncludeRegexes = array_map(function ($n) {
    $n = rtrim($n, '\\');
    return '/^' . preg_quote($n) . '(?:\\\\|$)/';
}, $namespacesToPrefix);

return [
    'expose-global-constants' => false,
    'expose-global-classes' => false,
    'expose-global-functions' => false,
    'force-no-global-alias' => $forceNoGlobalAlias,
    'prefix' => 'Matomo\\Dependencies\\' . $pluginName,
    'finders' => $finders,
    'patchers' => [
        // Patcher for making sure that AbstractAnnotation is looking for the correct root
        static function (string $filePath, string $prefix, string $content) use ($isRenamingReferences): string {
            if ($isRenamingReferences) {
                return $content;
            }

            // Fix the string reference of a scoped dependency in the AbstractAnnotation class
            $escapedPrefix = str_replace('\\', '\\\\', $prefix);
            if ($filePath === __DIR__ . '/vendor/zircote/swagger-php/src/Annotations/AbstractAnnotation.php') {
                $content = str_replace(
                    'OpenApi\\\\Annotations\\\\',
                    "{$escapedPrefix}\\\\OpenApi\\\\Annotations\\\\",
                    $content
                );
            }

            return $content;
        },

        // Patcher keeping attributes parseable on the minimum supported PHP version
        static function (string $filePath, string $prefix, string $content) use ($isRenamingReferences, $moveAttributesToOwnLine): string {
            if ($isRenamingReferences) {
                return $content;
            }

            return $moveAttributesToOwnLine($content);
        },
    ],
    'include-namespaces' => $namespacesToIncludeRegexes,
    'exclude-namespaces' => $namespacesToExclude,
    // PHP's own attribute, newer than php-scoper's list of global symbols, so it would otherwise be prefixed to a
    // class that is not shipped and PHP 8.3's override validation would be silently lost
    'exclude-classes' => ['Override'],
    'exclude-constants' => [
        'PIWIK_TEST_MODE',
        '/^self::/', // work around php-scoper bug
    ],
    'exclude-functions' => ['Piwik_ShouldPrintBackTraceWithMessage'],
];
