<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/ShoeController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsShoes;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\PerspectiveShoe;
use Truvoicer\TfPerspectives\Models\ShoeDiscussion;
use Truvoicer\TfPerspectives\Notifications\ShoeAdded;
use Truvoicer\TfPerspectives\Notifications\ShoeDiscussed;
use Truvoicer\TfPerspectives\Services\ShoeService;

class ShoeController extends Controller
{
    use TransformsShoes;

    public function __construct(private ShoeService $shoes)
    {
    }

    /** POST /perspectives/{perspective}/shoes */
    public function store(Request $request, Perspective $perspective)
    {
        $data = $request->validate([
            'voice'        => ['required', 'string', 'max:120'],
            'body'         => ['required', 'string', 'min:10', 'max:2000'],
            'mood'         => ['nullable', 'string', 'max:32'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $shoe = $this->shoes->addShoe(
            $perspective,
            $request->user()->id,
            $data['voice'],
            $data['body'],
            $data['mood'] ?? null,
            (bool) ($data['is_anonymous'] ?? false),
        );
        // Notify the perspective author
        if ($perspective->user_id !== $request->user()->id) {
            $perspective->author->notify(new ShoeAdded($shoe));
        }
        return redirect()->back();
    }

    /** PUT /shoes/{shoe} */
    public function update(Request $request, PerspectiveShoe $perspectiveShoe)
    {
        Gate::authorize('update', $perspectiveShoe);

        $data = $request->validate([
            'voice'        => ['required', 'string', 'max:120'],
            'body'         => ['required', 'string', 'min:10', 'max:2000'],
            'mood'         => ['nullable', 'string', 'max:32'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $perspectiveShoe->update([
            'voice'        => $data['voice'],
            'body'         => $data['body'],
            'mood'         => $data['mood'] ?? null,
            'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
        ]);

        return redirect()->back();
    }

    /** DELETE /shoes/{shoe} */
    public function destroy(Perspective $perspective, PerspectiveShoe $perspectiveShoe)
    {
        Gate::authorize('delete', $perspectiveShoe);

        $this->shoes->removeShoe($perspectiveShoe);

        return redirect()->back();
    }

    /** POST /shoes/{shoe}/discussions */
    public function discuss(Perspective $perspective, PerspectiveShoe $perspectiveShoe, Request $request)
    {
        $data = $request->validate([
            'body'         => ['required', 'string', 'min:2', 'max:1000'],
            'parent_id'    => ['nullable', 'integer', 'exists:shoe_discussions,id'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $discussion = $this->shoes->addDiscussion(
            $perspectiveShoe,
            $request->user()->id,
            $data['body'],
            $data['parent_id'] ?? null,
            (bool) ($data['is_anonymous'] ?? false),
        );

        // Notify the shoe author (unless it's a self-reply)
        if ($perspectiveShoe->user_id !== $request->user()->id) {
            $perspectiveShoe->author->notify(
                new ShoeDiscussed($discussion, $perspectiveShoe)
            );
        }
        return redirect()->back();
    }

    /** DELETE /discussions/{discussion} */
    public function destroyDiscussion(Perspective $perspective, ShoeDiscussion $discussion)
    {
        Gate::authorize('delete', $discussion);

        $discussion->delete();

        return redirect()->back();
    }

    /**
     * Reserved for the ranking feature. Not routed yet — the UI is a
     * follow-up, but the endpoint shape is here so nothing has to move
     * when you enable it.
     */
    public function markImpact(Perspective $perspective,  PerspectiveShoe $perspectiveShoe, Request $request)
    {
        Gate::authorize('markImpact', $perspectiveShoe);

        $data = $request->validate([
            'impact_rank'     => ['nullable', 'integer', 'min:1', 'max:255'],
            'changed_my_view' => ['nullable', 'boolean'],
        ]);

        $this->shoes->setImpact(
            $perspectiveShoe,
            $data['impact_rank'] ?? null,
            (bool) ($data['changed_my_view'] ?? false),
        );

        return redirect()->back();
    }
}
