<?php

namespace App\Tests\Service\PlaceholderReplacer;

use App\Contract\ApplicationContextInterface;
use App\Entity\User;
use App\Service\PlaceholderReplacer\UserPlaceholderReplacer;
use PHPUnit_Framework_TestCase;

/**
 * tests for UserPlaceholderReplacer
 */
class UserPlaceholderReplacerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var UserPlaceholderReplacer
     */
    private $replacer;

    /**
     * @var ApplicationContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContext;

    public function setUp()
    {
        $this->mockContext = $this->createMock(ApplicationContextInterface::class);
        $this->replacer = new UserPlaceholderReplacer($this->mockContext);
    }

    /**
     * @test
     */
    public function shouldReplaceFirstNameFromDataUser()
    {
        $user = new User(1, 'JEAN', 'Dupont', 'jean@example.com');

        $text = 'Bonjour [user:first_name]';
        $result = $this->replacer->replace($text, ['user' => $user]);

        $this->assertEquals('Bonjour Jean', $result);
    }

    /**
     * @test
     */
    public function shouldReplaceFirstNameFromApplicationContext()
    {
        $contextUser = new User(2, 'MARIE', 'Martin', 'marie@example.com');
        $this->mockContext->method('getCurrentUser')->willReturn($contextUser);

        $text = 'Bonjour [user:first_name]';
        $result = $this->replacer->replace($text, []);

        $this->assertEquals('Bonjour Marie', $result);
    }

    /**
     * @test
     */
    public function shouldPrioritizeDataUserOverContextUser()
    {
        $dataUser = new User(1, 'PIERRE', 'Dupont', 'pierre@example.com');
        $contextUser = new User(2, 'MARIE', 'Martin', 'marie@example.com');

        $this->mockContext->method('getCurrentUser')->willReturn($contextUser);

        $text = 'Bonjour [user:first_name]';
        $result = $this->replacer->replace($text, ['user' => $dataUser]);

        $this->assertEquals('Bonjour Pierre', $result);
    }

    /**
     * @test
     */
    public function shouldCleanPlaceholderWhenNoUser()
    {
        $this->mockContext->method('getCurrentUser')->willReturn(null);

        $text = 'Bonjour [user:first_name]';
        $result = $this->replacer->replace($text, []);

        $this->assertEquals('Bonjour ', $result);
    }

    /**
     * @test
     */
    public function shouldCleanPlaceholderWhenInvalidUser()
    {
        $text = 'Bonjour [user:first_name]';
        $result = $this->replacer->replace($text, ['user' => 'invalid']);

        $this->assertEquals('Bonjour ', $result);
    }

    /**
     * @test
     */
    public function shouldHandleSpecialCharactersInFirstName()
    {
        $user = new User(1, 'JEAN-CLAUDE', 'Dupont', 'jean@example.com');

        $text = 'Bonjour [user:first_name]';
        $result = $this->replacer->replace($text, ['user' => $user]);

        $this->assertEquals('Bonjour Jean-claude', $result);
    }

    /**
     * @test
     */
    public function shouldHandleAccentedCharactersInFirstName()
    {
        $user = new User(1, 'ANDRÉ', 'Dupont', 'andre@example.com');

        $text = 'Bonjour [user:first_name]';
        $result = $this->replacer->replace($text, ['user' => $user]);

        $this->assertEquals('Bonjour André', $result);
    }

    /**
     * @test
     */
    public function shouldNotReplaceWhenNoPlaceholder()
    {
        $user = new User(1, 'JEAN', 'Dupont', 'jean@example.com');

        $text = 'Bonjour Monsieur';
        $result = $this->replacer->replace($text, ['user' => $user]);

        $this->assertEquals('Bonjour Monsieur', $result);
    }
}
