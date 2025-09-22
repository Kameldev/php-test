<?php

namespace App\Repository;

use App\Contract\RepositoryInterface;
use App\Entity\Destination;

/**
 * Destination repository implementation without Singleton pattern
 * Provides access to Destination entities
 */
class DestinationRepository implements RepositoryInterface
{
    /**
     * @param int $id
     * @return Destination
     */
    public function getById($id)
    {
        // DO NOT MODIFY THIS METHOD
        $generator    = Faker\Factory::create();
        $generator->seed($id);

        return new Destination(
            $id,
            $generator->country,
            'en',
            $generator->slug()
        );
    }
}