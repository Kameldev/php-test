<?php

namespace App\Repository;

use App\Contract\RepositoryInterface;
use App\Entity\Site;

/**
 * Site repository implementation without Singleton pattern
 * Provides access to Site entities
 */
class SiteRepository implements RepositoryInterface
{
    /**
     * Retrieve a Site by its ID
     *
     * @param int $id
     * @return Site
     */
    public function getById($id): Site
    {
        // DO NOT MODIFY THIS METHOD
        $generator = Faker\Factory::create();
        $generator->seed($id);

        return new Site($id, $generator->url);
    }
}