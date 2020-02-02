<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use  App\Mail\AdminResetPassword;
//use Illuminate\Support\Facades\Request;
use App\Admin;
use DB;
use Carbon\carbon;
use Mail;
class AdminAuth extends Controller
{
    public function login()
    {
    	return view('admin.login');
    }

    public function dologin()
    {
    	$remember = request('remember')==1 ?true: false;

    	if(admin()->attempt(['email'=>request('email'),'password'=>request('password')],$remember))
    	{
    		return redirect('admin');
    	}else
    	{
    		session()->flash('error',trans('admin.incorrect_login'));
    		return redirect(aurl('login'));
    	}	
    }

    	public function logout()
	    {
	    	auth()->guard('admin')->logout();
	    	return redirect(aurl('login'));
	    }

	    public function forget_password()
	    {
	    	return view('admin.forgetPassword');
	    }
	    public function forget_password_post()
	    {
	    	$admin = Admin::where('email',request('email'))->firstOrFail();
	    	
	    	if($admin)
	    	{
	    		$token = app('auth.password.broker')->createToken($admin);
	    		$data = DB::table('password_resets')->insert([
	    			'email'=>$admin->email,
	    			'token'=>$token,
	    			'created_at'=> carbon::now()
	    		]);
	    		mail::to($admin->email)->send( new AdminResetPassword(['data'=>$admin,'token'=>$token]));
	    		session()->flash('success','Reset Link Is Sent');
	    		return back();


	    	}
	    }
	    public function reset_password($token)
	    {
	    	$check_token = DB::table('password_resets')->where('token',$token)->where('created_at','>',carbon::now()->subHours(2))->first();
	    	if(!empty($check_token)){
	    		return view('admin.resetPassword',['dataReset'=>$check_token]);
	    	}else{
	    		return redirect(aurl('forgetPassword'));
	    	}
	    }
	    
	    public function reset_password_valid($token)
	    {
	      	$this->validate(request(),[
	      		'password'            =>'required|confirmed',
	      		'password_confirmation' =>'required'
	      	]);

	      	$check_token = DB::table('password_resets')->where('token',$token)->where('created_at','>',carbon::now()->subHours(2))->first();

	    	if(!empty($check_token))
	    	{
	    		$admin = Admin::where('email',$check_token->email)->update([
	    			'email'=>$check_token->email,
	    			'password'=>bcrypt(request('password'))
	    		]);
	    		DB::table('password_resets')->where('email',$check_token->email)->delete();
	    		admin()->attempt(['email'=>$check_token->email,'password'=>request('password')],true);
	    		return redirect(aurl());

	    	}
	    	else
	    	{
	    		return redirect(aurl('forgetPassword'));
	    	}
	    }
}
