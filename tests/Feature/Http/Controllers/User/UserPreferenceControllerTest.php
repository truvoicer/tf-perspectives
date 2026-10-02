<?php

// tests/Feature/Http/Controllers/User/UserPreferenceControllerTest.php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\User\Theme;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Models\UserPreference;
use Truvoicer\TfPerspectives\Tests\TestCase;

class UserPreferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_updates_theme_preference_for_authenticated_user()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.preferences.update'), [
            'theme' => Theme::DARK,
            'email_subscribe' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Preferences updated successfully.',
            ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->getId(),
            'theme' => 'dark',
        ]);
    }

    #[Test]
    public function it_updates_multiple_preferences_at_once()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.preferences.update'), [
            'theme' => 'light',
            'timezone' => 'America/New_York',
            'email_subscribe' => true,
            'push_notifications' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Preferences updated successfully.',
            ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->getId(),
            'theme' => 'light',
            'timezone' => 'America/New_York',
            'email_subscribe' => 1,
            'push_notifications' => 0,
        ]);
    }

    #[Test]
    public function it_updates_existing_preference_record()
    {
        $this->actingAs($this->user);

        // Create initial preference
        UserPreference::create([
            'user_id' => $this->user->getId(),
            'timezone' => 'UTC',
            'theme' => Theme::LIGHT,
            'email_subscribe' => true,
        ]);

        // Update preference
        $response = $this->postJson(route('user.preferences.update'), [
            'theme' => 'dark',
            'timezone' => 'America/Los_Angeles',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->getId(),
            'theme' => 'dark',
            'timezone' => 'America/Los_Angeles',
        ]);

        // Should only have one record
        $this->assertEquals(1, UserPreference::where('user_id', $this->user->getId())->count());
    }

    #[Test]
    public function it_accepts_system_theme_preference()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.preferences.update'), [
            'theme' => Theme::SYSTEM,
            'email_subscribe' => true,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->getId(),
            'theme' => 'system',
        ]);
    }

    #[Test]
    public function it_validates_theme_field()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.preferences.update'), [
            'theme' => 'invalid_theme',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['theme']);
    }

    #[Test]
    public function it_validates_timezone_field()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.preferences.update'), [
            'timezone' => str_repeat('a', 300),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['timezone']);
    }

    #[Test]
    public function it_validates_email_subscribe_as_boolean()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.preferences.update'), [
            'email_subscribe' => 'not_a_boolean',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email_subscribe']);
    }

    #[Test]
    public function it_validates_push_notifications_as_boolean()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.preferences.update'), [
            'push_notifications' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['push_notifications']);
    }

    #[Test]
    public function it_returns_unauthorized_for_guest_user()
    {
        $response = $this->postJson(route('user.preferences.update'), [
            'theme' => 'dark',
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_handles_null_values_gracefully()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('user.preferences.update'), [
            'theme' => Theme::SYSTEM,
            'timezone' => null,
            'email_subscribe' => true,
            'push_notifications' => true,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->getId(),
            'theme' => Theme::SYSTEM->value,
            'timezone' => null,
            'email_subscribe' => 1,
            'push_notifications' => 1,
        ]);
    }

    #[Test]
    public function it_preserves_existing_values_when_partial_update()
    {
        $this->actingAs($this->user);

        // Create initial preference with all values
        UserPreference::create([
            'user_id' => $this->user->getId(),
            'theme' => 'light',
            'timezone' => 'Europe/London',
            'email_subscribe' => true,
            'push_notifications' => true,
        ]);

        // Update only theme
        $response = $this->postJson(route('user.preferences.update'), [
            'theme' => 'dark',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->getId(),
            'theme' => 'dark',
            'timezone' => 'Europe/London',
            'email_subscribe' => 1,
            'push_notifications' => 1,
        ]);
    }
}
