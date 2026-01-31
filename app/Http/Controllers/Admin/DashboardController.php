<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageAttempt;
use App\Models\QuestionPackage;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStudents = User::count();
        $totalPackages = QuestionPackage::count();
        $activePackages = QuestionPackage::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();
        $totalAttempts = PackageAttempt::count();

        $recentAttempts = PackageAttempt::with(['user', 'package'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalPackages',
            'activePackages',
            'totalAttempts',
            'recentAttempts'
        ));
    }
}
