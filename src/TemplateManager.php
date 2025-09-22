<?php

namespace App;

use App\Entity\Template;
use App\Factory\TemplateManagerFactory;
use App\Service\PlaceholderReplacementService;

/**
 * Template Manager with dependency injection support
 * SIGNATURE PRESERVED - This class maintains backward compatibility
 */
class TemplateManager
{
    /**
     * @var PlaceholderReplacementService
     */
    private $placeholderService;

    /**
     * Constructor with optional dependency injection
     * Maintains backward compatibility while enabling testability
     *
     * @param PlaceholderReplacementService|null $placeholderService
     */
    public function __construct(PlaceholderReplacementService $placeholderService = null)
    {
        $this->placeholderService = $placeholderService ?: TemplateManagerFactory::createPlaceholderReplacementService();
    }

    /**
     * SIGNATURE PRESERVED - DO NOT MODIFY
     * This method is called everywhere and cannot be changed
     *
     * @param Template $tpl
     * @param array    $data
     * @return Template
     * @throws \RuntimeException
     */
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        return $this->placeholderService->processTemplate($tpl, $data);
    }
}