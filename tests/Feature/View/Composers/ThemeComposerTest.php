<?php

// tests/Feature/View/Composers/ThemeComposerTest.php

namespace Truvoicer\TfPerspectives\Tests\Feature\View\Composers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Models\UserPreference;
use Truvoicer\TfPerspectives\Services\User\UserThemeService;
use Truvoicer\TfPerspectives\Tests\TestCase;
use Truvoicer\TfPerspectives\View\Composers\ThemeComposer;

class ThemeComposerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_composes_theme_data_for_authenticated_user()
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->getId(),
            'theme' => 'dark',
        ]);

        // Set the authenticated user on the request
        $this->actingAs($user);

        // Create a request and set it on the container
        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        app()->instance('request', $request);

        $view = View::make('app');
        $composer = app(ThemeComposer::class);
        $composer->compose($view);

        $themeData = $view->getData()['themeData'];

        $this->assertIsArray($themeData);
        $this->assertEquals('dark', $themeData['theme']['value']);
        $this->assertArrayHasKey('available', $themeData);
        $this->assertArrayHasKey('default', $themeData);
    }

    #[Test]
    public function it_composes_fallback_theme_data_when_exception_occurs()
    {
        // Create a mock that will throw an exception
        $mockService = \Mockery::mock(UserThemeService::class);
        $mockService->shouldReceive('getThemeData')->andThrow(new \Exception('Test exception'));

        $composer = new ThemeComposer($mockService);
        $view = View::make('app');

        $composer->compose($view);

        $themeData = $view->getData()['themeData'];

        $this->assertEquals('light', $themeData['theme']['value']);
        $this->assertEquals('system', $themeData['default']['value']);
        $this->assertCount(3, $themeData['available']);
    }

    #[Test]
    public function it_composes_theme_data_for_guest_user()
    {
        $view = View::make('app');
        $composer = app(ThemeComposer::class);
        $composer->compose($view);

        $themeData = $view->getData()['themeData'];

        $this->assertIsArray($themeData);
        $this->assertArrayHasKey('theme', $themeData);
        $this->assertArrayHasKey('available', $themeData);
        $this->assertArrayHasKey('default', $themeData);
    }
}
