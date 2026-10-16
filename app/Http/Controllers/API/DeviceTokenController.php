<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeviceToken;
use App\Traits\ApiResponse;

class DeviceTokenController extends Controller
{
      use ApiResponse;
    public function updateDeviceToken(Request $request){

        $request->validate([
            'device_token' => 'required',
             'user_id' => 'required|exists:users,id',
             'type' => 'required|in:android,ios'
        ]);

        $device_token = DeviceToken::where('user_id', $request->user_id)->first();

        if($device_token){
            $device_token->update([
                'device_token' => $request->device_token,
                'type' => $request->type
            ]);
        }else{
            DeviceToken::create([
                'device_token' => $request->device_token,
                'user_id' => $request->user_id,
                'type' => $request->type
            ]);
        }

         return $this->success('Device token updated successfully');

    }
}
