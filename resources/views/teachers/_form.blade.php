<div class="grid gap-5 sm:grid-cols-2">
    <x-field label="First name" name="first_name" :value="$teacher->first_name" required />
    <x-field label="Last name" name="last_name" :value="$teacher->last_name" required />
    <x-field label="Login email" name="email" type="email" :value="$teacher->user?->email" required />
    <x-field :label="$teacher->exists ? 'New password' : 'Password'" name="password" type="password" :required="! $teacher->exists" :hint="$teacher->exists ? 'Leave blank to keep the current password.' : 'At least 8 characters.'" />
    <x-field label="Employee number" name="employee_number" :value="$teacher->employee_number" :required="$teacher->exists" :hint="$teacher->exists ? null : 'Leave blank to generate automatically.'" />
    <x-field label="Phone" name="phone" type="tel" :value="$teacher->phone" />
    <x-field label="Specialization" name="specialization" :value="$teacher->specialization" placeholder="e.g. Mathematics" />
    <x-field label="Employment date" name="employment_date" type="date" :value="optional($teacher->employment_date)->toDateString()" required />
    <x-field label="Status" name="status" type="select" required>
        @foreach (['active', 'inactive'] as $s)<option value="{{ $s }}" @selected(old('status', $teacher->status) === $s)>{{ ucfirst($s) }}</option>@endforeach
    </x-field>
</div>
