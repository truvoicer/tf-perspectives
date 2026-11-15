<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TfPerspectives\Services\PerspectiveService;
use Truvoicer\TruFetcherCore\Contracts\Override;
use Truvoicer\TruFetcherCore\Http\Resources\Page\PageResource;
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
        $data = $block->getData();
        $threadPage = null;
        if (!empty($data['thread_page'])) {
            $page = Override::resolveClass(Page::class)::find($data['thread_page']);
            if ($page) {
                $threadPage = Override::resolveClass(PageResource::class)::make($page);
            }
        }

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
            'threadPagePermalink' => $threadPage ? $threadPage->getPermalink() : null,
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

    public function parseData(
        PageColumnBlock $block,
    ): array {
        $data = parent::parseData($block);

        if (!empty($data['thread_page'])) {
            $page = Override::resolveClass(Page::class)::find($data['thread_page']);
            if ($page) {
                $data['thread_page'] = Override::resolveClass(PageResource::class)::make($page);
            } else {
                $data['thread_page'] = null;
            }
        }

        return $data;
    }

}
