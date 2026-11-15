<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;
use Truvoicer\TruFetcherCore\Services\Page\Block\PageBlock;

class PerspectivesFeedBlock extends PageBlock
{

    public function __construct(
        protected PerspectiveService $perspectiveService
    ) {}
    public function inertiaDependencies(
        PageColumnBlock $block,
        Page $page,
    ): array {

        $validated = $this->getValidator()?->validated();
        $search = (!empty($validated['q'])) ? $validated['q'] : '';
        $tab = (!empty($validated['tab'])) ? $validated['tab'] : 'default';

        return [
            'user' => Inertia::optional(
                function () {
                    $user = $this->getUser();

                    return $user ? $this->userResource::make($user) : null;
                }
            ),

            'perspectives' => $this->perspectiveService->setPaginate(true)->fetchPerspectives(
                $this->getUser(),
                $search ?? '',
                $tab ?? 'default'
            ),
            'filters'      => ['q' => $search, 'tab' => $tab],
        ];
    }

    public function responseData(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $user = $this->getUser();

        $validated = $this->getValidator()?->validated();
        $search = (!empty($validated['q'])) ? $validated['q'] : '';
        $tab = (!empty($validated['tab'])) ? $validated['tab'] : 'default';
        return [
            'user' => $user ? $this->userResource::make($user) : null,
            'perspectives' => $this->perspectiveService->setPaginate(true)->fetchPerspectives(
                $user,
                $search ?? '',
                $tab ?? 'default'
            ),
            'filters'      => ['q' => $search, 'tab' => $tab],
        ];
    }

    public function formRequestRules(
        PageColumnBlock $block,
    ): array {
        return [
            'q' => ['nullable', 'sometimes', 'string'],
            'tab' => ['sometimes', 'string'],
        ];
    }
}
