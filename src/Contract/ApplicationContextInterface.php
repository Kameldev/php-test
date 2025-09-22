<?php

namespace App\Contract;

use App\Entity\Site;
use App\Entity\User;

/**
 * Application context contract for dependency injection
 */
interface ApplicationContextInterface
{
    /**
     * Get the current user
     *
     * @return User
     */
    public function getCurrentUser(): User;

    /**
     * Get the current site
     *
     * @return Site
     */
    public function getCurrentSite(): Site;
}