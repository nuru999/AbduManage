<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::with('user')->withCount('schoolClasses')
            ->when($request->query('q'), function ($query, $term) {
                $query->where(fn ($q) => $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('employee_number', 'like', "%{$term}%"));
            })
            ->orderBy('first_name')->paginate(12)->withQueryString();

        return view('teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('teachers.create', [
            'teacher' => new Teacher(['status' => 'active', 'employment_date' => today()]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'employee_number' => ['nullable', 'string', 'max:50', 'unique:teachers,employee_number'],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'employment_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $teacher = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_TEACHER,
            ]);

            return Teacher::create([
                'user_id' => $user->id,
                'employee_number' => $data['employee_number'] ?: 'TCH'.now()->format('y').str_pad((string) ($user->id), 4, '0', STR_PAD_LEFT),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'employment_date' => $data['employment_date'],
                'status' => $data['status'],
            ]);
        });

        return redirect()->route('teachers.index')->with('success', "{$teacher->full_name} added as a teacher.");
    }

    public function show(Teacher $teacher)
    {
        return redirect()->route('teachers.edit', $teacher);
    }

    public function edit(Teacher $teacher)
    {
        return view('teachers.edit', ['teacher' => $teacher->load('user', 'schoolClasses')]);
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'employee_number' => ['required', 'string', 'max:50', Rule::unique('teachers', 'employee_number')->ignore($teacher)],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'employment_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        DB::transaction(function () use ($data, $teacher) {
            $userData = [
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
            ];
            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }
            $teacher->user->update($userData);

            $teacher->update(collect($data)->except(['email', 'password'])->all());
        });

        return redirect()->route('teachers.index')->with('success', 'Teacher updated.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        // Deleting the user cascades to the teacher record; classes keep existing (teacher_id -> null).
        $teacher->user->delete();

        return redirect()->route('teachers.index')->with('success', 'Teacher removed.');
    }
}
