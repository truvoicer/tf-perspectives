<?php

namespace Truvoicer\TfPerspectives\Tests\Unit\Factories\Page\Block;

use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Block\Block;
use Truvoicer\TfPerspectives\Factories\Page\Block\PageBlockFactory;
use Truvoicer\TfPerspectives\Services\Page\Block\AccountSettingsBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\BlogListBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\CallToActionBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\ContactBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\EmailOptinBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\FaqBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\FeaturesBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\FetcherDetailPageBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\FetcherListPageBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\GalleryBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\HeadingBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\HeroBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\LogoCloudBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\MyApplicationsPageBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\PricingBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\ProfileBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\SavedItemListPageBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\StatsBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\TeamBlock;
use Truvoicer\TfPerspectives\Services\Page\Block\TestimonialsBlock;
use Truvoicer\TfPerspectives\Tests\TestCase;

class PageBlockFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_fetcher_detail_block()
    {
        $block = PageBlockFactory::make(Block::FETCHER_DETAIL->value);

        $this->assertInstanceOf(FetcherDetailPageBlock::class, $block);
    }

    #[Test]
    public function it_creates_fetcher_list_block()
    {
        $block = PageBlockFactory::make(Block::FETCHER_LIST->value);

        $this->assertInstanceOf(FetcherListPageBlock::class, $block);
    }

    #[Test]
    public function it_creates_saved_item_list_block()
    {
        $block = PageBlockFactory::make(Block::SAVED_ITEM_LIST->value);

        $this->assertInstanceOf(SavedItemListPageBlock::class, $block);
    }

    #[Test]
    public function it_creates_my_applications_block()
    {
        $block = PageBlockFactory::make(Block::MY_APPLICATIONS->value);

        $this->assertInstanceOf(MyApplicationsPageBlock::class, $block);
    }

    #[Test]
    public function it_creates_profile_block()
    {
        $block = PageBlockFactory::make(Block::PROFILE->value);

        $this->assertInstanceOf(ProfileBlock::class, $block);
    }

    #[Test]
    public function it_creates_account_settings_block()
    {
        $block = PageBlockFactory::make(Block::ACCOUNT_SETTINGS->value);

        $this->assertInstanceOf(AccountSettingsBlock::class, $block);
    }

    #[Test]
    public function it_creates_blog_list_block()
    {
        $block = PageBlockFactory::make(Block::BLOG_LIST->value);

        $this->assertInstanceOf(BlogListBlock::class, $block);
    }

    #[Test]
    public function it_creates_call_to_action_block()
    {
        $block = PageBlockFactory::make(Block::CALL_TO_ACTION->value);

        $this->assertInstanceOf(CallToActionBlock::class, $block);
    }

    #[Test]
    public function it_creates_contact_block()
    {
        $block = PageBlockFactory::make(Block::CONTACT->value);

        $this->assertInstanceOf(ContactBlock::class, $block);
    }

    #[Test]
    public function it_creates_email_optin_block()
    {
        $block = PageBlockFactory::make(Block::EMAIL_OPTIN->value);

        $this->assertInstanceOf(EmailOptinBlock::class, $block);
    }

    #[Test]
    public function it_creates_faq_block()
    {
        $block = PageBlockFactory::make(Block::FAQ->value);

        $this->assertInstanceOf(FaqBlock::class, $block);
    }

    #[Test]
    public function it_creates_features_block()
    {
        $block = PageBlockFactory::make(Block::FEATURES->value);

        $this->assertInstanceOf(FeaturesBlock::class, $block);
    }

    #[Test]
    public function it_creates_gallery_block()
    {
        $block = PageBlockFactory::make(Block::GALLERY->value);

        $this->assertInstanceOf(GalleryBlock::class, $block);
    }

    #[Test]
    public function it_creates_heading_block()
    {
        $block = PageBlockFactory::make(Block::HEADING->value);

        $this->assertInstanceOf(HeadingBlock::class, $block);
    }

    #[Test]
    public function it_creates_hero_block()
    {
        $block = PageBlockFactory::make(Block::HERO->value);

        $this->assertInstanceOf(HeroBlock::class, $block);
    }

    #[Test]
    public function it_creates_logo_cloud_block()
    {
        $block = PageBlockFactory::make(Block::LOGO_CLOUD->value);

        $this->assertInstanceOf(LogoCloudBlock::class, $block);
    }

    #[Test]
    public function it_creates_pricing_block()
    {
        $block = PageBlockFactory::make(Block::PRICING->value);

        $this->assertInstanceOf(PricingBlock::class, $block);
    }

    #[Test]
    public function it_creates_stats_block()
    {
        $block = PageBlockFactory::make(Block::STATS->value);

        $this->assertInstanceOf(StatsBlock::class, $block);
    }

    #[Test]
    public function it_creates_team_block()
    {
        $block = PageBlockFactory::make(Block::TEAM->value);

        $this->assertInstanceOf(TeamBlock::class, $block);
    }

    #[Test]
    public function it_creates_testimonials_block()
    {
        $block = PageBlockFactory::make(Block::TESTIMONIALS->value);

        $this->assertInstanceOf(TestimonialsBlock::class, $block);
    }

    #[Test]
    public function it_returns_null_for_invalid_block_type()
    {
        $block = PageBlockFactory::make('invalid_block_type');

        $this->assertNull($block);
    }

    #[Test]
    public function it_passes_data_to_block()
    {
        $data = ['test_key' => 'test_value'];
        $block = PageBlockFactory::make(Block::HERO->value, $data);

        $this->assertInstanceOf(HeroBlock::class, $block);
        // Assuming HeroBlock has a getData method or similar
        $this->assertEquals($data, $block->getData());
    }
}
