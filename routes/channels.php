<?php

// routes/channels.php

use Illuminate\Support\Facades\Broadcast;
use Truvoicer\TfPerspectives\Broadcasting\UserChannel;

Broadcast::channel('user.{userId}', UserChannel::class);
