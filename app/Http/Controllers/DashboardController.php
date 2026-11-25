<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = today();

        $presentToday = Attendance::whereDate('attendance_date', $today)->whereIn('status', ['present', 'late'])->count();
        $markedToday = Attendance::whereDate('attendance_date', $today)->count();

        // Last 7 days attendance rate for the mini chart
        $trend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = today()->subDays($daysAgo);
            $total = Attendance::whereDate('attendance_date', $date)->count();
            $present = Attendance::whereDate('attendance_date', $date)->whereIn('status', ['present', 'late'])->count();

            return [
                'label' => $date->format('D'),
                'rate' => $total ? round($present / $total * 100) : 0,
                'total' => $total,
            ];
        });

        $stats = [
            'students' => Student::where('status', 'active')->count(),
            'teachers' => Teacher::where('status', 'active')->count(),
            'classes' => SchoolClass::where('status', 'active')->count(),
            'attendance_rate' => $markedToday ? round($presentToday / $markedToday * 100) : null,
        ];

        $finance = null;
        if ($user->isAdmin()) {
            $collected = (int) Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount');
            $billed = (int) Fee::sum('amount');

            $finance = [
                'collected' => $collected,
                'billed' => $billed,
                'outstanding' => max($billed - $collected, 0),
                'this_month' => (int) Payment::where('status', Payment::STATUS_COMPLETED)
                    ->where('paid_at', '>=', now()->startOfMonth())->sum('amount'),
                'pending' => Payment::where('status', Payment::STATUS_PENDING)->count(),
                'recent' => Payment::with('student')->latest()->take(6)->get(),
            ];
        }

        $classes = SchoolClass::withCount('students')->with('teacher')
            ->where('status', 'active')->orderByDesc('students_count')->take(5)->get();

        return view('dashboard', compact('stats', 'finance', 'trend', 'classes'));
    }
}
