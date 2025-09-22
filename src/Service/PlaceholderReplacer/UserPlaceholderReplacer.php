<?php

namespace App\Service\PlaceholderReplacer;

use App\Contract\ApplicationContextInterface;
use App\Contract\PlaceholderReplacerInterface;
use App\Entity\User;

/**
 * Replacer for user-related placeholders
 * Follows Open/Closed Principle - extensible without modification
 */
class UserPlaceholderReplacer implements PlaceholderReplacerInterface
{
    /**
     * @var ApplicationContextInterface
     */
    private $applicationContext;

    /**
     * @param ApplicationContextInterface $applicationContext
     */
    public function __construct(ApplicationContextInterface $applicationContext)
    {
        $this->applicationContext = $applicationContext;
    }

    /**
     * Replace user-related placeholders
     *
     * @param string $text
     * @param array  $data
     * @return string
     */
    public function replace(string $text, array $data): string
    {
        $user = $this->extractUser($data);

        if (!$user) {
            return $this->cleanUserPlaceholders($text);
        }

        return $this->processUserPlaceholders($text, $user);
    }

    /**
     * Extract user from data or application context
     *
     * @param array $data
     * @return User|null
     */
    private function extractUser(array $data)
    {
        // Priorité aux données passées, sinon contexte applicatif
        if (isset($data['user']) && $data['user'] instanceof User) {
            return $data['user'];
        }

        return $this->applicationContext->getCurrentUser();
    }

    /**
     * Process user placeholders
     *
     * @param string $text
     * @param User   $user
     * @return string
     */
    private function processUserPlaceholders(string $text, User $user): string
    {
        if (strpos($text, '[user:first_name]') !== false) {
            $text = str_replace(
                '[user:first_name]',
                ucfirst(mb_strtolower($user->getFirstname())),
                $text
            );
        }

        // Facilement extensible pour d'autres placeholders user
        // [user:last_name], [user:email], etc.

        return $text;
    }

    /**
     * Clean user placeholders when no user available
     *
     * @param string $text
     * @return string
     */
    private function cleanUserPlaceholders(string $text): string
    {
        $userPlaceholders = array(
            '[user:first_name]'
            // Ajouter d'autres si nécessaire
        );

        return str_replace($userPlaceholders, '', $text);
    }
}