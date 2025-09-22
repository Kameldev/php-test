<?php

namespace App\Context;

use App\Contract\ApplicationContextInterface;
use App\Entity\Site;
use App\Entity\User;
use Faker\Factory;

/**
 * Application context providing current user and site information
 * Without Singleton pattern for better testability
 */
class ApplicationContext implements ApplicationContextInterface
{
    /**
     * @var Site
     */
    private $currentSite;

    /**
     * @var User
     */
    private $currentUser;

    /**
     * Initialize application context with fake data
     */
    public function __construct()
    {
        $faker = Factory::create();

        $this->currentSite = new Site(
            $faker->randomNumber(),
            $faker->url
        );

        $this->currentUser = new User(
            $faker->randomNumber(),
            $faker->firstName,
            $faker->lastName,
            $faker->email
        );
    }

    /**
     * Get the current site
     *
     * @return Site
     */
    public function getCurrentSite(): Site
    {
        return $this->currentSite;
    }

    /**
     * Get the current user
     *
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }
}