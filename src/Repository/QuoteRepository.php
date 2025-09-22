<?php

namespace App\Repository;

use App\Contract\RepositoryInterface;
use App\Entity\Quote;
use DateTime;

/**
 * Quote repository implementation without Singleton pattern
 * Provides access to Quote entities
 */
class QuoteRepository implements RepositoryInterface
{
    /**
     * @param int $id
     * @return Quote
     */
    public function getById($id)
    {
        // DO NOT MODIFY THIS METHOD
        $generator = Faker\Factory::create();
        $generator->seed($id);

        return new Quote(
            $id,
            $generator->numberBetween(1, 10),
            $generator->numberBetween(1, 200),
            new DateTime()
        );
    }
}