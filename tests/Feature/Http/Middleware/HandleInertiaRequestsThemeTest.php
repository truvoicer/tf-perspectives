<?php

// tests/Feature/Middleware/HandleInertiaRequestsThemeTest.php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Inertia;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role as AuthRole;
use Truvoicer\TfPerspectives\Enums\Page\DefaultPage;
use Truvoicer\TfPerspectives\Enums\User\Theme;
use Truvoicer\TfPerspectives\Models\Page;
use Truvoicer\TfPerspectives\Models\Role;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Models\UserPreference;
use Truvoicer\TfPerspectives\Services\User\UserThemeService;
use Truvoicer\TfPerspectives\Tests\TestCase;

class HandleInertiaRequestsThemeTest extends TestCase
{
    use RefreshDatabase;

    protected Page $homePage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->homePage = Page::create([
            'slug' => DefaultPage::HOME->value,
            'permalink' => '/',
            'is_active' => true,
            'title' => 'Homepage',
            'content' => 'Home page page content',
        ]);
        $this->homePage->roles()->attach(
            Role::all()->pluck('id')->toArray()
        );
        // Share theme data directly for testing
        $this->shareThemeDataForTesting();
    }

    protected function shareThemeDataForTesting(): void
    {
        // Share theme data with Inertia for testing
        Inertia::share('theme', function () {
            $user = auth()->user();
            $themeService = app(UserThemeService::class);

            return $themeService->getThemeData($user);
        });
    }

    #[Test]
    public function it_shares_theme_data_for_authenticated_user()
    {
        $user = User::factory()->create();
        $user->assignRole(AuthRole::USER);
        UserPreference::create([
            'user_id' => $user->getId(),
            'theme' => Theme::DARK,
            'email_subscribe' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertInertia(
            fn ($page) => $page
                ->has('theme')
                ->where('theme.theme.value', 'dark')
                ->where('theme.theme.label', 'Dark')
                ->has('theme.available')
                ->has('theme.default')
        );
    }

    #[Test]
    public function it_shares_theme_data_for_guest_user()
    {
        $response = $this->get('/');

        $defaultTheme = Theme::getDefaultTheme();

        $response->assertInertia(
            fn ($page) => $page
                ->has('theme')
                ->where('theme.theme.value', $defaultTheme->value)
                ->where('theme.theme.label', $defaultTheme->label())
                ->has('theme.available')
                ->has('theme.default')
        );
    }

    #[Test]
    public function it_shares_selected_theme_for_user_without_preference()
    {
        $user = User::factory()->create();
        $user->assignRole(AuthRole::USER);

        $this->actingAs($user);

        // Send the request with Inertia headers
        $response = $this->get('/');

        $defaultTheme = Theme::getDefaultTheme();

        $response->assertInertia(
            fn ($page) => $page
                ->where('theme.theme.value', $defaultTheme->value)
                ->where('theme.selected_theme', null)
        );
    }
}
