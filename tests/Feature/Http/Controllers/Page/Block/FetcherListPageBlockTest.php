<?php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Controllers\Page\Block;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Fetcher\FetcherListTplStatus;
use Truvoicer\TfPerspectives\Models\FetcherListTpl;
use Truvoicer\TfPerspectives\Models\FetcherListTplServiceDefault;
use Truvoicer\TfPerspectives\Models\FetcherServiceDefault;
use Truvoicer\TfPerspectives\Models\Page;
use Truvoicer\TfPerspectives\Models\PageColumn;
use Truvoicer\TfPerspectives\Models\PageColumnBlock;
use Truvoicer\TfPerspectives\Models\PageRow;
use Truvoicer\TfPerspectives\Services\Page\Block\FetcherListPageBlock;
use Truvoicer\TfPerspectives\Tests\TestCase;

class FetcherListPageBlockTest extends TestCase
{
    use RefreshDatabase;

    protected FetcherListPageBlock $block;

    protected Page $page;

    protected PageRow $pageRow;

    protected PageColumn $pageColumn;

    protected PageColumnBlock $pageColumnBlock;

    protected $mockUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock user that extends the package's User model

        $this->block = app(FetcherListPageBlock::class);

        // Create the page first
        $this->page = Page::create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'permalink' => '/test-page',
            'is_active' => true,
            'content' => 'Test content',
        ]);

        // Create a row for the page
        $this->pageRow = PageRow::create([
            'page_id' => $this->page->id,
            'position' => 1,
            'class_name' => 'test-row',
        ]);

        // Create a column for the row
        $this->pageColumn = PageColumn::create([
            'page_row_id' => $this->pageRow->id,
            'position' => 1,
            'class_name' => 'test-column',
        ]);

        // Now create the block with valid foreign keys
        $this->pageColumnBlock = PageColumnBlock::create([
            'uid' => 'test-block-uid',
            'page_column_id' => $this->pageColumn->id,
            'block' => 'fetcher_list',
            'data' => [
                'service' => 'test-service',
                'api_fetch_type' => 'database',
                'page_size' => 10,
            ],
            'position' => 1,
        ]);

        $fetcherListTpl = FetcherListTpl::create([
            'name' => 'test-service template',
            'status' => FetcherListTplStatus::ACTIVE,
        ]);
        FetcherServiceDefault::create([
            'service' => 'test-service',
            'external_url_item_attribute' => 'url',
            'title_item_attribute' => 'title',
        ]);
        FetcherListTplServiceDefault::create([
            'fetcher_list_tpl_id' => $fetcherListTpl->id,
            'service' => 'test-service',
        ]);
    }

    #[Test]
    public function it_parses_block_data()
    {
        $parsedData = $this->block->parseData($this->pageColumnBlock);

        $this->assertIsArray($parsedData);
        $this->assertArrayHasKey('service', $parsedData);
        $this->assertEquals('test-service', $parsedData['service']);
        $this->assertArrayHasKey('has_template', $parsedData);
    }

    #[Test]
    public function it_returns_inertia_dependencies()
    {
        $dependencies = $this->block->inertiaDependencies($this->pageColumnBlock, $this->page);

        $this->assertIsArray($dependencies);
        $this->assertArrayHasKey('fetcher', $dependencies);
    }

    #[Test]
    public function it_handles_block_without_template()
    {
        $blockWithoutTemplate = PageColumnBlock::create([
            'uid' => 'test-block-no-template',
            'page_column_id' => $this->pageColumn->id,
            'block' => 'fetcher_list',
            'data' => [
                'service' => 'test-services',
                'api_fetch_type' => 'database',
                'page_size' => 10,
                'fetcher_list_tpl_id' => null,
            ],
            'position' => 2,
        ]);

        $parsedData = $this->block->parseData($blockWithoutTemplate);

        $this->assertIsArray($parsedData);
        $this->assertArrayHasKey('has_template', $parsedData);
        $this->assertFalse($parsedData['has_template']);
    }

    #[Test]
    public function it_handles_empty_block_data()
    {
        $emptyBlock = PageColumnBlock::create([
            'uid' => 'test-block-empty',
            'page_column_id' => $this->pageColumn->id,
            'block' => 'fetcher_list',
            'data' => null,
            'position' => 3,
        ]);

        $parsedData = $this->block->parseData($emptyBlock);

        $this->assertIsArray($parsedData);
        $this->assertArrayHasKey('has_template', $parsedData);
    }
}
