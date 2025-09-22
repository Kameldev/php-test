<?php

namespace App\Factory;

use App\Context\ApplicationContext;
use App\Repository\DestinationRepository;
use App\Repository\QuoteRepository;
use App\Repository\SiteRepository;
use App\Service\PlaceholderReplacementService;
use App\Service\PlaceholderReplacer\QuotePlaceholderReplacer;
use App\Service\PlaceholderReplacer\UserPlaceholderReplacer;
use App\Service\QuoteRenderingService;
use App\TemplateManager;

/**
 * Factory for creating TemplateManager with proper dependency injection
 * Provides backward compatibility while enabling testability
 */
class TemplateManagerFactory
{
    /**
     * Create PlaceholderReplacementService with all dependencies injected
     *
     * @return PlaceholderReplacementService
     */
    public static function createPlaceholderReplacementService(): PlaceholderReplacementService
    {
        // Création des repositories (sans singleton)
        $quoteRepository = new QuoteRepository();
        $siteRepository = new SiteRepository();
        $destinationRepository = new DestinationRepository();
        $applicationContext = new ApplicationContext();
        $quoteRenderingService = new QuoteRenderingService();

        // Création des replacers avec injection
        $quoteReplacer = new QuotePlaceholderReplacer(
            $quoteRepository,
            $siteRepository,
            $destinationRepository,
            $quoteRenderingService
        );

        $userReplacer = new UserPlaceholderReplacer($applicationContext);

        // Création du service avec tous les replacers
        return new PlaceholderReplacementService(array(
            $quoteReplacer,
            $userReplacer
        ));
    }

    /**
     * Create complete TemplateManager with dependencies
     *
     * @return TemplateManager
     */
    public static function createTemplateManager(): TemplateManager
    {
        return new TemplateManager(self::createPlaceholderReplacementService());
    }
}
