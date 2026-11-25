<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with('schoolClass')
            ->search($request->query('q'))
            ->when($request->query('class'), fn ($q, $class) => $q->where('school_class_id', $class))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('first_name')
            ->paginate(12)
            ->withQueryString();

        return view('students.index', [
            'students' => $students,
            'classes' => SchoolClass::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('students.create', [
            'student' => new Student(['status' => 'active', 'enrollment_date' => today()]),
            'classes' => SchoolClass::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['admission_number'] = $data['admission_number'] ?: $this->nextAdmissionNumber();

        $student = Student::create($data);

        return redirect()->route('students.show', $student)->with('success', 'Student enrolled successfully.');
    }

    public function show(Student $student)
    {
        $student->load('schoolClass.teacher', 'guardian');

        $counts = $student->attendances()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $totalDays = $counts->sum();
        $attendance = [
            'counts' => $counts,
            'rate' => $totalDays ? round((($counts['present'] ?? 0) + ($counts['late'] ?? 0)) / $totalDays * 100) : null,
        ];

        return view('students.show', [
            'student' => $student,
            'attendance' => $attendance,
            'fees' => $student->fees()->latest()->get(),
            'payments' => $student->payments()->latest()->take(8)->get(),
            'recentAttendance' => $student->attendances()->latest('attendance_date')->take(10)->get(),
        ]);
    }

    public function edit(Student $student)
    {
        return view('students.edit', [
            'student' => $student,
            'classes' => SchoolClass::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $this->validated($request, $student);
        $data['admission_number'] = $data['admission_number'] ?: $student->admission_number;

        $student->update($data);

        return redirect()->route('students.show', $student)->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student removed.');
    }

    private function validated(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'admission_number' => ['nullable', 'string', 'max:50', Rule::unique('students', 'admission_number')->ignore($student)],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'enrollment_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated'])],
        ]);
    }

    private function nextAdmissionNumber(): string
    {
        $prefix = 'ADM'.now()->format('Y');
        $last = Student::where('admission_number', 'like', $prefix.'%')->orderByDesc('admission_number')->value('admission_number');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
