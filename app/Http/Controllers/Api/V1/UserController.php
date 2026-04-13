<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $query = User::query();
        $query->with('posts');
        return UserResource::collection($query->paginate());
    }
}
