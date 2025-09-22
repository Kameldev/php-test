<?php

namespace App\Tests\Service\PlaceholderReplacer;

use App\Contract\RepositoryInterface;
use App\Entity\Destination;
use App\Entity\Quote;
use App\Entity\Site;
use App\Service\PlaceholderReplacer\QuotePlaceholderReplacer;
use App\Service\QuoteRenderingService;
use DateTime;
use Exception;
use PHPUnit_Framework_TestCase;

/**
 * tests for QuotePlaceholderReplacer covering all edge cases
 */
class QuotePlaceholderReplacerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var QuotePlaceholderReplacer
     */
    private $replacer;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQuoteRepo;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSiteRepo;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDestinationRepo;

    /**
     * @var QuoteRenderingService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRenderingService;

    public function setUp()
    {
        $this->mockQuoteRepo = $this->createMock(RepositoryInterface::class);
        $this->mockSiteRepo = $this->createMock(RepositoryInterface::class);
        $this->mockDestinationRepo = $this->createMock(RepositoryInterface::class);
        $this->mockRenderingService = $this->createMock(QuoteRenderingService::class);

        $this->replacer = new QuotePlaceholderReplacer(
            $this->mockQuoteRepo,
            $this->mockSiteRepo,
            $this->mockDestinationRepo,
            $this->mockRenderingService
        );
    }

    /**
     * @test
     */
    public function shouldReplaceDestinationNamePlaceholder()
    {
        $quote = new Quote(1, 2, 3, new DateTime());
        $destination = new Destination(3, 'France', 'en', 'france');

        $this->mockQuoteRepo->method('getById')->willReturn($quote);
        $this->mockSiteRepo->method('getById')->willReturn(new Site(2, 'https://example.com'));
        $this->mockDestinationRepo->method('getById')->willReturn($destination);

        $text = 'Livraison vers [quote:destination_name]';
        $result = $this->replacer->replace($text, ['quote' => $quote]);

        $this->assertEquals('Livraison vers France', $result);
    }

    /**
     * @test
     */
    public function shouldReplaceSummaryHtmlPlaceholder()
    {
        $quote = new Quote(1, 2, 3, new DateTime());

        $this->mockQuoteRepo->method('getById')->willReturn($quote);
        $this->mockSiteRepo->method('getById')->willReturn(new Site(2, 'https://example.com'));
        $this->mockDestinationRepo->method('getById')->willReturn(new Destination(3, 'France', 'en', 'france'));
        $this->mockRenderingService->method('renderHtml')->willReturn('<p>Quote HTML</p>');

        $text = 'Summary: [quote:summary_html]';
        $result = $this->replacer->replace($text, ['quote' => $quote]);

        $this->assertEquals('Summary: <p>Quote HTML</p>', $result);
    }

    /**
     * @test
     */
    public function shouldReplaceSummaryTextPlaceholder()
    {
        $quote = new Quote(1, 2, 3, new DateTime());

        $this->mockQuoteRepo->method('getById')->willReturn($quote);
        $this->mockSiteRepo->method('getById')->willReturn(new Site(2, 'https://example.com'));
        $this->mockDestinationRepo->method('getById')->willReturn(new Destination(3, 'France', 'en', 'france'));
        $this->mockRenderingService->method('renderText')->willReturn('Quote Text');

        $text = 'Summary: [quote:summary]';
        $result = $this->replacer->replace($text, ['quote' => $quote]);

        $this->assertEquals('Summary: Quote Text', $result);
    }

    /**
     * @test
     */
    public function shouldReplaceDestinationLinkPlaceholder()
    {
        $quote = new Quote(1, 2, 3, new DateTime());
        $site = new Site(2, 'https://example.com');
        $destination = new Destination(3, 'France', 'en', 'france');

        $this->mockQuoteRepo->method('getById')->willReturn($quote);
        $this->mockSiteRepo->method('getById')->willReturn($site);
        $this->mockDestinationRepo->method('getById')->willReturn($destination);

        $text = 'Link: [quote:destination_link]';
        $result = $this->replacer->replace($text, ['quote' => $quote]);

        $this->assertEquals('Link: https://example.com/France/quote/1', $result);
    }

    /**
     * @test
     */
    public function shouldCleanPlaceholdersWhenNoQuote()
    {
        $text = 'Test [quote:destination_name] [quote:summary] [quote:destination_link]';
        $result = $this->replacer->replace($text, []);

        $this->assertEquals('Test   ', $result);
    }

    /**
     * @test
     */
    public function shouldCleanPlaceholdersWhenInvalidQuote()
    {
        $text = 'Test [quote:destination_name]';
        $result = $this->replacer->replace($text, ['quote' => 'invalid']);

        $this->assertEquals('Test ', $result);
    }

    /**
     * @test
     */
    public function shouldHandleRepositoryException()
    {
        $quote = new Quote(1, 2, 3, new DateTime());

        $this->mockQuoteRepo->method('getById')->willThrowException(new Exception('Repository error'));

        $text = 'Test [quote:destination_name]';
        $result = $this->replacer->replace($text, ['quote' => $quote]);

        $this->assertEquals('Test ', $result);
    }

    /**
     * @test
     */
    public function shouldHandleDestinationLinkWithMissingData()
    {
        $quote = new Quote(1, 2, 3, new DateTime());

        $this->mockQuoteRepo->method('getById')->willReturn($quote);
        $this->mockSiteRepo->method('getById')->willReturn(null); // Site manquant
        $this->mockDestinationRepo->method('getById')->willReturn(new Destination(3, 'France', 'en', 'france'));

        $text = 'Link: [quote:destination_link]';
        $result = $this->replacer->replace($text, ['quote' => $quote]);

        $this->assertEquals('Link: ', $result);
    }

    /**
     * @test
     */
    public function shouldNotProcessWhenNoDestinationLinkPlaceholder()
    {
        $quote = new Quote(1, 2, 3, new DateTime());

        $this->mockQuoteRepo->method('getById')->willReturn($quote);
        $this->mockSiteRepo->method('getById')->willReturn(new Site(2, 'https://example.com'));
        $this->mockDestinationRepo->method('getById')->willReturn(new Destination(3, 'France', 'en', 'france'));

        $text = 'No destination link here';
        $result = $this->replacer->replace($text, ['quote' => $quote]);

        $this->assertEquals('No destination link here', $result);
    }

    /**
     * @test
     */
    public function shouldHandleMultiplePlaceholdersInSameText()
    {
        $quote = new Quote(1, 2, 3, new DateTime());
        $destination = new Destination(3, 'France', 'en', 'france');

        $this->mockQuoteRepo->method('getById')->willReturn($quote);
        $this->mockSiteRepo->method('getById')->willReturn(new Site(2, 'https://example.com'));
        $this->mockDestinationRepo->method('getById')->willReturn($destination);
        $this->mockRenderingService->method('renderText')->willReturn('Quote123');

        $text = 'Quote [quote:summary] pour [quote:destination_name]';
        $result = $this->replacer->replace($text, ['quote' => $quote]);

        $this->assertEquals('Quote Quote123 pour France', $result);
    }
}