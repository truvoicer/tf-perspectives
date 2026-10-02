<?php

namespace Truvoicer\TfPerspectives\Tests\Unit\Services\Page\Response;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Block\Block;
use Truvoicer\TfPerspectives\Services\Page\Response\PageResponseService;
use Truvoicer\TfPerspectives\Tests\TestCase;

class PageResponseServiceTest extends TestCase
{
    protected PageResponseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PageResponseService;
    }

    #[Test]
    public function it_sets_and_gets_validation_data()
    {
        $validationData = [
            ['enum' => Block::HERO, 'validator' => Validator::make([], [])],
        ];

        $this->service->setValidationData($validationData);

        $this->assertEquals($validationData, $this->service->getValidationData());
    }

    #[Test]
    public function it_sets_and_gets_block_id()
    {
        $blockId = 'test-block-id-123';

        $this->service->setBlockId($blockId);

        $this->assertEquals($blockId, $this->service->getBlockId());
    }

    #[Test]
    public function it_finds_validation_data_by_enum()
    {
        $heroEnum = Block::HERO;
        $validator = Validator::make([], []);
        $validationData = [
            ['enum' => Block::FETCHER_LIST, 'validator' => Validator::make([], [])],
            ['enum' => $heroEnum, 'validator' => $validator],
        ];

        $this->service->setValidationData($validationData);

        $found = $this->service->findValidationDataByEnum($heroEnum);

        $this->assertIsArray($found);
        $this->assertEquals($heroEnum, $found['enum']);
        $this->assertEquals($validator, $found['validator']);
    }

    #[Test]
    public function it_returns_null_when_validation_data_not_found_by_enum()
    {
        $validationData = [
            ['enum' => Block::FETCHER_LIST, 'validator' => Validator::make([], [])],
        ];

        $this->service->setValidationData($validationData);

        $found = $this->service->findValidationDataByEnum(Block::HERO);

        $this->assertNull($found);
    }

    #[Test]
    public function it_finds_validation_data_by_enum_value()
    {
        $heroValue = Block::HERO->value;
        $validator = Validator::make([], []);
        $validationData = [
            ['enum' => Block::FETCHER_LIST, 'validator' => Validator::make([], [])],
            ['enum' => Block::HERO, 'validator' => $validator],
        ];

        $this->service->setValidationData($validationData);

        $found = $this->service->findValidationDataByEnumValue($heroValue);

        $this->assertIsArray($found);
        $this->assertEquals(Block::HERO, $found['enum']);
    }
}
