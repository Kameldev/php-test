<?php

namespace App\Tests;

use App\Context\ApplicationContext;
use App\Entity\Quote;
use App\Entity\Template;
use App\Repository\DestinationRepository;
use App\TemplateManager;
use PHPUnit_Framework_TestCase;

/**
 * Template Manager tests with updated namespaces
 */
class TemplateManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Init the mocks
     */
    public function setUp()
    {
        // Test setup
    }

    /**
     * Closes the mocks
     */
    public function tearDown()
    {
        // Test cleanup
    }

    /**
     * Test template processing with quote and user data
     *
     * @test
     */
    public function test()
    {
        $faker = \Faker\Factory::create();

        $destinationId = $faker->randomNumber();
        $expectedDestination = (new DestinationRepository())->getById($destinationId);
        $expectedUser = (new ApplicationContext())->getCurrentUser();

        $quote = new Quote(
            $faker->randomNumber(),
            $faker->randomNumber(),
            $destinationId,
            new \DateTime($faker->date())
        );

        $template = new Template(
            1,
            'Votre livraison à [quote:destination_name]',
            "
Bonjour [user:first_name],

Merci de nous avoir contacté pour votre livraison à [quote:destination_name].

Bien cordialement,

L'équipe de Shipper
"
        );

        $templateManager = new TemplateManager();

        $message = $templateManager->getTemplateComputed(
            $template,
            [
                'quote' => $quote
            ]
        );

        $this->assertEquals(
            'Votre livraison à ' . $expectedDestination->getCountryName(),
            $message->getSubject()
        );

        $this->assertEquals(
            "
Bonjour " . ucfirst(mb_strtolower($expectedUser->getFirstname())) . ",

Merci de nous avoir contacté pour votre livraison à " . $expectedDestination->getCountryName() . ".

Bien cordialement,

L'équipe de Shipper
",
            $message->getContent()
        );
    }
}