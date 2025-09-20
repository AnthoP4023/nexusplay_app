<?php
if (!function_exists('highlightSearchTerm')) {
    function highlightSearchTerm($text, $term) {
        if (empty($term)) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }

        $escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $escapedTerm = htmlspecialchars($term, ENT_QUOTES, 'UTF-8');

        return preg_replace(
            "/(" . preg_quote($escapedTerm, "/") . ")/i",
            '<span class="highlight">$1</span>',
            $escapedText
        );
    }
}
?>
