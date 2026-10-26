<?php
// packages/truvoicer/tf-perspectives/src/Http/Responses/LoginResponse.php

namespace Truvoicer\TfPerspectives\Http\Responses;

use Truvoicer\TruFetcherCore\Fortify\FortifyBindings;
use Truvoicer\TruFetcherCore\Http\Responses\LoginResponse as ResponsesLoginResponse;

class LoginResponse extends ResponsesLoginResponse
{
    public function toResponse($request)
    {
        return FortifyBindings::resolveResponseByRequestContext(self::responseMapping($request), $request);
    }

    public static function responseMapping($request)
    {
        return [
            ...parent::responseMapping($request),
            'perspectives' => function ($request) {

                if ($request->wantsJson() && ! $request->header('X-Inertia')) {
                    return response()->json(['message' => 'Logged in successfully.']);
                }

                return redirect()->route('perspectives.index');
            },
        ];
    }
}
