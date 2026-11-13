<?php
// packages/truvoicer/tf-perspectives/src/Http/Responses/LogoutResponse.php

namespace Truvoicer\TfPerspectives\Http\Responses;

use Truvoicer\TruFetcherCore\Fortify\FortifyBindings;
use Truvoicer\TruFetcherCore\Http\Responses\LogoutResponse as ResponsesLogoutResponse;

class LogoutResponse extends ResponsesLogoutResponse
{
    public function toResponse($request)
    {
        return FortifyBindings::resolveResponseByRequestContext($this->responseMapping($request), $request);
    }

    public function responseMapping($request)
    {
        return [
            ...parent::responseMapping($request),
            'perspectives' => function ($request) {
                if ($request->wantsJson() && ! $request->header('X-Inertia')) {
                    return response()->json(['message' => 'Logged out.']);
                }

                return redirect()->route('perspectives.login');
            },
        ];
    }
}
