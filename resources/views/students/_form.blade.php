<div class="grid gap-5 sm:grid-cols-2">
    <x-field label="First name" name="first_name" :value="$student->first_name" required />
    <x-field label="Last name" name="last_name" :value="$student->last_name" required />

    <x-field label="Admission number" name="admission_number" :value="$student->admission_number" hint="Leave blank to generate automatically." />
    <x-field label="Gender" name="gender" type="select" required>
        @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $v => $l)
            <option value="{{ $v }}" @selected(old('gender', $student->gender) === $v)>{{ $l }}</option>
        @endforeach
    </x-field>

    <x-field label="Date of birth" name="date_of_birth" type="date" :value="optional($student->date_of_birth)->toDateString()" />
    <x-field label="Phone" name="phone" type="tel" :value="$student->phone" placeholder="0712 345 678" />

    <x-field label="Class" name="school_class_id" type="select">
        <option value="">— Not assigned —</option>
        @foreach ($classes as $c)
            <option value="{{ $c->id }}" @selected((int) old('school_class_id', $student->school_class_id) === $c->id)>{{ $c->name }}</option>
        @endforeach
    </x-field>
    <x-field label="Enrollment date" name="enrollment_date" type="date" :value="optional($student->enrollment_date)->toDateString()" required />

    <x-field label="Status" name="status" type="select" required>
        @foreach (['active', 'inactive', 'graduated'] as $s)
            <option value="{{ $s }}" @selected(old('status', $student->status) === $s)>{{ ucfirst($s) }}</option>
        @endforeach
    </x-field>
    <x-field label="Address" name="address" type="textarea" :value="$student->address" class="sm:col-span-2" />
</div>
