<?php

// tests/Unit/Services/User/UserThemeServiceTest.php

namespace Truvoicer\TfPerspectives\Tests\Unit\Services\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\User\Theme;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Models\UserPreference;
use Truvoicer\TfPerspectives\Services\User\UserThemeService;
use Truvoicer\TfPerspectives\Tests\TestCase;

class UserThemeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserThemeService $userThemeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userThemeService = new UserThemeService;
    }

    #[Test]
    public function it_returns_default_theme_when_user_is_null_and_fallback_enabled()
    {
        $theme = $this->userThemeService->getUserTheme(null, true);

        $this->assertNotNull($theme);
        $this->assertInstanceOf(Theme::class, $theme);
        $this->assertEquals(Theme::getDefaultTheme(), $theme);
    }

    #[Test]
    public function it_returns_null_when_user_is_null_and_fallback_disabled()
    {
        $theme = $this->userThemeService->getUserTheme(null, false);

        $this->assertNull($theme);
    }

    #[Test]
    public function it_returns_user_preference_theme_when_exists()
    {
        $user = User::factory()->create();
        $expectedTheme = Theme::DARK;

        UserPreference::create([
            'user_id' => $user->getId(),
            'theme' => $expectedTheme,
            'email_subscribe' => true,
        ]);

        $theme = $this->userThemeService->getUserTheme($user);

        $this->assertEquals($expectedTheme, $theme);
    }

    #[Test]
    public function it_returns_default_theme_when_user_preference_does_not_exist()
    {
        $user = User::factory()->create();

        $theme = $this->userThemeService->getUserTheme($user);

        $this->assertEquals(Theme::getDefaultTheme(), $theme);
    }

    #[Test]
    public function it_returns_null_when_user_preference_does_not_exist_and_fallback_disabled()
    {
        $user = User::factory()->create();

        $theme = $this->userThemeService->getUserTheme($user, false);

        $this->assertNull($theme);
    }

    #[Test]
    public function it_returns_user_preference_theme_even_when_fallback_disabled()
    {
        $user = User::factory()->create();
        $expectedTheme = Theme::LIGHT;

        UserPreference::create([
            'user_id' => $user->getId(),
            'theme' => $expectedTheme,
            'email_subscribe' => true,
        ]);

        $theme = $this->userThemeService->getUserTheme($user, false);

        $this->assertEquals($expectedTheme, $theme);
    }

    #[Test]
    public function it_returns_correct_theme_data_structure_for_authenticated_user()
    {
        $user = User::factory()->create();
        $userTheme = Theme::DARK;

        UserPreference::create([
            'user_id' => $user->getId(),
            'theme' => $userTheme,
            'email_subscribe' => true,
        ]);

        $themeData = $this->userThemeService->getThemeData($user);

        $this->assertIsArray($themeData);
        $this->assertArrayHasKey('theme', $themeData);
        $this->assertArrayHasKey('selected_theme', $themeData);
        $this->assertArrayHasKey('default', $themeData);
        $this->assertArrayHasKey('available', $themeData);

        $this->assertIsArray($themeData['theme']);
        $this->assertArrayHasKey('value', $themeData['theme']);
        $this->assertArrayHasKey('label', $themeData['theme']);

        $this->assertEquals($userTheme->value, $themeData['theme']['value']);
        $this->assertEquals($userTheme->label(), $themeData['theme']['label']);
    }

    #[Test]
    public function it_returns_correct_theme_data_structure_for_guest_user()
    {
        $themeData = $this->userThemeService->getThemeData(null);

        $this->assertIsArray($themeData);
        $this->assertArrayHasKey('theme', $themeData);
        $this->assertArrayHasKey('selected_theme', $themeData);
        $this->assertArrayHasKey('default', $themeData);
        $this->assertArrayHasKey('available', $themeData);

        $defaultTheme = Theme::getDefaultTheme();

        $this->assertEquals($defaultTheme->value, $themeData['theme']['value']);
        $this->assertEquals($defaultTheme->label(), $themeData['theme']['label']);
    }

    #[Test]
    public function it_handles_system_theme_correctly()
    {
        $user = User::factory()->create();
        $userTheme = Theme::SYSTEM;

        UserPreference::create([
            'user_id' => $user->getId(),
            'theme' => $userTheme,
            'email_subscribe' => true,
        ]);

        $themeData = $this->userThemeService->getThemeData($user);

        $this->assertEquals('system', $themeData['theme']['value']);
        $this->assertEquals('System', $themeData['theme']['label']);
    }

    #[Test]
    public function it_returns_available_themes_list()
    {
        $themeData = $this->userThemeService->getThemeData(null);

        $this->assertIsArray($themeData['available']);
        $this->assertCount(3, $themeData['available']);

        $expectedThemes = ['light', 'dark', 'system'];
        foreach ($themeData['available'] as $index => $theme) {
            $this->assertArrayHasKey('value', $theme);
            $this->assertArrayHasKey('label', $theme);
            $this->assertContains($theme['value'], $expectedThemes);
        }
    }

    #[Test]
    public function it_distinguishes_between_theme_and_selected_theme()
    {
        $user = User::factory()->create();
        $userTheme = Theme::SYSTEM;

        UserPreference::create([
            'user_id' => $user->getId(),
            'theme' => $userTheme,
            'email_subscribe' => true,
        ]);

        $themeData = $this->userThemeService->getThemeData($user);

        // theme should be SYSTEM (user preference)
        $this->assertEquals('system', $themeData['theme']['value']);
        $this->assertEquals('system', $themeData['selected_theme']['value']);

    }

    #[Test]
    public function it_returns_default_theme_in_theme_data_when_no_user_preference()
    {
        $user = User::factory()->create();
        $themeData = $this->userThemeService->getThemeData($user);

        $defaultTheme = Theme::getDefaultTheme();
        $this->assertEquals($defaultTheme->value, $themeData['theme']['value']);
    }
}
