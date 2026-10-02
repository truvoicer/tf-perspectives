<?php

// tests/Unit/Resources/Setting/SettingResourceTest.php

namespace Truvoicer\TfPerspectives\Tests\Unit\Resources\Setting;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Resources\MissingValue;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\User\Theme;
use Truvoicer\TfPerspectives\Http\Resources\Setting\SettingResource;
use Truvoicer\TfPerspectives\Models\Setting;
use Truvoicer\TfPerspectives\Tests\TestCase;

class SettingResourceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_transforms_setting_to_array()
    {
        $setting = Setting::create([
            'app_name' => 'Test App',
            'site_email' => 'test@example.com',
            'comment_depth' => 10,
            'require_login_to_comment' => true,
            'default_theme' => Theme::DARK,
        ]);

        $resource = new SettingResource($setting);
        $array = $resource->toArray(request());

        $this->assertEquals('Test App', $array['app_name']);
        $this->assertEquals('test@example.com', $array['site_email']);
        $this->assertEquals(10, $array['comment_depth']);
        $this->assertTrue($array['require_login_to_comment']);
        $this->assertEquals(Theme::DARK, $array['default_theme']);
    }

    #[Test]
    public function it_masks_sensitive_data()
    {
        $setting = Setting::create([
            'ga_api_secret' => 'abcdefghijklmnop123456',
            'facebook_app_secret' => 'secret123456789',
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $array = $resource->toArray(request());

        // Should mask the secret (first 4 and last 4 characters visible)
        $this->assertStringStartsWith('abcd', $array['ga_api_secret']);
        $this->assertStringEndsWith('3456', $array['ga_api_secret']);
        $this->assertStringContainsString('****', $array['ga_api_secret']);

        $this->assertStringStartsWith('secr', $array['facebook_app_secret']);
        $this->assertStringEndsWith('6789', $array['facebook_app_secret']);
        $this->assertStringContainsString('****', $array['facebook_app_secret']);
    }

    #[Test]
    public function it_returns_null_for_null_sensitive_data()
    {
        $setting = Setting::create([
            'ga_api_secret' => null,
            'facebook_app_secret' => null,
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $array = $resource->toArray(request());

        $this->assertNull($array['ga_api_secret']);
        $this->assertNull($array['facebook_app_secret']);
    }

    #[Test]
    public function it_returns_analytics_config_for_gtm()
    {
        $setting = Setting::create([
            'analytics_driver' => 'gtm',
            'gtm_id' => 'GTM-ABCD123',
            'gtm_server_endpoint' => 'https://gtm.example.com',
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $array = $resource->toArray(request());

        $this->assertArrayHasKey('analytics_config', $array);
        $this->assertEquals('gtm', $array['analytics_config']['driver']);
        $this->assertEquals('GTM-ABCD123', $array['analytics_config']['gtm_id']);
        $this->assertEquals('https://gtm.example.com', $array['analytics_config']['server_endpoint']);
    }

    #[Test]
    public function it_returns_analytics_config_for_ga()
    {
        $setting = Setting::create([
            'analytics_driver' => 'ga',
            'ga_measurement_id' => 'G-ABCD123',
            'ga_api_secret' => 'secret_key_12345',
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $array = $resource->toArray(request());

        $this->assertArrayHasKey('analytics_config', $array);
        $this->assertEquals('ga', $array['analytics_config']['driver']);
        $this->assertEquals('G-ABCD123', $array['analytics_config']['measurement_id']);
        $this->assertArrayHasKey('api_secret', $array['analytics_config']);
    }

    #[Test]
    public function it_returns_null_analytics_config_for_none()
    {
        $setting = Setting::create([
            'analytics_driver' => 'none',
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $array = $resource->toArray(request());

        $this->assertNull($array['analytics_config']);
    }

    #[Test]
    public function it_returns_social_links_only_when_present()
    {
        $setting = Setting::create([
            'facebook_share_link' => 'https://facebook.com/test',
            'x_share_link' => 'https://x.com/test',
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $array = $resource->toArray(request());

        $this->assertArrayHasKey('social_links', $array);
        $this->assertCount(2, $array['social_links']);
        $this->assertEquals('https://facebook.com/test', $array['social_links']['facebook']);
        $this->assertEquals('https://x.com/test', $array['social_links']['x']);
    }

    #[Test]
    public function it_does_not_return_social_links_when_empty()
    {
        $setting = Setting::create([
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $array = $resource->toArray(request());

        // The key may exist but be a MissingValue instance
        // So we should check if it's actually present and has a value
        $hasSocialLinks = array_key_exists('social_links', $array) &&
            ! ($array['social_links'] instanceof MissingValue);

        $this->assertFalse($hasSocialLinks);
        // Or simply check that if the key exists, it's a MissingValue
        if (array_key_exists('social_links', $array)) {
            $this->assertInstanceOf(MissingValue::class, $array['social_links']);
        }
    }

    #[Test]
    public function it_returns_public_array_without_sensitive_data()
    {
        $setting = Setting::create([
            'app_name' => 'Public App',
            'ga_api_secret' => 'secret_value',
            'facebook_app_secret' => 'app_secret_value',
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $publicArray = $resource->toPublicArray(request());

        $this->assertEquals('Public App', $publicArray['app_name']);
        $this->assertArrayNotHasKey('ga_api_secret', $publicArray);
        $this->assertArrayNotHasKey('facebook_app_secret', $publicArray);
    }

    #[Test]
    public function it_returns_public_analytics_config_without_secrets()
    {
        $setting = Setting::create([
            'analytics_driver' => 'ga',
            'ga_measurement_id' => 'G-ABCD123',
            'ga_api_secret' => 'secret_should_be_excluded',
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $publicArray = $resource->toPublicArray(request());

        $this->assertArrayHasKey('analytics_config', $publicArray);
        $this->assertEquals('ga', $publicArray['analytics_config']['driver']);
        $this->assertEquals('G-ABCD123', $publicArray['analytics_config']['measurement_id']);
        $this->assertArrayNotHasKey('api_secret', $publicArray['analytics_config']);
    }

    #[Test]
    public function it_returns_meta_data()
    {
        $setting = Setting::create([
            'app_name' => 'Meta Test',
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $response = $resource->with(request());

        $this->assertArrayHasKey('meta', $response);
        $this->assertEquals('1.0', $response['meta']['settings_version']);
        $this->assertArrayHasKey('last_updated', $response['meta']);
    }

    #[Test]
    public function it_masks_short_secrets_completely()
    {
        $setting = Setting::create([
            'ga_api_secret' => 'short',
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $array = $resource->toArray(request());

        $this->assertEquals('*****', $array['ga_api_secret']);
    }

    #[Test]
    public function it_handles_boolean_conversion_correctly()
    {
        $setting = Setting::create([
            'require_login_to_comment' => true,
            'enable_edit_comment_window' => false,
            'push_notifications' => true,
            'default_theme' => Theme::SYSTEM,
        ]);

        $resource = new SettingResource($setting);
        $array = $resource->toArray(request());

        $this->assertTrue($array['require_login_to_comment']);
        $this->assertFalse($array['enable_edit_comment_window']);
        $this->assertTrue($array['push_notifications']);
    }
}
