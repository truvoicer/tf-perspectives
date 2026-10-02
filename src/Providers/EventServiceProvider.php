<?php

namespace Truvoicer\TfPerspectives\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Truvoicer\TfPerspectives\Events\Application\ApplicationStartFailed;
use Truvoicer\TfPerspectives\Events\Application\ApplicationStartSuccess;
use Truvoicer\TfPerspectives\Events\Application\ApplicationSubmissionFailed;
use Truvoicer\TfPerspectives\Events\Application\ApplicationSubmitted;
use Truvoicer\TfPerspectives\Events\Email\EmailSubscriptionCreated;
use Truvoicer\TfPerspectives\Events\User\UserLoggedOut;
use Truvoicer\TfPerspectives\Events\User\UserLoginFailed;
use Truvoicer\TfPerspectives\Events\User\UserLoginSucceeded;
use Truvoicer\TfPerspectives\Events\User\UserRegistered;
use Truvoicer\TfPerspectives\Events\User\UserRegistrationFailed;
use Truvoicer\TfPerspectives\Listeners\Email\AddSubscriberToHubSpot;
use Truvoicer\TfPerspectives\Listeners\Email\EnableUserEmailSubscribePreference;
use Truvoicer\TfPerspectives\Listeners\Email\NotifyUserSubscriptionCreated;
use Truvoicer\TfPerspectives\Listeners\Track\Application\Email\NotifyAdminApplicationSubmitted;
use Truvoicer\TfPerspectives\Listeners\Track\Application\Email\NotifyUserApplicationSubmitted;
use Truvoicer\TfPerspectives\Listeners\Track\Application\TrackApplicationSubmitted;
use Truvoicer\TfPerspectives\Listeners\Track\Application\TrackFailedApplicationStart;
use Truvoicer\TfPerspectives\Listeners\Track\Application\TrackFailedApplicationSubmission;
use Truvoicer\TfPerspectives\Listeners\Track\Application\TrackSuccessfulApplicationStart;
use Truvoicer\TfPerspectives\Listeners\Track\Email\TrackEmailSubscriptionCreated;
use Truvoicer\TfPerspectives\Listeners\Track\User\TrackFailedLogin;
use Truvoicer\TfPerspectives\Listeners\Track\User\TrackFailedUserRegistration;
use Truvoicer\TfPerspectives\Listeners\Track\User\TrackLoggedOut;
use Truvoicer\TfPerspectives\Listeners\Track\User\TrackSuccessfulLogin;
use Truvoicer\TfPerspectives\Listeners\Track\User\TrackUserRegistered;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        EmailSubscriptionCreated::class => [
            TrackEmailSubscriptionCreated::class,
            EnableUserEmailSubscribePreference::class,
            AddSubscriberToHubSpot::class,
            NotifyUserSubscriptionCreated::class,
        ],

        // -----------------
        // Auth / user lifecycle
        // -----------------
        UserLoggedOut::class => [
            TrackLoggedOut::class,
        ],

        UserLoginFailed::class => [
            TrackFailedLogin::class,
        ],

        UserLoginSucceeded::class => [
            TrackSuccessfulLogin::class,
        ],

        UserRegistered::class => [
            TrackUserRegistered::class,
        ],

        UserRegistrationFailed::class => [
            TrackFailedUserRegistration::class,
        ],

        ApplicationSubmitted::class => [
            TrackApplicationSubmitted::class,
            NotifyUserApplicationSubmitted::class,
            NotifyAdminApplicationSubmitted::class,
        ],

        ApplicationStartFailed::class => [
            TrackFailedApplicationStart::class,
        ],

        ApplicationStartSuccess::class => [
            TrackSuccessfulApplicationStart::class,
        ],

        ApplicationSubmissionFailed::class => [
            TrackFailedApplicationSubmission::class,
        ],

    ];
}
