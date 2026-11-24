<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * Sanitize HTML content by allowing safe tags and stripping dangerous scripts, event handlers, and styles.
     */
    public static function clean(?string $content): ?string
    {
        if ($content === null || trim($content) === '') {
            return null;
        }

        // Strip dangerous tags completely along with their contents
        $pattern = '/<(script|style|iframe|object|embed|applet|form|input|button|svg|canvas)[^>]*?>.*?<\/\\1>/si';
        $cleaned = preg_replace($pattern, '', $content) ?? '';

        // Strip single self-closing dangerous tags
        $cleaned = preg_replace('/<(script|style|iframe|object|embed|applet|form|input|button|svg|canvas)[^>]*?\/?>/si', '', $cleaned) ?? '';

        // Allow basic formatting tags
        $allowedTags = '<p><br><b><strong><i><em><u><ul><ol><li><a><blockquote><h1><h2><h3><h4><h5><h6><span>';
        $stripped = strip_tags($cleaned, $allowedTags);

        // Remove any on* event attributes (e.g. onload=..., onclick=..., onerror=...)
        $stripped = preg_replace('/\s*on[a-z]+\s*=\s*(["\'][^"\']*["\']|[^\s>]+)/i', '', $stripped) ?? '';

        // Remove javascript: or data: in href attributes
        $stripped = preg_replace('/href\s*=\s*["\']\s*(javascript|data|vbscript):[^"\']*["\']/i', 'href="#"', $stripped) ?? '';

        return trim($stripped);
    }
}
