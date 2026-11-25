<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with('teacher')->withCount('students')->orderBy('name')->get();

        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        return view('classes.create', [
            'class' => new SchoolClass(['status' => 'active', 'academic_year' => now()->year]),
            'teachers' => Teacher::where('status', 'active')->orderBy('first_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $class = SchoolClass::create($this->validated($request));

        return redirect()->route('classes.show', $class)->with('success', 'Class created.');
    }

    public function show(SchoolClass $class)
    {
        $class->load('teacher')->loadCount('students');

        return view('classes.show', [
            'class' => $class,
            'students' => $class->students()->orderBy('first_name')->get(),
        ]);
    }

    public function edit(SchoolClass $class)
    {
        return view('classes.edit', [
            'class' => $class,
            'teachers' => Teacher::where('status', 'active')->orderBy('first_name')->get(),
        ]);
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $class->update($this->validated($request, $class));

        return redirect()->route('classes.show', $class)->with('success', 'Class updated.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        if ($class->attendances()->exists()) {
            return back()->with('error', 'This class has attendance records and cannot be deleted. Mark it inactive instead.');
        }

        $class->delete();

        return redirect()->route('classes.index')->with('success', 'Class deleted.');
    }

    private function validated(Request $request, ?SchoolClass $class = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'class_code' => ['required', 'string', 'max:30', Rule::unique('school_classes', 'class_code')->ignore($class)],
            'academic_year' => ['required', 'string', 'max:20'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}
