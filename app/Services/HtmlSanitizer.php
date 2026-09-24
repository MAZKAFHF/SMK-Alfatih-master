<?php

namespace App\Services;

use Mews\Purifier\Facades\Purifier;

class HtmlSanitizer
{
    /**
     * Clean HTML content for storage/display.
     * Allows rich text but blocks scripts, javascript: URIs, event handlers.
     */
    public static function clean(?string $html, string $config = 'default'): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        // Pre-strip obviously dangerous snippets before Purifier for belt-and-suspenders
        $html = preg_replace('/\son\w+\s*=\s*["\'][^"\']*["\']/i', '', (string) $html);

        $clean = Purifier::clean($html, $config);

        // Normalize Trix default <div> blocks to <p> for consistent prose styling
        // Trix wraps blocks in <div> by default; we configured Trix to use <p> for new content,
        // but normalize existing <div> for backwards compatibility
        if (str_contains($clean, '<div>')) {
            // Only convert simple divs containing text (not nested structures) to p
            $clean = preg_replace('#<div>(.*?)</div>#s', '<p>$1</p>', $clean);
        }

        return $clean;
    }
}
