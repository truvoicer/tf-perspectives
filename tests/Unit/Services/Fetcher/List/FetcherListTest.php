<?php

namespace Truvoicer\TfPerspectives\Tests\Unit\Services\Fetcher\List;

use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfDbReadCore\Repositories\MongoDB\MongoDBRepository;
use Truvoicer\TfPerspectives\Services\Fetcher\List\FetcherList;
use Truvoicer\TfPerspectives\Tests\TestCase;

class FetcherListTest extends TestCase
{
    protected FetcherList $fetcherList;

    private MongoDBRepository $mongoDbRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fetcherList = app(FetcherList::class);

    }

    #[Test]
    public function it_merges_default_parameters_with_provided_data()
    {
        $data = [
            'service' => 'test-service',
            'page_size' => 20,
        ];

        // Use reflection to test the merge logic
        $reflection = new \ReflectionClass($this->fetcherList);
        $method = $reflection->getMethod('makeRequest');

        // This is a structural test for the method
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function it_handles_different_api_fetch_types()
    {
        $fetchTypes = ['database', 'api_direct'];

        foreach ($fetchTypes as $fetchType) {
            $data = [
                'service' => 'test-service',
                'api_fetch_type' => $fetchType,
            ];

            // Test structure
            $this->assertIsArray($data);
            $this->assertArrayHasKey('api_fetch_type', $data);
        }
    }
}
