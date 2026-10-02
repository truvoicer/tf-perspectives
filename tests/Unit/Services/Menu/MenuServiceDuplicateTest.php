<?php

namespace Truvoicer\TfPerspectives\Tests\Unit\Services\Menu;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Database\Seeders\PermissionSeeder;
use Truvoicer\TfPerspectives\Database\Seeders\RoleSeeder;
use Truvoicer\TfPerspectives\Enums\Auth\Permission\Permission as AuthPermission;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role as AuthRole;
use Truvoicer\TfPerspectives\Enums\Menu\MenuItemType;
use Truvoicer\TfPerspectives\Models\Menu;
use Truvoicer\TfPerspectives\Models\MenuItem;
use Truvoicer\TfPerspectives\Models\Page;
use Truvoicer\TfPerspectives\Models\Permission;
use Truvoicer\TfPerspectives\Models\Role;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Services\Menu\MenuService;
use Truvoicer\TfPerspectives\Tests\TestCase;

class MenuServiceDuplicateTest extends TestCase
{
    use RefreshDatabase;

    protected MenuService $menuService;

    protected User $superUser;

    protected User $adminUser;

    protected User $guestUser;

    protected Role $superRole;

    protected Role $adminRole;

    protected Role $guestRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->menuService = app(MenuService::class);

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
        // Create roles
        $this->superRole = Role::where('name', AuthRole::SUPERUSER->value)->first();
        $this->adminRole = Role::where('name', AuthRole::ADMIN->value)->first();
        $this->guestRole = Role::where('name', AuthRole::GUEST->value)->first();

        // Create users with roles
        $this->superUser = User::create([
            'name' => 'Super User',
            'email' => 'super@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->superUser->assignRole(AuthRole::SUPERUSER);

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->assignRole(AuthRole::ADMIN);

        $this->guestUser = User::create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->guestUser->assignRole(AuthRole::GUEST);

        // Authenticate as super user for tests (bypasses scopes)
        $this->actingAs($this->superUser);
    }

    private function createMenuWithRoles(Menu $menu, array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)->get();
        $menu->roles()->attach($roles->pluck('id')->toArray());
    }

    private function createMenuItemWithRoles(MenuItem $menuItem, array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)->get();
        $menuItem->roles()->attach($roles->pluck('id')->toArray());
    }

    #[Test]
    public function it_can_duplicate_a_menu_without_menu_items()
    {
        // Create original menu with roles
        $originalMenu = Menu::create([
            'title' => 'Original Menu',
            'slug' => 'original-menu',
            'ul_class' => 'nav nav-tabs',
            'is_active' => true,
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin', 'guest']);

        // Duplicate the menu
        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, false);

        // Assert duplicated menu exists
        $this->assertNotNull($duplicatedMenu);
        $this->assertNotEquals($originalMenu->id, $duplicatedMenu->id);

        // Assert duplicated menu properties
        $this->assertEquals('Original Menu (Copy)', $duplicatedMenu->title);
        $this->assertStringStartsWith('original-menu-copy', $duplicatedMenu->slug);
        $this->assertEquals('nav nav-tabs', $duplicatedMenu->ul_class);
        $this->assertFalse($duplicatedMenu->is_active);

        // Assert roles were copied (using withoutGlobalScopes to see all roles)
        $this->assertCount(2, $duplicatedMenu->roles()->withoutGlobalScopes()->get());
    }

    #[Test]
    public function it_can_duplicate_a_menu_with_menu_items()
    {
        // Create original menu with roles
        $originalMenu = Menu::create([
            'title' => 'Original Menu',
            'slug' => 'original-menu',
            'is_active' => true,
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin']);

        // Create menu items with roles
        $menuItem1 = MenuItem::create([
            'title' => 'Home',
            'type' => MenuItemType::PAGE,
            'url' => '/home',
            'is_active' => true,
        ]);
        $this->createMenuItemWithRoles($menuItem1, ['admin', 'guest']);

        $menuItem2 = MenuItem::create([
            'title' => 'About',
            'type' => MenuItemType::PAGE,
            'url' => '/about',
            'is_active' => true,
        ]);
        $this->createMenuItemWithRoles($menuItem2, ['guest']);

        // Attach menu items with order
        $originalMenu->menuItems()->attach($menuItem1->id, ['order' => 1]);
        $originalMenu->menuItems()->attach($menuItem2->id, ['order' => 2]);

        // Duplicate the menu with items
        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, true);

        // Assert duplicated menu has items
        $this->assertCount(2, $duplicatedMenu->menuItems()->withoutGlobalScopes()->get());

        // Check if items were duplicated (not just attached from original)
        $duplicatedMenuItemIds = $duplicatedMenu->menuItems()->withoutGlobalScopes()->pluck('menu_items.id')->toArray();
        $this->assertNotContains($menuItem1->id, $duplicatedMenuItemIds);
        $this->assertNotContains($menuItem2->id, $duplicatedMenuItemIds);

        // Check item properties
        $duplicatedMenuItem1 = $duplicatedMenu->menuItems()->withoutGlobalScopes()->first();
        $this->assertEquals('Home', $duplicatedMenuItem1->title);
        $this->assertEquals('/home', $duplicatedMenuItem1->url);

        // Assert menu item roles were copied
        $this->assertCount(2, $duplicatedMenuItem1->roles()->withoutGlobalScopes()->get());
        // $this->assertCount(1, $duplicatedMenuItem2->roles()->withoutGlobalScopes()->get());
    }

    #[Test]
    public function it_preserves_menu_item_order_when_duplicating()
    {
        $originalMenu = Menu::create([
            'title' => 'Original Menu',
            'slug' => 'original-menu',
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin']);

        $item1 = MenuItem::create(['title' => 'First', 'type' => MenuItemType::PAGE, 'url' => '/first']);
        $item2 = MenuItem::create(['title' => 'Second', 'type' => MenuItemType::PAGE, 'url' => '/second']);
        $item3 = MenuItem::create(['title' => 'Third', 'type' => MenuItemType::PAGE, 'url' => '/third']);

        $originalMenu->menuItems()->attach($item1->id, ['order' => 3]);
        $originalMenu->menuItems()->attach($item2->id, ['order' => 1]);
        $originalMenu->menuItems()->attach($item3->id, ['order' => 2]);

        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, true);

        $orders = $duplicatedMenu->menuItems()
            ->withoutGlobalScopes()
            ->withPivot('order')
            ->orderBy('order')
            ->get()
            ->pluck('pivot.order')
            ->toArray();

        $this->assertEquals([1, 2, 3], $orders);
    }

    #[Test]
    public function it_copies_menu_permissions_when_duplicating()
    {

        $permission1 = Permission::where('name', AuthPermission::CREATE)->first();
        $permission2 = Permission::where('name', AuthPermission::VIEW)->first();

        $originalMenu = Menu::create([
            'title' => 'Original Menu',
            'slug' => 'original-menu',
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin']);
        $originalMenu->permissions()->attach([$permission1->id, $permission2->id]);

        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, false);

        $this->assertCount(2, $duplicatedMenu->permissions);
        $duplicatedPermissionIds = $duplicatedMenu->permissions->pluck('id')->toArray();
        $this->assertContains($permission1->id, $duplicatedPermissionIds);
        $this->assertContains($permission2->id, $duplicatedPermissionIds);
    }

    #[Test]
    public function it_copies_menu_roles_when_duplicating()
    {
        $originalMenu = Menu::create([
            'title' => 'Original Menu',
            'slug' => 'original-menu',
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin', 'guest']);

        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, false);

        $this->assertCount(2, $duplicatedMenu->roles()->withoutGlobalScopes()->get());
        $duplicatedRoleNames = $duplicatedMenu->roles()->withoutGlobalScopes()->pluck('name')->toArray();
        $this->assertContains('admin', $duplicatedRoleNames);
        $this->assertContains('guest', $duplicatedRoleNames);
    }

    #[Test]
    public function it_copies_menu_item_permissions_when_duplicating()
    {
        $permission = Permission::where('name', AuthPermission::CREATE)->first();

        $originalMenu = Menu::create([
            'title' => 'Original Menu',
            'slug' => 'original-menu',
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin']);

        $menuItem = MenuItem::create([
            'title' => 'Dashboard',
            'type' => MenuItemType::PAGE,
            'url' => '/dashboard',
        ]);
        $this->createMenuItemWithRoles($menuItem, ['admin']);
        $menuItem->permissions()->attach($permission->id);
        $originalMenu->menuItems()->attach($menuItem->id, ['order' => 1]);

        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, true);
        $duplicatedMenuItem = $duplicatedMenu->menuItems()->withoutGlobalScopes()->first();

        $this->assertCount(1, $duplicatedMenuItem->permissions);
        $this->assertEquals($permission->id, $duplicatedMenuItem->permissions->first()->id);
    }

    #[Test]
    public function it_copies_menu_item_roles_when_duplicating()
    {
        $originalMenu = Menu::create([
            'title' => 'Original Menu',
            'slug' => 'original-menu',
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin']);

        $menuItem = MenuItem::create([
            'title' => 'Admin Panel',
            'type' => MenuItemType::PAGE,
            'url' => '/admin',
        ]);
        $this->createMenuItemWithRoles($menuItem, ['admin', 'guest']);
        $originalMenu->menuItems()->attach($menuItem->id, ['order' => 1]);

        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, true);
        $duplicatedMenuItem = $duplicatedMenu->menuItems()->withoutGlobalScopes()->first();

        $this->assertCount(2, $duplicatedMenuItem->roles()->withoutGlobalScopes()->get());
        $duplicatedRoleNames = $duplicatedMenuItem->roles()->withoutGlobalScopes()->pluck('name')->toArray();
        $this->assertContains('admin', $duplicatedRoleNames);
        $this->assertContains('guest', $duplicatedRoleNames);
    }

    #[Test]
    public function it_handles_duplicating_menu_with_nested_menu_items()
    {
        $parentMenu = Menu::create([
            'title' => 'Parent Menu',
            'slug' => 'parent-menu',
        ]);
        $this->createMenuWithRoles($parentMenu, ['admin']);

        $childMenu = Menu::create([
            'title' => 'Child Menu',
            'slug' => 'child-menu',
        ]);
        $this->createMenuWithRoles($childMenu, ['admin']);

        $menuItem = MenuItem::create([
            'title' => 'Dropdown',
            'type' => MenuItemType::CUSTOM_LINK,
            'is_active' => true,
        ]);
        $this->createMenuItemWithRoles($menuItem, ['admin']);

        $parentMenu->menuItems()->attach($menuItem->id, ['order' => 1]);
        $menuItem->menus()->attach($childMenu->id);

        $duplicatedMenu = $this->menuService->duplicateMenu($parentMenu, true);

        $this->assertNotNull($duplicatedMenu);
        $this->assertNotEquals($parentMenu->id, $duplicatedMenu->id);
        $this->assertCount(1, $duplicatedMenu->menuItems()->withoutGlobalScopes()->get());

        $duplicatedMenuItem = $duplicatedMenu->menuItems()->withoutGlobalScopes()->first();
        $this->assertEquals('Dropdown', $duplicatedMenuItem->title);
    }

    #[Test]
    public function it_handles_duplicating_menu_for_guest_users()
    {
        // Switch to guest user (roles are applied via scope)
        $this->actingAs($this->guestUser);

        $originalMenu = Menu::create([
            'title' => 'Guest Menu',
            'slug' => 'guest-menu',
        ]);
        $this->createMenuWithRoles($originalMenu, ['guest']);

        // The scope should allow access since guest role matches
        $this->assertTrue($originalMenu->exists);

        // Duplicate - should work because we're using withoutGlobalScopes in the service
        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, false);

        $this->assertNotNull($duplicatedMenu);
        $this->assertEquals('Guest Menu (Copy)', $duplicatedMenu->title);
    }

    #[Test]
    public function it_creates_unique_slug_for_duplicated_menu()
    {
        $originalMenu = Menu::create([
            'title' => 'Main Menu',
            'slug' => 'main-menu',
            'is_active' => true,
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin']);

        $duplicate1 = $this->menuService->duplicateMenu($originalMenu, false);
        $duplicate2 = $this->menuService->duplicateMenu($originalMenu, false);

        $this->assertNotEquals($duplicate1->slug, $duplicate2->slug);
        $this->assertStringContainsString('main-menu-copy', $duplicate1->slug);
        $this->assertStringContainsString('main-menu-copy-', $duplicate2->slug);
    }

    #[Test]
    public function it_creates_inactive_duplicated_menu_by_default()
    {
        $originalMenu = Menu::create([
            'title' => 'Active Menu',
            'slug' => 'active-menu',
            'is_active' => true,
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin']);

        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, false);

        $this->assertFalse($duplicatedMenu->is_active);
    }

    #[Test]
    public function it_copies_page_id_from_menu_items()
    {
        $page = Page::factory()->create();
        $originalMenu = Menu::create([
            'title' => 'Menu with Page',
            'slug' => 'menu-with-page',
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin']);

        $menuItem = MenuItem::create([
            'title' => 'Page Link',
            'type' => MenuItemType::PAGE,
            'page_id' => $page->id,
            'url' => null,
        ]);
        $this->createMenuItemWithRoles($menuItem, ['admin']);
        $originalMenu->menuItems()->attach($menuItem->id, ['order' => 1]);

        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, true);
        $duplicatedMenuItem = $duplicatedMenu->menuItems()->withoutGlobalScopes()->first();

        $this->assertEquals(1, $duplicatedMenuItem->page_id);
    }

    #[Test]
    public function it_preserves_menu_item_icon_and_classes_when_duplicating()
    {
        $originalMenu = Menu::create([
            'title' => 'Menu with Styled Items',
            'slug' => 'styled-menu',
        ]);
        $this->createMenuWithRoles($originalMenu, ['admin']);

        $menuItem = MenuItem::create([
            'title' => 'Styled Link',
            'type' => MenuItemType::CUSTOM_LINK,
            'url' => '/styled',
            'icon' => 'fa-star',
            'li_class' => 'nav-item-custom',
            'a_class' => 'nav-link-custom',
            'target' => '_blank',
        ]);
        $this->createMenuItemWithRoles($menuItem, ['admin']);
        $originalMenu->menuItems()->attach($menuItem->id, ['order' => 1]);

        $duplicatedMenu = $this->menuService->duplicateMenu($originalMenu, true);
        $duplicatedMenuItem = $duplicatedMenu->menuItems()->withoutGlobalScopes()->first();

        $this->assertEquals('fa-star', $duplicatedMenuItem->icon);
        $this->assertEquals('nav-item-custom', $duplicatedMenuItem->li_class);
        $this->assertEquals('nav-link-custom', $duplicatedMenuItem->a_class);
        $this->assertEquals('_blank', $duplicatedMenuItem->target);
    }
}
