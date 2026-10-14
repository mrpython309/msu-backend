<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class UserPermissionsController extends Controller
{
    use ApiResponse;
    public function userPermissions(){

        if(!auth()->check()){

            return $this->error('You are not logged in'); 
        }

        return $this->success(auth()->user()
    ->getAllPermissions()
    ->pluck('name')
    ->toArray());


    }
}
