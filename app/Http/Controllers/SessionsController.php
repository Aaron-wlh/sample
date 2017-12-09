<?php

namespace App\Http\Controllers;
use Auth;
use Illuminate\Http\Request;

class SessionsController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest', ['only' => ['create']]);
    }

    public function create()
    {
        return view('sessions.create');
    }

    //用户登录
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->has('remember'))) {
            //Auth::attempt($credentials);
            session()->flash('success', '欢迎回来！');
            return redirect()->intended(route('users.show', Auth::id()));
        } else {
            session()->flash('danger',  '很抱歉，您的邮箱和密码不匹配');
            return back();
        }
    }

    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出');
        return redirect()->route('login');
    }
}
