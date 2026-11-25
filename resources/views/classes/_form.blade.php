<div class="grid gap-5 sm:grid-cols-2">
    <x-field label="Class name" name="name" :value="$class->name" placeholder="e.g. Grade 5 Green" required />
    <x-field label="Class code" name="class_code" :value="$class->class_code" placeholder="e.g. G5G2026" required />
    <x-field label="Academic year" name="academic_year" :value="$class->academic_year" required />
    <x-field label="Class teacher" name="teacher_id" type="select">
        <option value="">— Unassigned —</option>
        @foreach ($teachers as $t)<option value="{{ $t->id }}" @selected((int) old('teacher_id', $class->teacher_id) === $t->id)>{{ $t->full_name }}</option>@endforeach
    </x-field>
    <x-field label="Status" name="status" type="select" required>
        @foreach (['active', 'inactive'] as $s)<option value="{{ $s }}" @selected(old('status', $class->status) === $s)>{{ ucfirst($s) }}</option>@endforeach
    </x-field>
    <x-field label="Description" name="description" type="textarea" :value="$class->description" class="sm:col-span-2" />
</div>
