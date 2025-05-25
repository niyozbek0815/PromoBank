<?php

namespace App\Services;

use App\Jobs\UserUpdateAvatarJob;
use App\Models\UserOtps;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tymon\JWTAuth\Facades\JWTAuth;

class GameStartService {}