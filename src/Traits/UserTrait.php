<?php

namespace Truvoicer\TfPerspectives\Traits;

use Truvoicer\TfPerspectives\Contracts\User\UserInterface;

trait UserTrait
{
    protected ?UserInterface $user = null;

    public function setUser(?UserInterface $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }
}
