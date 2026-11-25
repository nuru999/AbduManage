<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $classes = $this->availableClasses($request);
        $date = $request->query('date', today()->toDateString());
        $class = $classes->firstWhere('id', (int) $request->query('class')) ?? $classes->first();

        $students = collect();
        $existing = collect();

        if ($class) {
            $students = $class->students()->where('status', 'active')->orderBy('first_name')->get();
            $existing = Attendance::where('attendance_date', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()->keyBy('student_id');
        }

        return view('attendance.index', compact('classes', 'class', 'date', 'students', 'existing'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'array'],
            'status.*' => ['required', Rule::in(Attendance::STATUSES)],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:255'],
        ]);

        $class = $this->availableClasses($request)->firstWhere('id', (int) $data['school_class_id']);
        abort_unless($class, 403);

        // Only accept students that really belong to this class.
        $validIds = $class->students()->pluck('id')->all();

        foreach ($data['status'] as $studentId => $status) {
            if (! in_array((int) $studentId, $validIds, true)) {
                continue;
            }

            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'attendance_date' => $data['date']],
                [
                    'school_class_id' => $class->id,
                    'status' => $status,
                    'remarks' => $data['remarks'][$studentId] ?? null,
                ]
            );
        }

        return redirect()
            ->route('attendance.index', ['class' => $class->id, 'date' => $data['date']])
            ->with('success', 'Attendance saved for '.$class->name.'.');
    }

    private function availableClasses(Request $request)
    {
        $user = $request->user();

        return SchoolClass::where('status', 'active')
            ->when($user->isTeacher(), fn ($q) => $q->where('teacher_id', optional($user->teacher)->id))
            ->orderBy('name')->get();
    }
}
