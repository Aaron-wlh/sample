<?php

namespace App\Http\Controllers;
use Auth;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function create()
    {
        return view('users.create');
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed'
        ]);
        $user = User::create($request->all());
        Auth::login($user);
        //session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect('/users/' . $user->id)->with(['success' => '欢迎，您将在这里开启一段新的旅程~']);
    }
}
