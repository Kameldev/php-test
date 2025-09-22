<?php

namespace App\Service;

use App\Contract\PlaceholderReplacerInterface;
use App\Entity\Template;

/**
 * Service responsible for coordinating placeholder replacement
 * Respects Single Responsibility Principle by delegating to specific replacers
 */
class PlaceholderReplacementService
{
    /**
     * @var PlaceholderReplacerInterface[]
     */
    private $replacers;

    /**
     * @param PlaceholderReplacerInterface[] $replacers
     */
    public function __construct(array $replacers = array())
    {
        $this->replacers = $replacers;
    }

    /**
     * Process template with all registered replacers
     *
     * @param Template $template
     * @param array    $data
     * @return Template
     */
    public function processTemplate(Template $template, array $data)
    {
        $processed = clone $template;
        $processed->setSubject($this->replacePlaceholders($processed->getSubject(), $data));
        $processed->setContent($this->replacePlaceholders($processed->getContent(), $data));

        return $processed;
    }

    /**
     * Replace placeholders in text using all replacers
     *
     * @param string $text
     * @param array  $data
     * @return string
     */
    private function replacePlaceholders(string $text, array $data): string
    {
        if (empty($text)) {
            return $text;
        }

        foreach ($this->replacers as $replacer) {
            $text = $replacer->replace($text, $data);
        }

        return $text;
    }

    /**
     * Add a new placeholder replacer
     *
     * @param PlaceholderReplacerInterface $replacer
     */
    public function addReplacer(PlaceholderReplacerInterface $replacer)
    {
        $this->replacers[] = $replacer;
    }
}
