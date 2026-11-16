<?php

namespace Truvoicer\TfPerspectives\Enums\Block;

use Truvoicer\TfDbReadCore\Traits\Enum\EnumUtilityTrait;
use Truvoicer\TfDbReadCore\Traits\Enum\SelectDataTrait;

enum Block: string
{
    use EnumUtilityTrait, SelectDataTrait;

    case PERSPECTIVES_CATEGORY = 'perspectives_category';
    case PERSPECTIVES_CATEGORY_THREAD = 'perspectives_category_thread';
    case PERSPECTIVES_FEATURED = 'perspectives_featured';
    case PERSPECTIVES_FEED = 'perspectives_feed';
    case PERSPECTIVES_STATS = 'perspectives_stats';
    case PERSPECTIVES_THREAD = 'perspectives_thread';
    case PERSPECTIVES_SEARCH = 'perspectives_search';
    case PERSPECTIVES_SEARCH_RESULTS = 'perspectives_search_results';
    case PERSPECTIVES_BREADCRUMBS = 'perspectives_breadcrumbs';

    public function label(): string
    {
        return match ($this) {
            self::PERSPECTIVES_CATEGORY => 'Perspectives Category',
            self::PERSPECTIVES_CATEGORY_THREAD => 'Perspectives Category Thread',
            self::PERSPECTIVES_FEATURED => 'Perspectives Featured',
            self::PERSPECTIVES_FEED => 'Perspectives Feed',
            self::PERSPECTIVES_STATS => 'Perspectives Stats',
            self::PERSPECTIVES_THREAD => 'Perspectives Thread',
            self::PERSPECTIVES_SEARCH => 'Perspectives Search',
            self::PERSPECTIVES_SEARCH_RESULTS => 'Perspectives Search Results',
            self::PERSPECTIVES_BREADCRUMBS => 'Perspectives Breadcrumbs',
        };
    }

    public function schema(): array
    {
        $schema = match ($this) {
            self::PERSPECTIVES_CATEGORY => [
                'page_number' => 1,
                'page_size' => 10,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'id' => '',

            ],
            self::PERSPECTIVES_CATEGORY_THREAD => [
                'page_number' => 1,
                'page_size' => 10,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'id' => '',
            ],
            self::PERSPECTIVES_FEATURED => [
                'page_number' => 1,
                'page_size' => 10,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'id' => '',
            ],
            self::PERSPECTIVES_FEED => [
                'page_number' => 1,
                'page_size' => 10,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'id' => '',
            ],
            self::PERSPECTIVES_STATS => [
                'page_number' => 1,
                'page_size' => 10,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'id' => '',
            ],
            self::PERSPECTIVES_THREAD => [
                'page_number' => 1,
                'page_size' => 10,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'id' => '',
            ],
            self::PERSPECTIVES_SEARCH => [
                'page_number' => 1,
                'page_size' => 10,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'id' => '',
                'show_search_results' => false,
            ],
            self::PERSPECTIVES_SEARCH_RESULTS => [
                'page_number' => 1,
                'page_size' => 10,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'id' => '',
            ],
            self::PERSPECTIVES_BREADCRUMBS => [
                'id' => '',
            ],
        };

        return [...$this->sharedSchema(), ...$schema];
    }

    public function validationRules(): array
    {
        $rules = match ($this) {
            self::PERSPECTIVES_FEATURED => [
                'page_number' => ['sometimes', 'integer', 'min:1'],
                'page_size' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['sometimes', 'string'],
                'sort_order' => ['sometimes', 'in:asc,desc'],
                'id' => ['sometimes', 'string'],
                ...$this->stylingValidationRules(),
            ],
            self::PERSPECTIVES_FEED => [
                'page_number' => ['sometimes', 'integer', 'min:1'],
                'page_size' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['sometimes', 'string'],
                'sort_order' => ['sometimes', 'in:asc,desc'],
                'id' => ['sometimes', 'string'],
                ...$this->stylingValidationRules(),
            ],
            self::PERSPECTIVES_STATS => [
                'page_number' => ['sometimes', 'integer', 'min:1'],
                'page_size' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['sometimes', 'string'],
                'sort_order' => ['sometimes', 'in:asc,desc'],
                'id' => ['sometimes', 'string'],
                ...$this->stylingValidationRules(),
            ],
            self::PERSPECTIVES_CATEGORY => [
                'page_number' => ['sometimes', 'integer', 'min:1'],
                'page_size' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['sometimes', 'string'],
                'sort_order' => ['sometimes', 'in:asc,desc'],
                'id' => ['sometimes', 'string'],
                ...$this->stylingValidationRules(),
            ],
            self::PERSPECTIVES_CATEGORY_THREAD => [
                'page_number' => ['sometimes', 'integer', 'min:1'],
                'page_size' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['sometimes', 'string'],
                'sort_order' => ['sometimes', 'in:asc,desc'],
                'id' => ['sometimes', 'string'],
                ...$this->stylingValidationRules(),
            ],
            self::PERSPECTIVES_THREAD => [
                'page_number' => ['sometimes', 'integer', 'min:1'],
                'page_size' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['sometimes', 'string'],
                'sort_order' => ['sometimes', 'in:asc,desc'],
                'id' => ['sometimes', 'string'],
                ...$this->stylingValidationRules(),
            ],
            self::PERSPECTIVES_SEARCH => [
                'page_number' => ['sometimes', 'integer', 'min:1'],
                'page_size' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['sometimes', 'string'],
                'sort_order' => ['sometimes', 'in:asc,desc'],
                'id' => ['sometimes', 'string'],
                'show_search_results' => ['sometimes', 'boolean'],
                ...$this->stylingValidationRules(),
            ],
            self::PERSPECTIVES_SEARCH_RESULTS => [
                'page_number' => ['sometimes', 'integer', 'min:1'],
                'page_size' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['sometimes', 'string'],
                'sort_order' => ['sometimes', 'in:asc,desc'],
                'id' => ['sometimes', 'string'],
                ...$this->stylingValidationRules(),
            ],
            self::PERSPECTIVES_BREADCRUMBS => [
                ...$this->stylingValidationRules(),
            ],
        };

        return [...$this->sharedValidationRules(), ...$rules];
    }

    public function pageColumnBlockLoads(): array
    {
        return match ($this) {
            self::PERSPECTIVES_FEATURED,
            self::PERSPECTIVES_FEED,
            self::PERSPECTIVES_STATS,
            self::PERSPECTIVES_THREAD,
            self::PERSPECTIVES_CATEGORY_THREAD => [],
            self::PERSPECTIVES_SEARCH,
            self::PERSPECTIVES_SEARCH_RESULTS => [],
            self::PERSPECTIVES_CATEGORY => [],
            self::PERSPECTIVES_BREADCRUMBS => []
        };
    }

    public static function getData(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'schema' => $case->schema(),
            ],
            self::cases()
        );
    }

    public function sharedSchema(): array
    {
        return [
            'title' => '',
            'thread_page' => null,
            'feed_page' => null,
            'search_page' => null,
            'search_results_page' => null,
            'categories_page' => null,
            'category_thread_page' => null,
        ];
    }

    public function sharedValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'string'],
            'search_page' => ['sometimes', 'numeric', 'exists:pages,id'],
            'search_results_page' => ['sometimes', 'numeric', 'exists:pages,id'],
            'thread_page' => ['sometimes', 'numeric', 'exists:pages,id'],
            'feed_page' => ['sometimes', 'numeric', 'exists:pages,id'],
            'categories_page' => ['sometimes', 'numeric', 'exists:pages,id'],
            'category_thread_page' => ['sometimes', 'numeric', 'exists:pages,id'],
        ];
    }

    /**
     * Get the validation rules for styling fields
     */
    private function stylingValidationRules(): array
    {
        $colorRule = ['sometimes', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'];
        $spacingRule = ['sometimes', 'string', 'regex:/^(\d+px)(\s+(\d+px)){0,3}$/'];
        $shadowRule = ['sometimes', 'string', 'nullable'];

        return [
            // Background colors
            'background_color' => $colorRule,
            'light_background_color' => $colorRule,
            'dark_background_color' => $colorRule,

            // Border colors
            'border_color' => $colorRule,
            'light_border_color' => $colorRule,
            'dark_border_color' => $colorRule,

            // Border properties
            'border_width' => ['sometimes', 'integer', 'min:0', 'max:20'],
            'border_style' => ['sometimes', 'in:solid,dashed,dotted,double,groove,ridge,inset,outset,none'],
            'border_radius' => ['sometimes', 'string', 'regex:/^(\d+px)(\s+(\d+px)){0,3}$/'],

            // Box shadows
            'box_shadow' => $shadowRule,
            'light_box_shadow' => $shadowRule,
            'dark_box_shadow' => $shadowRule,

            // Spacing
            'padding' => $spacingRule,
            'margin' => $spacingRule,
        ];
    }

}
