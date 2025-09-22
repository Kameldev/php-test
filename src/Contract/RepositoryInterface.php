<?php

namespace App\Contract;

/**
 * Repository contract for dependency injection
 * Follows Dependency Inversion Principle
 */
interface RepositoryInterface
{
    /**
     * Retrieve entity by ID
     *
     * @param int $id
     * @return mixed
     */
    public function getById($id);
}
