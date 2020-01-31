<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{

    public function login(){ 
		$credentials = request(['email', 'password']);
		
		$rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];
		
		try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Giriş İşlemi Başarısız !']);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token Oluşturulamadı !']);
        }

        return response()->json(compact('token'));
    }
	
	public function update(Request $request)
	{
		if (!$auth = JWTAuth::parseToken())
			return response()->json(['error'=>"Token Error !"]);
		
		$validator = Validator::make($request->all(), [ 
            'email' => 'required|email',
        ]);
		
		if ($validator->fails())
            return response()->json(['error'=>$validator->errors()]);    
		
		$id = auth::id();
		$user = User::find($id);
		
		if(request('name'))
			$user->name = request('name');
		
		if(request('surname'))
			$user->surname = request('surname');
		
		if(request('password'))
			$user->password = bcrypt(request('password'));
		
		if(request('phone'))
			$user->phone = request('phone');
		
        $user->save();
		
		return response()->json(compact('id'));
	}
	
	public function getProfile()
	{
		if (!$auth = JWTAuth::parseToken())
			return response()->json(['error'=>"Token Error !"]);

		$id = auth::id();
		$user = User::find($id);

		return response()->json($user);
	}

    public function register(Request $request) 
    { 
        $validator = Validator::make($request->all(), [ 
            'name' => 'required',
			'surname' => 'required',
            'email' => 'required|email', 
            'password' => 'required', 
            'phone' => 'required|regex:/(0)[0-9]/|not_regex:/[a-z]/|min:10',
        ]);
		if ($validator->fails())
            return response()->json(['error'=>$validator->errors()]);            

		$user = User::create([
            'name'    => $request->name,
            'surname'    => $request->surname,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'phone'    => $request->phone,
        ]);

        return response()->json(compact('user'));
    }
	
	public function logout()
    {
		if (!$auth = JWTAuth::parseToken())
			return response()->json(['error'=>"Token Error !"]);
		
		$token = JWTAuth::getToken();
		JWTAuth::invalidate($token);
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}