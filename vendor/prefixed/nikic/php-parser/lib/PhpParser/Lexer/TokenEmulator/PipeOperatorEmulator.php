<?php

declare (strict_types=1);
namespace Matomo\Dependencies\OpenApiDocs\PhpParser\Lexer\TokenEmulator;

use Matomo\Dependencies\OpenApiDocs\PhpParser\Lexer\TokenEmulator\TokenEmulator;
use Matomo\Dependencies\OpenApiDocs\PhpParser\PhpVersion;
use Matomo\Dependencies\OpenApiDocs\PhpParser\Token;
class PipeOperatorEmulator extends TokenEmulator
{
    public function getPhpVersion() : PhpVersion
    {
        return PhpVersion::fromComponents(8, 5);
    }
    public function isEmulationNeeded(string $code) : bool
    {
        return \strpos($code, '|>') !== \false;
    }
    public function emulate(string $code, array $tokens) : array
    {
        for ($i = 0, $c = count($tokens); $i < $c; ++$i) {
            $token = $tokens[$i];
            if ((is_array($token) ? $token[1] : $token) === '|' && isset($tokens[$i + 1]) && (is_array($tokens[$i + 1]) ? $tokens[$i + 1][1] : $tokens[$i + 1]) === '>') {
                array_splice($tokens, $i, 2, [new Token(\Matomo\Dependencies\OpenApiDocs\T_PIPE, '|>', $token->line, $token->pos)]);
                $c--;
            }
        }
        return $tokens;
    }
    public function reverseEmulate(string $code, array $tokens) : array
    {
        for ($i = 0, $c = count($tokens); $i < $c; ++$i) {
            $token = $tokens[$i];
            if ($token->id === \Matomo\Dependencies\OpenApiDocs\T_PIPE) {
                array_splice($tokens, $i, 1, [new Token(\ord('|'), '|', $token->line, $token->pos), new Token(\ord('>'), '>', $token->line, $token->pos + 1)]);
                $i++;
                $c++;
            }
        }
        return $tokens;
    }
}
