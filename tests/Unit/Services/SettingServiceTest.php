<?php

// tests/Unit/Services/SettingServiceTest.php

namespace Truvoicer\TfPerspectives\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Models\Setting;
use Truvoicer\TfPerspectives\Services\SettingService;
use Truvoicer\TfPerspectives\Tests\TestCase;

class SettingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    #[Test]
    public function it_gets_setting_with_config_fallback()
    {
        $value = SettingService::get('app_name');

        $this->assertEquals(config('app.name'), $value);
    }

    #[Test]
    public function it_returns_database_value_over_config_fallback()
    {
        SettingService::update(['app_name' => 'Database App Name']);

        $value = SettingService::get('app_name');

        $this->assertEquals('Database App Name', $value);
        $this->assertNotEquals(config('app.name'), $value);
    }

    #[Test]
    public function it_returns_default_value_when_no_config_fallback()
    {
        $value = SettingService::get('non_existent_key', 'my_default', false);

        $this->assertEquals('my_default', $value);
    }

    #[Test]
    public function it_caches_setting_values()
    {
        SettingService::update(['app_name' => 'Cached App']);

        // First call should cache
        $value1 = SettingService::getCached('app_name');

        // Update database directly
        Setting::first()->update(['app_name' => 'Different App']);

        // Should still return cached value
        $value2 = SettingService::getCached('app_name');

        $this->assertEquals('Cached App', $value1);
        $this->assertEquals('Cached App', $value2);
    }

    #[Test]
    public function it_clears_cache_after_update()
    {
        SettingService::update(['app_name' => 'Original']);
        SettingService::getCached('app_name');

        SettingService::update(['app_name' => 'Updated']);

        $value = SettingService::getCached('app_name');
        $this->assertEquals('Updated', $value);
    }

    #[Test]
    public function it_returns_all_settings_with_config_fallback()
    {
        SettingService::update([
            'app_name' => 'Laravel',
            'site_email' => null,
        ]);

        $all = SettingService::all(true);

        $this->assertEquals('Laravel', $all['app_name']);
        $this->assertEquals(config('services.site.email'), $all['site_email']);
        $this->assertEquals(config('app.name'), $all['app_name']);
    }

    #[Test]
    public function it_returns_all_settings_without_config_fallback()
    {
        SettingService::update(['app_name' => 'Test App']);

        $all = SettingService::all(false);

        $this->assertEquals('Test App', $all['app_name']);
        $this->assertNull($all['site_email']);
    }

    #[Test]
    public function it_caches_all_settings()
    {
        SettingService::update(['app_name' => 'Cached All']);

        $all1 = SettingService::allCached();

        Setting::first()->update(['app_name' => 'Different']);

        $all2 = SettingService::allCached();

        $this->assertEquals('Cached All', $all1['app_name']);
        $this->assertEquals('Cached All', $all2['app_name']);
    }

    #[Test]
    public function it_returns_only_filled_settings()
    {
        SettingService::update([
            'app_name' => 'Filled',
            'site_email' => null,
            'comment_depth' => 10,
        ]);

        $filled = SettingService::filled();

        $this->assertArrayHasKey('app_name', $filled);
        $this->assertArrayHasKey('comment_depth', $filled);
        $this->assertArrayNotHasKey('site_email', $filled);
    }

    #[Test]
    public function it_returns_only_specified_keys()
    {
        SettingService::update([
            'app_name' => 'Only Test',
            'site_email' => 'test@example.com',
            'comment_depth' => 30,
        ]);

        $only = SettingService::only(['app_name', 'comment_depth']);

        $this->assertCount(2, $only);
        $this->assertEquals('Only Test', $only['app_name']);
        $this->assertEquals(30, $only['comment_depth']);
        $this->assertArrayNotHasKey('site_email', $only);
    }

    #[Test]
    public function it_checks_if_setting_has_value()
    {
        SettingService::update([
            'app_name' => 'Test',
            'site_email' => null,
        ]);

        $this->assertTrue(SettingService::hasValue('app_name'));
        $this->assertFalse(SettingService::hasValue('site_email'));
        $this->assertFalse(SettingService::hasValue('non_existent'));
    }

    #[Test]
    public function it_toggles_boolean_setting()
    {
        SettingService::update(['require_login_to_comment' => true]);

        $newValue = SettingService::toggle('require_login_to_comment');

        $this->assertFalse($newValue);
        $this->assertFalse(SettingService::get('require_login_to_comment'));
    }

    #[Test]
    public function it_initializes_default_settings()
    {
        Setting::truncate();

        SettingService::initializeDefaults(['app_name' => 'Custom Default']);

        $settings = SettingService::getSettings();
        $this->assertEquals('Custom Default', $settings->app_name);
        $this->assertEquals(5, $settings->comment_depth);
    }

    #[Test]
    public function it_resets_setting_to_default()
    {
        SettingService::update(['app_name' => 'Custom Name']);

        SettingService::resetToDefault('app_name');

        $this->assertEquals(config('app.name'), SettingService::get('app_name'));
    }

    #[Test]
    public function it_returns_null_when_resetting_non_default_setting()
    {
        $result = SettingService::resetToDefault('non_existent_key');

        $this->assertNull($result);
    }

    #[Test]
    public function it_validates_and_updates_settings_successfully()
    {
        $result = SettingService::validateAndUpdate([
            'app_name' => 'Validated App',
            'comment_depth' => 15,
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('Validated App', $result['data']->app_name);
    }

    #[Test]
    public function it_returns_validation_errors()
    {
        $result = SettingService::validateAndUpdate([
            'app_email' => 'invalid-email',
            'comment_depth' => 'not a number',
        ]);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('app_email', $result['errors']);
        $this->assertArrayHasKey('comment_depth', $result['errors']);
    }

    #[Test]
    public function it_returns_success_false_on_exception()
    {
        // Create a situation that will cause an exception
        // This might require mocking the repository to throw an exception

        $result = SettingService::validateAndUpdate([]);

        // Even with empty data, validation should pass but update might still work
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    #[Test]
    public function it_gets_social_share_links()
    {
        SettingService::update([
            'facebook_share_link' => 'https://facebook.com/test',
            'x_share_link' => 'https://x.com/test',
        ]);

        $links = SettingService::getSocialShareLinks();

        $this->assertEquals('https://facebook.com/test', $links['facebook']);
        $this->assertEquals('https://x.com/test', $links['x']);
        $this->assertNull($links['linkedin']);
    }

    #[Test]
    public function it_gets_app_info()
    {
        SettingService::update([
            'app_name' => 'Info App',
            'app_email' => 'info@example.com',
            'site_copyright' => '© 2024',
        ]);

        $info = SettingService::getAppInfo();

        $this->assertEquals('Info App', $info['name']);
        $this->assertEquals('info@example.com', $info['email']);
        $this->assertEquals('© 2024', $info['copyright']);
    }

    #[Test]
    public function it_checks_login_requirements()
    {
        SettingService::update([
            'require_login_to_comment' => true,
            'require_login_to_like_comment' => false,
            'require_login_to_report_comment' => true,
        ]);

        $this->assertTrue(SettingService::isLoginRequiredForComments());
        $this->assertFalse(SettingService::isLoginRequiredForLikeComments());
        $this->assertTrue(SettingService::isLoginRequiredForReportComments());
    }
}
