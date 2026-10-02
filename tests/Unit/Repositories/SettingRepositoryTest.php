<?php

// tests/Unit/Repositories/SettingRepositoryTest.php

namespace Truvoicer\TfPerspectives\Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Models\Setting;
use Truvoicer\TfPerspectives\Repositories\SettingRepository;
use Truvoicer\TfPerspectives\Tests\TestCase;

class SettingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected SettingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SettingRepository;
    }

    #[Test]
    public function it_creates_default_settings_when_none_exist()
    {
        $settings = $this->repository->getSettings();

        $this->assertNotNull($settings);
        $this->assertInstanceOf(Setting::class, $settings);
        $this->assertEquals(5, $settings->comment_depth);
        $this->assertTrue($settings->require_login_to_comment);
        $this->assertNull($settings->push_notifications);
    }

    #[Test]
    public function it_returns_existing_settings_instead_of_creating_new()
    {
        $originalSettings = $this->repository->getSettings();

        $settings = $this->repository->getSettings();

        $this->assertEquals($originalSettings->id, $settings->id);
        $this->assertEquals(1, Setting::count());
    }

    #[Test]
    public function it_gets_specific_setting_value()
    {
        $this->repository->updateSettings([
            'app_name' => 'Test App',
            'comment_depth' => 10,
        ]);

        $appName = $this->repository->getSetting('app_name');
        $commentDepth = $this->repository->getSetting('comment_depth');

        $this->assertEquals('Test App', $appName);
        $this->assertEquals(10, $commentDepth);
    }

    #[Test]
    public function it_returns_default_value_for_missing_setting()
    {
        $value = $this->repository->getSetting('non_existent_key', 'default');

        $this->assertEquals('default', $value);
    }

    #[Test]
    public function it_updates_settings()
    {
        $settings = $this->repository->updateSettings([
            'app_name' => 'Updated App',
            'site_email' => 'admin@example.com',
            'comment_depth' => 15,
        ]);

        $this->assertEquals('Updated App', $settings->app_name);
        $this->assertEquals('admin@example.com', $settings->site_email);
        $this->assertEquals(15, $settings->comment_depth);
    }

    #[Test]
    public function it_ignores_non_fillable_fields_when_updating()
    {
        $settings = $this->repository->updateSettings([
            'app_name' => 'Test App',
            'non_fillable_field' => 'should be ignored',
            'id' => 999,
        ]);

        $this->assertEquals('Test App', $settings->app_name);
        $this->assertNotEquals(999, $settings->id);
    }

    #[Test]
    public function it_returns_settings_as_array()
    {
        $this->repository->updateSettings([
            'app_name' => 'Array Test',
            'site_copyright' => '© 2024',
        ]);

        $array = $this->repository->getSettingsArray();

        $this->assertIsArray($array);
        $this->assertEquals('Array Test', $array['app_name']);
        $this->assertEquals('© 2024', $array['site_copyright']);
    }

    #[Test]
    public function it_gets_only_filled_settings()
    {
        $this->repository->updateSettings([
            'app_name' => 'Filled App',
            'site_email' => null,
            'comment_depth' => 20,
        ]);

        $filled = $this->repository->getFilledSettings();

        $this->assertArrayHasKey('app_name', $filled);
        $this->assertArrayHasKey('comment_depth', $filled);
        $this->assertArrayNotHasKey('site_email', $filled);
        $this->assertEquals('Filled App', $filled['app_name']);
        $this->assertEquals(20, $filled['comment_depth']);
    }

    #[Test]
    public function it_checks_if_settings_exist()
    {
        $this->assertFalse($this->repository->settingsExist());

        $this->repository->getSettings();

        $this->assertTrue($this->repository->settingsExist());
    }

    #[Test]
    public function it_checks_if_setting_has_value()
    {
        $this->repository->updateSettings([
            'app_name' => 'Test App',
            'site_email' => null,
        ]);

        $this->assertTrue($this->repository->hasSetting('app_name'));
        $this->assertFalse($this->repository->hasSetting('site_email'));
        $this->assertFalse($this->repository->hasSetting('non_existent'));
    }

    #[Test]
    public function it_toggles_boolean_setting()
    {
        $this->repository->updateSettings(['require_login_to_comment' => true]);

        $newValue = $this->repository->toggleSetting('require_login_to_comment');

        $this->assertFalse($newValue);
        $this->assertFalse($this->repository->getSetting('require_login_to_comment'));

        $newValue = $this->repository->toggleSetting('require_login_to_comment');

        $this->assertTrue($newValue);
        $this->assertTrue($this->repository->getSetting('require_login_to_comment'));
    }

    #[Test]
    public function it_returns_null_when_toggling_non_boolean_setting()
    {
        $this->repository->updateSettings(['app_name' => 'Test']);

        $result = $this->repository->toggleSetting('app_name');

        $this->assertNull($result);
        $this->assertEquals('Test', $this->repository->getSetting('app_name'));
    }

    #[Test]
    public function it_updates_multiple_settings_with_allowed_keys()
    {
        $this->repository->updateMultipleSettings([
            'app_name' => 'Allowed App',
            'site_email' => 'allowed@example.com',
            'comment_depth' => 50,
            'non_allowed' => 'ignored',
        ], ['app_name', 'site_email']);

        $this->assertEquals('Allowed App', $this->repository->getSetting('app_name'));
        $this->assertEquals('allowed@example.com', $this->repository->getSetting('site_email'));
        $this->assertNotEquals(50, $this->repository->getSetting('comment_depth'));
    }

    #[Test]
    public function it_resets_settings_to_defaults()
    {
        $this->repository->updateSettings([
            'app_name' => 'Custom App',
            'comment_depth' => 100,
            'require_login_to_comment' => false,
        ]);

        $settings = $this->repository->resetToDefaults();

        $this->assertNull($settings->app_name);
        $this->assertEquals(5, $settings->comment_depth);
        $this->assertTrue($settings->require_login_to_comment);
    }

    #[Test]
    public function it_clears_settings()
    {
        $this->repository->getSettings();
        $this->assertTrue($this->repository->settingsExist());

        $result = $this->repository->clearSettings();

        $this->assertTrue($result);
        $this->assertFalse($this->repository->settingsExist());
    }

    #[Test]
    public function it_gets_settings_with_specific_keys()
    {
        $this->repository->updateSettings([
            'app_name' => 'Key Test',
            'site_email' => 'test@example.com',
            'comment_depth' => 25,
        ]);

        $subset = $this->repository->getSettingsOnly(['app_name', 'comment_depth']);

        $this->assertCount(2, $subset);
        $this->assertEquals('Key Test', $subset['app_name']);
        $this->assertEquals(25, $subset['comment_depth']);
        $this->assertArrayNotHasKey('site_email', $subset);
    }

    #[Test]
    public function it_updates_single_setting()
    {
        $this->repository->updateSetting('app_name', 'Single Update');

        $this->assertEquals('Single Update', $this->repository->getSetting('app_name'));
    }
}
