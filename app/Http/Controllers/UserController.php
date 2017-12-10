<?php

namespace App\Http\Controllers;
use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'confirmEmail']
        ]);
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    //用户注册
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        $user = User::create($request->all());
        //发送激活令牌邮件
        $this->sendEmailConfirmationTo($user);
//        Auth::login($user);
        //session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect('/')->with(['success' => '验证邮件已发送到你的注册邮箱上，请注意查收。']);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    //用户编辑
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);
        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = $request->password;
        }
        $user = User::find($id);
        $this->authorize('update', $user);
        $user->update($data);
        session()->flash('success', '修改信息成功~');
        return redirect()->route('users.show', $user->id);
    }

    //删除用户
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜您，激活成功！');
        return redirect()->route('users.show', $user->id);
    }

    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';  //邮件模板
        $data = compact('user');    //给该视图的数据数组
        $from = '976724250@qq.com'; //发送者的邮箱
        $name = 'wlh'; //发送者的名字
        $to = $user->email; //发送到的邮箱;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view, $data, function($message) use($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }
}
