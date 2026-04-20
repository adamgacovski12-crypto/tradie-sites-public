<?php
/**
 * Shared markdown-whitespace normaliser.
 *
 * Stripped from both the generator (before writing to disk) and the
 * template (before handing to Parsedown) so posts can never render as
 * code blocks because of stray leading indentation.
 *
 * Blocked from web by ^_ rule in .htaccess.
 */

function tradie_normalise_markdown(string $s): string {
    $lines = explode("\n", $s);
    $inFence = false;
    $out = [];
    foreach ($lines as $line) {
        $rtrimmed = rtrim($line);
        /* Toggle on ``` fences so any intentional code block keeps its indent. */
        if (preg_match('/^\s*```/', $rtrimmed)) {
            $inFence = !$inFence;
            $out[] = ltrim($rtrimmed);
            continue;
        }
        if ($inFence) {
            $out[] = $rtrimmed;
        } else {
            $out[] = ltrim($rtrimmed);
        }
    }
    return implode("\n", $out);
}
