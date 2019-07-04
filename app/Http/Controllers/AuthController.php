<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Transformers\Json;

class AuthController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
      $this->middleware('auth:api');
  }

  //
  public function authenticate(Request $request){
    $this->validate($request, [
        'email' => 'required|email',
        'password' => 'required'
    ]);

    try {
      $user = User::where('email', $request->email)->firstOrFail();

      if(Hash::check($request->input('password'), $user->password)){
        $apiKey = base64_encode(str_random(40));
        $user->auth_key = $apiKey;
        if($user->save()){
          return response()->json(['status'=>true, 'key'=>$apiKey]);
        }
      } else {
        return Json::exception('Unauthorized', null, 401);
      }
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e){
      return Json::exception('Email Not Found', env('APP_ENV', 'local') == 'local' ? $e : null, 401);
    } catch (\Exception $e) {
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 401);
    }
    
  }

  public function register(Request $request){
    $this->validate($request, [
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required'
    ]);

    // try {
      $user = new User;
      $user->name = $request->name;
      $user->email = $request->email;
      $user->password = Hash::make($request->password);


      if($user->save()){
        return Json::response($user);
      } else {
        return Json::exception('Failed to Register');
      }
    
  }
}
