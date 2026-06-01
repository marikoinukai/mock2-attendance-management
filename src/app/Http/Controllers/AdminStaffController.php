<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        $staffUsers = User::where('is_admin', false)
            ->orderBy('id')
            ->get();

        return view('admin.staff.list', compact('staffUsers'));
    }
}
