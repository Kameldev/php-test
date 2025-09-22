<?php

namespace App\Contract;

/**
 * Placeholder replacer contract for Strategy pattern
 * Follows Open/Closed Principle
 */
interface PlaceholderReplacerInterface
{
    /**
     * Replace placeholders in text
     *
     * @param string $text
     * @param array  $data
     * @return string
     */
    public function replace(string $text, array $data): string;
}