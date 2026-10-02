<?php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Controllers\Page;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role;
use Truvoicer\TfPerspectives\Models\Page;
use Truvoicer\TfPerspectives\Models\Role as ModelsRole;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Page $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole(Role::USER);

        $this->page = Page::create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'permalink' => '/test-page',
            'is_active' => true,
            'content' => 'Test content',
        ]);
        $this->page->roles()->attach(
            ModelsRole::all()->pluck('id')->toArray()
        );
    }

    #[Test]
    public function it_displays_active_page()
    {
        $this->actingAs($this->user);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals('page/page', $data['component']);
        $this->assertEquals('/test-page', $data['url']);
    }

    #[Test]
    public function it_returns_404_for_inactive_page()
    {
        $this->page->update(['is_active' => false]);
        $this->actingAs($this->user);

        $response = $this->get('/test-page');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_for_non_existent_page()
    {
        $this->actingAs($this->user);

        $response = $this->get('/non-existent-page');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_handles_root_permalink()
    {
        $homePage = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'permalink' => '/',
            'is_active' => true,
            'content' => 'Home content',
        ]);
        $homePage->roles()->attach(
            ModelsRole::all()->pluck('id')->toArray()
        );

        $this->actingAs($this->user);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Inertia', 'true');

        $data = $response->json();

        // Assert Inertia response structure
        $this->assertArrayHasKey('component', $data);
        $this->assertArrayHasKey('props', $data);
        $this->assertArrayHasKey('url', $data);

        // Assert component name
        $this->assertEquals('page/page', $data['component']);

        // Assert URL is correct
        $this->assertEquals('/', $data['url']);

        // Assert page data is present in props
        $this->assertArrayHasKey('page', $data['props']);
        $this->assertArrayHasKey('data', $data['props']['page']);

        // Assert page data matches the created home page
        $this->assertEquals($homePage->id, $data['props']['page']['data']['id']);
        $this->assertEquals('Home', $data['props']['page']['data']['title']);
        $this->assertEquals('/', $data['props']['page']['data']['permalink']);
        $this->assertTrue($data['props']['page']['data']['is_active']);
    }

    // Alternative using assertInertia helper
    #[Test]
    public function it_handles_root_permalink_with_assert_inertia()
    {
        $homePage = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'permalink' => '/',
            'is_active' => true,
            'content' => 'Home content',
        ]);
        $homePage->roles()->attach(
            ModelsRole::all()->pluck('id')->toArray()
        );

        $this->actingAs($this->user);

        $response = $this->get('/');

        $response->assertInertia(
            fn ($page) => $page
                ->has('page')
                ->where('page.data.id', $homePage->id)
                ->where('page.data.title', 'Home')
                ->where('page.data.permalink', '/')
                ->where('page.data.is_active', true)
        );
    }

    // Test with partial reload
    #[Test]
    public function it_handles_root_permalink_with_partial_reload()
    {
        $homePage = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'permalink' => '/',
            'is_active' => true,
            'content' => 'Home content',
        ]);
        $homePage->roles()->attach(
            ModelsRole::all()->pluck('id')->toArray()
        );

        $this->actingAs($this->user);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'page/page',
            'X-Inertia-Partial-Data' => 'page',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Inertia', 'true');

        $data = $response->json();

        // Only the requested partial data should be in props
        $this->assertArrayHasKey('page', $data['props']);

        // Other props might not be present due to partial reload
        $this->assertEquals($homePage->id, $data['props']['page']['data']['id']);
    }

    // Test without Inertia headers (should return HTML)
    #[Test]
    public function it_returns_html_for_root_permalink_without_inertia_headers()
    {
        $homePage = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'permalink' => '/',
            'is_active' => true,
            'content' => 'Home content',
        ]);
        $homePage->roles()->attach(
            ModelsRole::all()->pluck('id')->toArray()
        );

        $this->actingAs($this->user);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeaderMissing('X-Inertia');
        $response->assertViewHas('page');
    }

    #[Test]
    public function it_allows_superuser_to_access_any_page()
    {
        $superuser = User::factory()->create();
        $superuser->assignRole(Role::SUPERUSER);

        $this->actingAs($superuser);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('page/page', $data['component']);
    }

    #[Test]
    public function it_allows_admin_to_access_any_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('page/page', $data['component']);
    }

    #[Test]
    public function it_allows_user_with_assigned_role_to_access_page()
    {
        $regularUser = User::factory()->create();
        $regularUser->assignRole(Role::USER);

        // Attach the USER role to the page
        $userRole = ModelsRole::where('name', Role::USER->value)->first();
        $this->page->roles()->attach($userRole->id);

        $this->actingAs($regularUser);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_denies_user_without_assigned_role_from_accessing_page()
    {
        $regularUser = User::factory()->create();
        $regularUser->assignRole(Role::USER);

        // Don't attach any roles to the page
        $this->page->roles()->detach();

        $this->actingAs($regularUser);

        $response = $this->get('/test-page');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_denies_guest_user_from_accessing_protected_page()
    {
        // Don't attach any roles to the page (requires authentication)
        $this->page->roles()->detach();

        $response = $this->get('/test-page');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_allows_guest_user_to_access_page_with_guest_role()
    {
        $guestRole = ModelsRole::where('name', Role::GUEST->value)->first();
        $this->page->roles()->attach($guestRole->id);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_allows_multiple_roles_to_access_same_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $regularUser = User::factory()->create();
        $regularUser->assignRole(Role::USER);

        // Attach both ADMIN and USER roles to the page
        $adminRole = ModelsRole::where('name', Role::ADMIN->value)->first();
        $userRole = ModelsRole::where('name', Role::USER->value)->first();
        $this->page->roles()->attach([$adminRole->id, $userRole->id]);

        // Admin should have access
        $this->actingAs($admin);
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');
        $response->assertStatus(200);

        // Regular user should have access
        $this->actingAs($regularUser);
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');
        $response->assertStatus(200);
    }

    #[Test]
    public function it_prioritizes_superuser_role_over_page_restrictions()
    {
        $superuser = User::factory()->create();
        $superuser->assignRole(Role::SUPERUSER);

        $regularUser = User::factory()->create();
        $regularUser->assignRole(Role::USER);

        // Only attach USER role to the page
        $userRole = ModelsRole::where('name', Role::USER->value)->first();
        $this->page->roles()->attach($userRole->id);

        // Regular user should have access
        $this->actingAs($regularUser);
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');
        $response->assertStatus(200);

        // Superuser should also have access (bypasses role check)
        $this->actingAs($superuser);
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');
        $response->assertStatus(200);
    }

    #[Test]
    public function it_returns_401_for_page_with_no_roles_attached_when_no_user()
    {
        // Create a page with no roles attached
        $restrictedPage = Page::create([
            'title' => 'Restricted Page',
            'slug' => 'restricted',
            'permalink' => '/restricted',
            'is_active' => true,
            'content' => 'Restricted content',
        ]);
        // No roles attached

        $response = $this->get('/restricted');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_returns_404_for_page_with_roles_but_user_has_no_matching_role()
    {
        // Attach only ADMIN role to the page
        $adminRole = ModelsRole::where('name', Role::ADMIN->value)->first();
        $this->page->roles()->detach(
            ModelsRole::all()->pluck('id')->toArray()
        );
        $this->page->roles()->attach($adminRole->id);

        $regularUser = User::factory()->create();
        $regularUser->assignRole(Role::USER);

        $this->actingAs($regularUser);

        $response = $this->get('/test-page');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_doesnt_allow_access_when_page_has_no_role_restrictions()
    {
        // Page with no roles attached (open to all authenticated users)
        $openPage = Page::create([
            'title' => 'Open Page',
            'slug' => 'open',
            'permalink' => '/open',
            'is_active' => true,
            'content' => 'Open content',
        ]);
        // No roles attached

        $regularUser = User::factory()->create();
        $regularUser->assignRole(Role::USER);

        $this->actingAs($regularUser);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/open');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_handles_page_with_multiple_role_attachments_correctly()
    {
        $superuser = User::factory()->create();
        $superuser->assignRole(Role::SUPERUSER);

        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $user = User::factory()->create();
        $user->assignRole(Role::USER);

        // Attach ADMIN and USER roles to the page
        $adminRole = ModelsRole::where('name', Role::ADMIN->value)->first();
        $userRole = ModelsRole::where('name', Role::USER->value)->first();
        $this->page->roles()->attach([$adminRole->id, $userRole->id]);

        // Superuser should have access
        $this->actingAs($superuser);
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');
        $response->assertStatus(200);

        // Admin should have access
        $this->actingAs($admin);
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');
        $response->assertStatus(200);

        // User should have access
        $this->actingAs($user);
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('/test-page');
        $response->assertStatus(200);
    }
}
