<?php

namespace App\Http\Controllers;

use App\Models\TourLegUser;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class UserController extends Controller
{
    public function delete(int $user_id, Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader) return response(null, 401);
        if ($authHeader != config('connect.delete_token')) return response(null, 401);
        $user = User::find($user_id);
        if (!$user) return response(null, 200);
        try {
            TourLegUser::where('user_id', $user_id)->delete();
            User::where('id', $user_id)->delete();
            return response(null, 200);
        } catch (\Exception $e) {}
        return response(null, 500);
    }
}
