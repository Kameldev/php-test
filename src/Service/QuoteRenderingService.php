<?php

namespace App\Service;

use App\Entity\Quote;

/**
 * Service responsible for rendering Quote entities
 * Separated from Quote entity to respect Single Responsibility Principle
 */
class QuoteRenderingService
{
    /**
     * Render quote as HTML
     *
     * @param Quote $quote
     * @return string
     */
    public function renderHtml(Quote $quote): string
    {
        return '<p>' . $quote->getId() . '</p>';
    }

    /**
     * Render quote as plain text
     *
     * @param Quote $quote
     * @return string
     */
    public function renderText(Quote $quote): string
    {
        return (string) $quote->getId();
    }
}

