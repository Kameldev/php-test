<?php

namespace App\Service\PlaceholderReplacer;

use App\Contract\PlaceholderReplacerInterface;
use App\Contract\RepositoryInterface;
use App\Entity\Quote;
use App\Service\QuoteRenderingService;

/**
 * Replacer for quote-related placeholders
 * Follows Open/Closed Principle - extensible without modification
 */
class QuotePlaceholderReplacer implements PlaceholderReplacerInterface
{
    /**
     * @var RepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var RepositoryInterface
     */
    private $siteRepository;

    /**
     * @var RepositoryInterface
     */
    private $destinationRepository;

    /**
     * @var QuoteRenderingService
     */
    private $quoteRenderingService;

    /**
     * @param RepositoryInterface   $quoteRepository
     * @param RepositoryInterface   $siteRepository
     * @param RepositoryInterface   $destinationRepository
     * @param QuoteRenderingService $quoteRenderingService
     */
    public function __construct(
        RepositoryInterface $quoteRepository,
        RepositoryInterface $siteRepository,
        RepositoryInterface $destinationRepository,
        QuoteRenderingService $quoteRenderingService
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->siteRepository = $siteRepository;
        $this->destinationRepository = $destinationRepository;
        $this->quoteRenderingService = $quoteRenderingService;
    }

    /**
     * Replace quote-related placeholders
     *
     * @param string $text
     * @param array  $data
     * @return string
     */
    public function replace(string $text, array $data): string
    {
        $quote = $this->extractQuote($data);

        if (!$quote) {
            return $this->cleanQuotePlaceholders($text);
        }

        try {
            return $this->processQuotePlaceholders($text, $quote);
        } catch (\Exception $e) {
            return $this->cleanQuotePlaceholders($text);
        }
    }

    /**
     * Extract quote from data array
     *
     * @param array $data
     * @return Quote|null
     */
    private function extractQuote(array $data)
    {
        return (isset($data['quote']) && $data['quote'] instanceof Quote)
            ? $data['quote']
            : null;
    }

    /**
     * Process all quote placeholders
     *
     * @param string $text
     * @param Quote  $quote
     * @return string
     */
    private function processQuotePlaceholders(string $text, Quote $quote): string
    {
        // Récupération sécurisée des entités
        $quoteFromRepository = $this->quoteRepository->getById($quote->getId());
        $site = $this->siteRepository->getById($quote->getSiteId());
        $destination = $this->destinationRepository->getById($quote->getDestinationId());

        // Traitement des différents types de placeholders
        $text = $this->replaceSummaryPlaceholders($text, $quoteFromRepository);
        $text = $this->replaceDestinationName($text, $destination);
        $text = $this->replaceDestinationLink($text, $quote, $site, $quoteFromRepository);

        return $text;
    }

    /**
     * Replace summary placeholders using rendering service
     *
     * @param string $text
     * @param Quote  $quote
     * @return string
     */
    private function replaceSummaryPlaceholders(string $text, Quote $quote): string
    {
        $containsSummaryHtml = strpos($text, '[quote:summary_html]') !== false;
        $containsSummary = strpos($text, '[quote:summary]') !== false;

        if ($containsSummaryHtml) {
            $text = str_replace(
                '[quote:summary_html]',
                $this->quoteRenderingService->renderHtml($quote),
                $text
            );
        }

        if ($containsSummary) {
            $text = str_replace(
                '[quote:summary]',
                $this->quoteRenderingService->renderText($quote),
                $text
            );
        }

        return $text;
    }

    /**
     * Replace destination name placeholder
     *
     * @param string $text
     * @param mixed  $destination
     * @return string
     */
    private function replaceDestinationName(string $text, $destination): string
    {
        if (strpos($text, '[quote:destination_name]') !== false && $destination) {
            $text = str_replace('[quote:destination_name]', $destination->getCountryName(), $text);
        }

        return $text;
    }

    /**
     * Replace destination link placeholder
     * FIXED: Proper initialization of destinationForLink variable
     *
     * @param string $text
     * @param Quote  $quote
     * @param mixed  $site
     * @param Quote  $quoteFromRepository
     * @return string
     */
    private function replaceDestinationLink(string $text, Quote $quote, $site, Quote $quoteFromRepository): string
    {
        if (strpos($text, '[quote:destination_link]') === false) {
            return $text;
        }

        try {
            $destinationForLink = $this->destinationRepository->getById($quote->getDestinationId());

            if ($destinationForLink && $site && $quoteFromRepository) {
                $link = $site->getUrl() . '/' . $destinationForLink->getCountryName() . '/quote/' . $quoteFromRepository->getId();
                return str_replace('[quote:destination_link]', $link, $text);
            }
        } catch (\Exception $e) {
            // En cas d'erreur de récupération, on vide le placeholder
        }

        return str_replace('[quote:destination_link]', '', $text);
    }

    /**
     * Clean all quote placeholders when no quote available
     *
     * @param string $text
     * @return string
     */
    private function cleanQuotePlaceholders(string $text): string
    {
        $quotePlaceholders = array(
            '[quote:summary_html]',
            '[quote:summary]',
            '[quote:destination_name]',
            '[quote:destination_link]'
        );

        return str_replace($quotePlaceholders, '', $text);
    }
}
