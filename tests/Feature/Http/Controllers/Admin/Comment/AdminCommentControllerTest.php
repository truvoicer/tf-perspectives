<?php

// tests/Feature/Admin/Comment/AdminCommentControllerTest.php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Controllers\Admin\Comment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role;
use Truvoicer\TfPerspectives\Enums\Page\DefaultPage;
use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\Page;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

class AdminCommentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::ADMIN);

        // Create required page
        Page::create([
            'slug' => DefaultPage::ADMIN_COMMENTS->value,
            'is_active' => true,
            'title' => 'Admin Comments',
            'content' => 'Test content',
        ]);

        $this->actingAs($this->admin);
    }

    #[Test]
    public function it_displays_comments_index_page_for_partial_reloads()
    {
        Comment::factory()->count(5)->create();

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/index',
            'X-Inertia-Partial-Data' => 'comments,page',
        ])->get(route('admin.comment.index'));

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');

        $data = $response->json();

        // Assert Inertia response structure
        $this->assertArrayHasKey('component', $data);
        $this->assertArrayHasKey('props', $data);
        $this->assertArrayHasKey('url', $data);
        $this->assertArrayHasKey('version', $data);

        // Assert component name
        $this->assertEquals('admin/comment/index', $data['component']);

        // Assert URL is correct
        $this->assertEquals('/admin/comment', $data['url']);

        // Assert version exists (could be empty string or hash)
        $this->assertIsString($data['version']);

        // Assert sharedProps exists
        $this->assertArrayHasKey('sharedProps', $data);

        // Assert props exist and have required keys
        $this->assertArrayHasKey('page', $data['props']);
        $this->assertArrayHasKey('comments', $data['props']);

        // Assert page data
        $this->assertEquals(DefaultPage::ADMIN_COMMENTS->value, $data['props']['page']['data']['slug']);

        // Assert comments data
        $this->assertCount(5, $data['props']['comments']['data']);
    }

    #[Test]
    public function it_displays_comments_index_page_with_pagination_for_partial_reloads()
    {
        Comment::factory()->count(25)->create();

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/index',
            'X-Inertia-Partial-Data' => 'comments',
        ])->get(route('admin.comment.index', [
            'per_page' => 10,
            'page' => 2,
        ]));

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');

        $data = $response->json();

        // Assert props structure
        $this->assertArrayHasKey('props', $data);
        $this->assertArrayHasKey('comments', $data['props']);
        $this->assertArrayHasKey('data', $data['props']['comments']);
        $this->assertArrayHasKey('meta', $data['props']['comments']);
        $this->assertArrayHasKey('links', $data['props']['comments']);

        // Assert pagination data count
        $this->assertCount(10, $data['props']['comments']['data']);

        // Assert meta information
        $this->assertEquals(2, $data['props']['comments']['meta']['current_page']);
        $this->assertEquals(10, $data['props']['comments']['meta']['per_page']);
        $this->assertEquals(25, $data['props']['comments']['meta']['total']);
        $this->assertEquals(3, $data['props']['comments']['meta']['last_page']);

        // Assert links structure
        $this->assertArrayHasKey('first', $data['props']['comments']['links']);
        $this->assertArrayHasKey('last', $data['props']['comments']['links']);
        $this->assertArrayHasKey('prev', $data['props']['comments']['links']);
        $this->assertArrayHasKey('next', $data['props']['comments']['links']);
    }

    #[Test]
    public function it_displays_comments_index_page_with_search_for_partial_reloads()
    {
        // Create test comments
        $testComment1 = Comment::factory()->create(['content' => 'Test comment one']);
        $testComment2 = Comment::factory()->create(['content' => 'Test comment two']);
        $otherComment = Comment::factory()->create(['content' => 'Another comment']);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/index',
            'X-Inertia-Partial-Data' => 'comments',
        ])->get(route('admin.comment.index', [
            'query' => 'Test',
        ]));

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');

        $data = $response->json();

        // Assert props structure
        $this->assertArrayHasKey('props', $data);
        $this->assertArrayHasKey('comments', $data['props']);
        $this->assertArrayHasKey('data', $data['props']['comments']);

        // Assert only 2 comments are returned (both "Test" comments)
        $this->assertCount(2, $data['props']['comments']['data']);

        // Get the returned comment IDs
        $returnedIds = collect($data['props']['comments']['data'])->pluck('id')->toArray();

        // Assert the test comments are in the results
        $this->assertContains($testComment1->id, $returnedIds);
        $this->assertContains($testComment2->id, $returnedIds);

        // Assert the other comment is NOT in the results
        $this->assertNotContains($otherComment->id, $returnedIds);

        // Assert the content matches (order may vary, so check both possibilities)
        $contents = collect($data['props']['comments']['data'])->pluck('content')->toArray();
        $this->assertContains('Test comment one', $contents);
        $this->assertContains('Test comment two', $contents);
        $this->assertNotContains('Another comment', $contents);
    }

    #[Test]
    public function it_displays_comments_index_page_without_optional_props()
    {
        Comment::factory()->create(['status' => 'active']);
        Comment::factory()->create(['status' => 'hidden']);
        Comment::factory()->create(['status' => 'deleted']);

        $response = $this->get(route('admin.comment.index', [
            'status' => 'active',
        ]));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->missing('comments', 1)
        );
    }

    #[Test]
    public function it_displays_comments_index_page_with_status_filter_for_partial_reload()
    {
        $activeComment = Comment::factory()->create(['status' => 'active']);
        $hiddenComment = Comment::factory()->create(['status' => 'hidden']);
        $deletedComment = Comment::factory()->create(['status' => 'deleted']);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/index',
            'X-Inertia-Partial-Data' => 'comments',
        ])->get(route('admin.comment.index', [
            'status' => 'active',
        ]));

        $response->assertJsonStructure([
            'props' => [
                'comments' => [
                    'data',
                    'links',
                    'meta',
                ],
            ],
        ]);

        $data = $response->json();

        // Assert the props structure
        $this->assertArrayHasKey('props', $data);
        $this->assertArrayHasKey('comments', $data['props']);
        $this->assertArrayHasKey('data', $data['props']['comments']);

        // Assert only one comment is returned
        $this->assertCount(1, $data['props']['comments']['data']);

        // Assert the comment has status 'active'
        $this->assertEquals('active', $data['props']['comments']['data'][0]['status']);
        $this->assertEquals($activeComment->id, $data['props']['comments']['data'][0]['id']);

        // Assert the hidden and deleted comments are not in the results
        $returnedIds = collect($data['props']['comments']['data'])->pluck('id')->toArray();
        $this->assertContains($activeComment->id, $returnedIds);
        $this->assertNotContains($hiddenComment->id, $returnedIds);
        $this->assertNotContains($deletedComment->id, $returnedIds);
    }

    #[Test]
    public function it_returns_only_requested_props_for_partial_reload()
    {
        Comment::factory()->count(5)->create();

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/index',
            'X-Inertia-Partial-Data' => 'comments',
        ])->get(route('admin.comment.index'));

        $response->assertJsonStructure([
            'props' => [
                'comments' => [
                    'data',
                    'links',
                    'meta',
                ],
            ],
        ]);
    }

    #[Test]
    public function it_displays_comment_show_page()
    {
        $comment = Comment::factory()->create();

        $response = $this->get(route('admin.comment.show', $comment->id));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('page')
                ->has('comment')
                ->where('comment.data.id', $comment->id)
                ->where('comment.data.content', $comment->content)
        );
    }

    #[Test]
    public function it_displays_create_comment_page()
    {
        $response = $this->get(route('admin.comment.create'));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('page')
        );
    }

    #[Test]
    public function it_displays_edit_comment_page()
    {
        $comment = Comment::factory()->create();

        $response = $this->get(route('admin.comment.edit', $comment->id));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('page')
                ->has('comment')
                ->where('comment.data.id', $comment->id)
        );
    }

    #[Test]
    public function it_stores_a_new_comment()
    {
        $user = User::factory()->create();

        $commentData = [
            'user_id' => $user->getId(),
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'content' => 'This is a test comment',
            'status' => 'active',
            'is_approved' => true,
            'is_pinned' => false,
        ];

        $response = $this->post(route('admin.comment.store'), $commentData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment created successfully.');

        $this->assertDatabaseHas('comments', [
            'content' => 'This is a test comment',
            'user_id' => $user->getId(),
            'item_id' => 'post_123',
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_storing_comment()
    {
        $response = $this->post(route('admin.comment.store'), []);

        $response->assertSessionHasErrors(['provider', 'service', 'item_id', 'content']);
    }

    #[Test]
    public function it_updates_a_comment()
    {
        $comment = Comment::factory()->create([
            'content' => 'Original content',
        ]);

        $response = $this->patch(route('admin.comment.update', $comment->id), [
            'user_id' => $comment->user_id,
            'provider' => $comment->provider,
            'service' => $comment->service,
            'item_id' => $comment->item_id,
            'content' => 'Updated content',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment updated successfully.');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    }

    #[Test]
    public function it_deletes_a_comment()
    {
        $comment = Comment::factory()->create();

        $response = $this->delete(route('admin.comment.destroy', $comment->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment deleted successfully.');

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);
    }

    #[Test]
    public function it_bulk_deletes_comments()
    {
        $comments = Comment::factory()->count(3)->create();
        $commentIds = $comments->pluck('id')->toArray();

        $response = $this->post(route('admin.comment.bulk.destroy'), [
            'ids' => $commentIds,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Selected comments deleted successfully.');

        foreach ($comments as $comment) {
            // Check that the comment is soft deleted (deleted_at is not null)
            $this->assertSoftDeleted('comments', ['id' => $comment->id]);
        }
    }

    #[Test]
    public function it_validates_bulk_delete_request()
    {
        $response = $this->post(route('admin.comment.bulk.destroy'), [
            'ids' => ['invalid'],
        ]);

        $response->assertSessionHasErrors(['ids.0']);
    }

    #[Test]
    public function it_returns_404_when_page_not_found()
    {
        Page::where('slug', DefaultPage::ADMIN_COMMENTS->value)->delete();

        $response = $this->get(route('admin.comment.index'));

        $response->assertStatus(404);
    }
}
