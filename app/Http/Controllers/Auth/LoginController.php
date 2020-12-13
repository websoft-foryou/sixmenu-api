<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\CaptureIpTrait;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use DB;
use ReallySimpleJWT\Token;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        login as protected auth_logins;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectAfterLogout = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Login
     */
    public function login_user(Request $request) {

        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $login_result = Auth::guard()->attempt($request->only('email', 'password'), true);

        if ($login_result) {

            $user = DB::table('users')->where('email', $request->email)->where('email_verified_at', '!=', null)->first();
            if (empty($user))
                return response()->json(['success' => false, 'result' => 'Your email was not verified yet.']);
            else {
                $client_info = new CaptureIpTrait();
                $user_ipaddress = $client_info->getClientIp();
                $user_country = $client_info->getClientCountry();
                $user_device = $client_info->getDevice();
                $user_platform = $client_info->getPlatform();
                $user_browser = $client_info->getBrowser();

                DB::table('user_logins')->insert(['user_id'=>$user->id, 'ip_address'=>$user_ipaddress, 'country' => $user_country, 'device' => $user_device,
                    'platform' => $user_platform, 'browser'=>$user_browser, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s') ]);

                $secret = '!sixMenu*atarit$915*';
                $expiration = time() + 3600;
                $issuer = $user_ipaddress;
                $token = Token::create($user->id, $secret, $expiration, $issuer);

                DB::table('users')->where('email', $request->email)->update(['api_token'=> $token]);
                return response()->json(['success' => true, 'result' => ['token' => $token, 'membership' => $user->membership] ]);
            }
        }
        else
            return response()->json(['success' => false, 'result' => 'The email or passord is wrong. Please try again']);
    }

    /**
     * Logout, Clear Session, and Return.
     *
     * @return void
     */
    public function logout()
    {
        // $user = Auth::user();
        // Log::info('User Logged Out. ', [$user]);
        Auth::logout();
        Session::flush();

        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }

}
