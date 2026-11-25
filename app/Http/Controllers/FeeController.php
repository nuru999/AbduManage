<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function store(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'integer', 'min:1', 'max:10000000'],
            'due_date' => ['nullable', 'date'],
        ]);

        $student->fees()->create($data);

        return back()->with('success', 'Fee added to '.$student->full_name.'.');
    }

    public function destroy(Fee $fee): RedirectResponse
    {
        $fee->delete();

        return back()->with('success', 'Fee removed.');
    }
}
