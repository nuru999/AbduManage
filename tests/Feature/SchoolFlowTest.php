<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_sign_in_and_see_the_dashboard(): void
    {
        User::factory()->create(['email' => 'boss@school.test', 'role' => 'admin']);

        $this->post('/login', ['email' => 'boss@school.test', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk()->assertSee('Active students');
    }

    public function test_admin_can_enroll_a_student_with_generated_admission_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/students', [
            'first_name' => 'Amani', 'last_name' => 'Otieno', 'gender' => 'female',
            'enrollment_date' => today()->toDateString(), 'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('students', ['first_name' => 'Amani']);
        $this->assertStringStartsWith('ADM'.now()->format('Y'), Student::first()->admission_number);
    }

    public function test_teachers_cannot_create_students(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->get('/students/create')->assertForbidden();
        $this->actingAs($teacher)->get('/students')->assertOk();
    }

    public function test_attendance_can_be_saved_and_updated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $class = SchoolClass::create(['name' => 'Grade 1', 'class_code' => 'G1', 'academic_year' => '2026']);
        $student = Student::create([
            'first_name' => 'A', 'last_name' => 'B', 'gender' => 'male', 'admission_number' => 'X1',
            'enrollment_date' => today(), 'school_class_id' => $class->id,
        ]);

        $payload = fn ($status) => [
            'school_class_id' => $class->id, 'date' => today()->toDateString(), 'status' => [$student->id => $status],
        ];

        $this->actingAs($admin)->post('/attendance', $payload('present'))->assertRedirect();
        $this->actingAs($admin)->post('/attendance', $payload('absent'))->assertRedirect();

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', ['student_id' => $student->id, 'status' => 'absent']);
    }
}
