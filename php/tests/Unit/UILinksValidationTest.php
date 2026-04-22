<?php

use PHPUnit\Framework\TestCase;

class UILinksValidationTest extends TestCase
{
    public function testUIRelativeLinksAreAbsoluteOrRouted()
    {
        $uiFiles = array_merge(
            glob(__DIR__ . '/../../*.php'),
            glob(__DIR__ . '/../../admin/*.php')
        );

        // Matches href="something.php..." or href="/dashboard.php" but excludes:
        // - External links starting with http, mailto
        // - In-page anchors or queries starting with # or ?
        // - Direct PHP echoes starting with <?
        $pattern = '/href=["\']((?!(http|mailto|#|\?|javascript|<\?))[^"\']*\.php[^"\']*)["\']/i';

        foreach ($uiFiles as $file) {
            if (!file_exists($file)) continue;

            $content = file_get_contents($file);
            preg_match_all($pattern, $content, $matches);

            $violations = [];
            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    $violations[] = $match;
                }
            }

            $this->assertEmpty(
                $violations,
                "File " . basename($file) . " contains pure relative links to PHP scripts which will break on virtual router paths. Use absolute '/file.php' or routed '/games/...' paths instead. Found links:\n- " . implode("\n- ", $violations)
            );
        }
    }
}
