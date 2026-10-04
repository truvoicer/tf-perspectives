<?php
// app/Http/Resources/PerspectiveResource.php

namespace Truvoicer\TfPerspectives\Http\Resources;

use Truvoicer\TfPerspectives\Models\Perspective;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerspectiveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'parent_id'      => $this->parent_id,
            'root_id'        => $this->root_id,
            'depth'          => $this->depth,
            'voice'          => $this->voice,
            'body'           => $this->body,
            'author'         => $this->whenLoaded('author', fn () => [
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ]),
            'empathy_count'  => (int) ($this->empathies_count ?? 0),
            'children_count' => (int) ($this->children_count ?? 0),
            'has_empathized' => (bool) ($this->has_empathized ?? false),
            'can_branch'     => $this->depth < Perspective::MAX_DEPTH,
            'created_at'     => optional($this->created_at)->toIso8601String(),
        ];
    }
}
