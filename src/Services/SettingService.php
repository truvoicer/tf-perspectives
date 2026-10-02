<?php

namespace Truvoicer\TfPerspectives\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Truvoicer\TfPerspectives\Models\Setting;
use Truvoicer\TfPerspectives\Repositories\SettingRepository;

class SettingService
{
    protected SettingRepository $repository;

    protected string $cacheKey = 'site_settings';

    protected int $cacheTtl = 3600; // 1 hour

    protected array $configDefaults = [];

    public function __construct()
    {
        $this->repository = new SettingRepository;
        $this->loadConfigDefaults();
    }

    /**
     * Load default values from various config files.
     */
    protected function loadConfigDefaults(): void
    {
        $this->configDefaults = [
            // App info from config
            'app_name' => config('app.name'),
            'app_email' => config('mail.from.address'),

            // Site settings from services.php
            'site_email' => config('services.site.email'),
            'site_copyright' => config('services.site.copyright'),

            // Analytics settings
            'analytics_driver' => config('tru-fetcher-core.analytics.default'),
            'gtm_id' => config('services.google.tag_manager.id'),
            'gtm_server_endpoint' => config('tru-fetcher-core.analytics.gtm.endpoint'),
            'ga_measurement_id' => config('tru-fetcher-core.analytics.ga.measurement_id'),
            'ga_api_secret' => config('tru-fetcher-core.analytics.ga.api_secret'),

            // Social login
            'google_client_id' => config('tru-fetcher-core.google.client_id'),
            'facebook_app_id' => config('tru-fetcher-core.facebook.app_id'),
            'facebook_app_secret' => config('tru-fetcher-core.facebook.app_secret'),

            // Social share links
            'facebook_share_link' => config('services.site.social.facebook.share_link'),
            'linkedin_share_link' => config('services.site.social.linkedin.share_link'),
            'x_share_link' => config('services.site.social.x.share_link'),
            'instagram_share_link' => config('services.site.social.instagram.share_link'),
            'tiktok_share_link' => config('services.site.social.tiktok.share_link'),
            'rss_share_link' => config('services.site.social.rss.share_link'),

            // Comment settings default
            'comment_depth' => 5,
            'enable_edit_comment_window' => true,
            'enable_delete_comment_window' => true,
            'edit_comment_window_minutes' => 30,
            'delete_comment_window_minutes' => 30,
            'require_login_to_comment' => true,
            'require_login_to_like_comment' => true,
            'require_login_to_report_comment' => true,
            'default_entity_service' => null,
            'default_entity_category' => null,
            'default_services' => null,
        ];
    }

    /**
     * Get the singleton instance of the service.
     */
    public static function instance(): self
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new self;
        }

        return $instance;
    }

    /**
     * Get all settings.
     */
    public static function getSettings(): Setting
    {
        return self::instance()->repository->getSettings();
    }

    /**
     * Get a specific setting value with optional fallback to config defaults.
     *
     * @param  string  $key  The setting key
     * @param  mixed  $default  Default value if setting doesn't exist
     * @param  bool  $useConfigFallback  Whether to fall back to config defaults if setting is null
     */
    public static function get(string $key, mixed $default = null, bool $useConfigFallback = true): mixed
    {
        $service = self::instance();
        $value = $service->repository->getSetting($key);

        // If value exists in database, return it
        if ($value !== null) {
            return $value;
        }

        // Check config defaults if fallback is enabled
        if ($useConfigFallback && array_key_exists($key, $service->configDefaults)) {
            return $service->configDefaults[$key];
        }

        // Return provided default or null
        return $default;
    }

    /**
     * Get a setting value with caching.
     */
    public static function getCached(string $key, mixed $default = null, ?int $ttl = null, bool $useConfigFallback = true): mixed
    {
        $service = self::instance();
        $cacheKey = $service->cacheKey.'.'.$key;

        return Cache::remember($cacheKey, $ttl ?? $service->cacheTtl, function () use ($key, $default, $useConfigFallback) {
            return self::get($key, $default, $useConfigFallback);
        });
    }

    /**
     * Update settings.
     */
    public static function update(array $data): Setting
    {
        $service = self::instance();
        $result = $service->repository->updateSettings($data);

        // Clear cache after update
        $service->clearCache($data);

        return $result;
    }

    /**
     * Update or create settings.
     */
    public static function updateOrCreate(array $data): Setting
    {
        $service = self::instance();
        $result = $service->repository->updateOrCreateSettings($data);

        // Clear cache after update
        $service->clearCache();

        return $result;
    }

    /**
     * Create new settings (only if none exist).
     */
    public static function create(array $data): Setting
    {
        $service = self::instance();
        $result = $service->repository->createSettings($data);

        // Clear cache after creation
        $service->clearCache();

        return $result;
    }

    /**
     * Initialize default settings.
     */
    public static function initializeDefaults(array $defaults = []): Setting
    {
        $service = self::instance();

        // Merge with config defaults
        $mergedDefaults = array_merge($service->configDefaults, $defaults);

        $result = $service->repository->initializeDefaultSettings($mergedDefaults);

        // Clear cache after initialization
        $service->clearCache();

        return $result;
    }

    /**
     * Toggle a boolean setting.
     */
    public static function toggle(string $key): ?bool
    {
        $service = self::instance();
        $result = $service->repository->toggleSetting($key);

        // Clear cache after toggle
        $service->clearCache();

        return $result;
    }

    /**
     * Check if settings exist.
     */
    public static function exists(): bool
    {
        return self::instance()->repository->settingsExist();
    }

    /**
     * Check if a specific setting exists (not null in database).
     */
    public static function has(string $key): bool
    {
        return self::instance()->repository->hasSetting($key);
    }

    /**
     * Check if a setting has a value (including config fallback).
     */
    public static function hasValue(string $key, bool $includeConfigFallback = true): bool
    {
        $value = self::get($key, null, $includeConfigFallback);

        return ! is_null($value) && $value !== '';
    }

    /**
     * Get all settings as array with optional config fallback.
     */
    public static function all(bool $useConfigFallback = true): array
    {
        $service = self::instance();
        $settings = $service->repository->getSettingsArray();

        if (! $useConfigFallback) {
            return $settings;
        }

        // Merge with config defaults for any missing keys
        $merged = [];
        $allKeys = array_unique(array_merge(array_keys($settings), array_keys($service->configDefaults)));

        foreach ($allKeys as $key) {
            if (array_key_exists($key, $settings) && $settings[$key] !== null) {
                $merged[$key] = $settings[$key];
            } elseif (array_key_exists($key, $service->configDefaults)) {
                $merged[$key] = $service->configDefaults[$key];
            } else {
                $merged[$key] = null;
            }
        }

        return $merged;
    }

    /**
     * Get all settings with caching.
     */
    public static function allCached(?int $ttl = null, bool $useConfigFallback = true): array
    {
        $service = self::instance();
        $cacheKey = $service->cacheKey.'_all';

        return Cache::remember($cacheKey, $ttl ?? $service->cacheTtl, function () use ($useConfigFallback) {
            return self::all($useConfigFallback);
        });
    }

    /**
     * Get only filled (non-null) settings.
     */
    public static function filled(): array
    {
        return self::instance()->repository->getFilledSettings();
    }

    /**
     * Get settings with specific keys only.
     */
    public static function only(array $keys, bool $useConfigFallback = true): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::get($key, null, $useConfigFallback);
        }

        return $result;
    }

    /**
     * Update multiple settings with key filtering.
     */
    public static function updateMultiple(array $data, array $allowedKeys = []): Setting
    {
        $service = self::instance();
        $result = $service->repository->updateMultipleSettings($data, $allowedKeys);

        // Clear cache after update
        $service->clearCache();

        return $result;
    }

    /**
     * Clear all settings cache.
     */
    public static function clearCache(?array $data = []): bool
    {
        $service = self::instance();

        Cache::forget($service->cacheKey);
        Cache::forget($service->cacheKey.'_all');

        // Also clear any pattern-based caches if needed
        $keys = Cache::get($service->cacheKey.'_keys', []);
        foreach ($keys as $key) {
            Cache::forget($service->cacheKey.'.'.$key);
        }
        Cache::forget($service->cacheKey.'_keys');

        // Also clear the specific keys that were updated
        foreach (array_keys($data) as $key) {
            Cache::forget($service->cacheKey.'.'.$key);
        }

        return true;
    }

    /**
     * Get the require login to comment setting.
     */
    public static function isLoginRequiredForComments(): bool
    {
        return (bool) self::get('require_login_to_comment', true);
    }

    /**
     * Get the require login to like comment setting.
     */
    public static function isLoginRequiredForLikeComments(): bool
    {
        return (bool) self::get('require_login_to_like_comment', true);
    }

    /**
     * Get the require login to report comment setting.
     */
    public static function isLoginRequiredForReportComments(): bool
    {
        return (bool) self::get('require_login_to_report_comment', true);
    }

    /**
     * Get the analytics driver.
     */
    public static function getAnalyticsDriver(): ?string
    {
        return self::get('analytics_driver');
    }

    /**
     * Get Google Tag Manager ID.
     */
    public static function getGtmId(): ?string
    {
        return self::get('gtm_id');
    }

    /**
     * Get Google Analytics measurement ID.
     */
    public static function getGaMeasurementId(): ?string
    {
        return self::get('ga_measurement_id');
    }

    /**
     * Get social share links with config fallback.
     */
    public static function getSocialShareLinks(): array
    {
        return [
            'facebook' => self::get('facebook_share_link'),
            'linkedin' => self::get('linkedin_share_link'),
            'x' => self::get('x_share_link'),
            'instagram' => self::get('instagram_share_link'),
            'tiktok' => self::get('tiktok_share_link'),
            'rss' => self::get('rss_share_link'),
        ];
    }

    /**
     * Get app information with config fallback.
     */
    public static function getAppInfo(): array
    {
        return [
            'name' => self::get('app_name'),
            'email' => self::get('app_email'),
            'site_email' => self::get('site_email'),
            'copyright' => self::get('site_copyright'),
        ];
    }

    /**
     * Get Google client ID.
     */
    public static function getGoogleClientId(): ?string
    {
        return self::get('google_client_id');
    }

    /**
     * Get Facebook app ID.
     */
    public static function getFacebookAppId(): ?string
    {
        return self::get('facebook_app_id');
    }

    /**
     * Get Facebook app secret.
     */
    public static function getFacebookAppSecret(): ?string
    {
        return self::get('facebook_app_secret');
    }

    /**
     * Get all settings merged with config defaults.
     */
    public static function getMergedSettings(): array
    {
        return self::all(true);
    }

    /**
     * Reset a specific setting to its config default.
     */
    public static function resetToDefault(string $key): ?Setting
    {
        $service = self::instance();

        if (array_key_exists($key, $service->configDefaults)) {
            return self::update([$key => $service->configDefaults[$key]]);
        }

        return null;
    }

    /**
     * Validate and update settings.
     */
    public static function validateAndUpdate(array $data, array $rules = []): array
    {
        $service = self::instance();

        // Basic validation rules if none provided
        if (empty($rules)) {
            $rules = [
                'app_name' => 'nullable|string|max:255',
                'app_email' => 'nullable|email|max:255',
                'site_email' => 'nullable|email|max:255',
                'site_copyright' => 'nullable|string',
                'comment_depth' => 'nullable|integer',
                'enable_edit_comment_window' => ['nullable', 'boolean'],
                'enable_delete_comment_window' => ['nullable', 'boolean'],
                'edit_comment_window_minutes' => ['nullable', 'integer'],
                'delete_comment_window_minutes' => ['nullable', 'integer'],
                'require_login_to_comment' => 'nullable|boolean',
                'require_login_to_like_comment' => 'nullable|boolean',
                'require_login_to_report_comment' => 'nullable|boolean',
                'default_entity_service' => 'nullable|string',
                'default_entity_category' => 'nullable|string',

                'default_services' => ['sometimes', 'nullable', 'array'],
                'default_services.*.name' => ['required', 'string'],
                'default_services.*.id' => ['required', 'numeric'],
                'default_services.*.label' => ['required', 'string'],

                'analytics_driver' => 'nullable|string|in:gtm,ga,none',
                'gtm_id' => 'nullable|string|max:255',
                'ga_measurement_id' => 'nullable|string|max:255',
                'ga_api_secret' => 'nullable|string|max:255',
                'facebook_share_link' => 'nullable|url|max:255',
                'linkedin_share_link' => 'nullable|url|max:255',
                'x_share_link' => 'nullable|url|max:255',
                'instagram_share_link' => 'nullable|url|max:255',
                'tiktok_share_link' => 'nullable|url|max:255',
                'rss_share_link' => 'nullable|url|max:255',
            ];
        }

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->toArray(),
            ];
        }

        try {
            $settings = self::update($validator->validated());

            return [
                'success' => true,
                'data' => $settings,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
