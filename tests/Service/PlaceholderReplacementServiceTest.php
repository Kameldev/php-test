<?php

namespace App\Tests\Service;

use App\Contract\PlaceholderReplacerInterface;
use App\Entity\Template;
use App\Service\PlaceholderReplacementService;
use PHPUnit_Framework_TestCase;

/**
 * tests for PlaceholderReplacementService
 */
class PlaceholderReplacementServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PlaceholderReplacementService
     */
    private $service;

    /**
     * @var PlaceholderReplacerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockReplacer;

    public function setUp()
    {
        $this->mockReplacer = $this->createMock(PlaceholderReplacerInterface::class);
        $this->service = new PlaceholderReplacementService([$this->mockReplacer]);
    }

    /**
     * @test
     */
    public function shouldProcessTemplateWithReplacers()
    {
        $template = new Template(1, 'Subject [placeholder]', 'Content [placeholder]');
        $data = ['test' => 'value'];

        $this->mockReplacer
            ->expects($this->exactly(2))
            ->method('replace')
            ->withConsecutive(
                ['Subject [placeholder]', $data],
                ['Content [placeholder]', $data]
            )
            ->willReturnOnConsecutiveCalls(
                'Subject replaced',
                'Content replaced'
            );

        $result = $this->service->processTemplate($template, $data);

        $this->assertEquals('Subject replaced', $result->getSubject());
        $this->assertEquals('Content replaced', $result->getContent());
    }

    /**
     * @test
     */
    public function shouldHandleEmptyReplacersArray()
    {
        $service = new PlaceholderReplacementService([]);
        $template = new Template(1, 'Subject', 'Content');

        $result = $service->processTemplate($template, []);

        $this->assertEquals('Subject', $result->getSubject());
        $this->assertEquals('Content', $result->getContent());
    }

    /**
     * @test
     */
    public function shouldHandleEmptyTemplate()
    {
        $template = new Template(1, '', '');

        $result = $this->service->processTemplate($template, []);

        $this->assertEquals('', $result->getSubject());
        $this->assertEquals('', $result->getContent());
    }

    /**
     * @test
     */
    public function shouldAddReplacerDynamically()
    {
        $newReplacer = $this->createMock(PlaceholderReplacerInterface::class);

        $this->service->addReplacer($newReplacer);

        // Vérifier que le nouveau replacer est appelé
        $template = new Template(1, 'test', 'test');
        $newReplacer->expects($this->exactly(2))->method('replace');

        $this->service->processTemplate($template, []);
    }
}
