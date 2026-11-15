<?php

namespace Truvoicer\TfPerspectives\Enums\Block;

use Truvoicer\TfDbReadCore\Traits\Enum\EnumUtilityTrait;
use Truvoicer\TfDbReadCore\Traits\Enum\SelectDataTrait;

enum Block: string
{
    use EnumUtilityTrait, SelectDataTrait;

    case PERSPECTIVES_CATEGORY = 'perspectives_category';
    case PERSPECTIVES_FEATURED = 'perspectives_featured';
    case PERSPECTIVES_FEED = 'perspectives_feed';
    case PERSPECTIVES_STATS = 'perspectives_stats';
    case PERSPECTIVES_THREAD = 'perspectives_thread';

    public function label(): string
    {
        return match ($this) {
            self::PERSPECTIVES_CATEGORY => 'Perspectives Category',
            self::PERSPECTIVES_FEATURED => 'Perspectives Featured',
            self::PERSPECTIVES_FEED => 'Perspectives Feed',
            self::PERSPECTIVES_STATS => 'Perspectives Stats',
            self::PERSPECTIVES_THREAD => 'Perspectives Thread',
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
        };

        return [...$this->sharedSchema(), ...$schema];
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
            self::PERSPECTIVES_THREAD => [
                'page_number' => ['sometimes', 'integer', 'min:1'],
                'page_size' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['sometimes', 'string'],
                'sort_order' => ['sometimes', 'in:asc,desc'],
                'id' => ['sometimes', 'string'],
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
            self::PERSPECTIVES_CATEGORY => []
        };
    }

    public static function getData(): array
    {
        return array_map(
            fn ($case) => [
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
        ];
    }

    public function sharedValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'string'],
        ];
    }
}
