<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{


    public function getBankAccounts()
    {
        return BankAccount::where('user_id' , Auth::user()->id)->get();
    }
}
