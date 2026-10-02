<?php

namespace Truvoicer\TfPerspectives\Tests\Unit\Services\Page;

use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Block\Block;
use Truvoicer\TfPerspectives\Enums\File\FileType;
use Truvoicer\TfPerspectives\Services\Page\PageService;
use Truvoicer\TfPerspectives\Services\Page\Response\PageResponseService;
use Truvoicer\TfPerspectives\Tests\TestCase;

class PageServiceTest extends TestCase
{
    protected PageService $pageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageService = app(PageService::class);
    }

    #[Test]
    public function it_returns_file_types_array()
    {
        $fileTypes = $this->pageService->getFileTypes();
        $this->assertNotEmpty($fileTypes);
        $this->assertContainsOnlyInstancesOf(\BackedEnum::class, $fileTypes);

        // Test that all FileType enum cases are returned
        $expectedCount = count(FileType::cases());
        $this->assertCount($expectedCount, $fileTypes);

        // Test that each item is a FileType enum instance
        foreach ($fileTypes as $fileType) {
            $this->assertInstanceOf(FileType::class, $fileType);
        }
    }

    #[Test]
    public function it_returns_block_cases_array()
    {
        $blockCases = $this->pageService->getBlockCases();

        $this->assertIsArray($blockCases);
        $this->assertContains(Block::HERO, $blockCases);
        $this->assertContains(Block::FETCHER_LIST, $blockCases);
        $this->assertContains(Block::TESTIMONIALS, $blockCases);
    }

    #[Test]
    public function it_returns_page_response_service_instance()
    {
        $responseService = $this->pageService->getPageResponseService();

        $this->assertInstanceOf(PageResponseService::class, $responseService);
    }

    #[Test]
    public function it_creates_instance_via_make_method()
    {
        $service = PageService::make();

        $this->assertInstanceOf(PageService::class, $service);
        $this->assertNotSame($this->pageService, $service);
    }
}
