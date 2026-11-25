<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Student::exists()) {
            return; // never double-seed
        }

        mt_srand(42);
        $year = now()->year;

        $teacherData = [
            ['Grace', 'Wanjiru', 'Mathematics'],
            ['Peter', 'Otieno', 'English & Literature'],
            ['Amina', 'Hassan', 'Science'],
            ['Daniel', 'Kiptoo', 'Kiswahili'],
        ];

        $teachers = [];
        foreach ($teacherData as $i => [$first, $last, $subject]) {
            $user = User::create([
                'name' => "$first $last",
                'email' => strtolower($first).'@abdumanage.test',
                'password' => 'password',
                'role' => User::ROLE_TEACHER,
            ]);

            $teachers[] = Teacher::create([
                'user_id' => $user->id,
                'employee_number' => 'TCH'.substr((string) $year, 2).str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'first_name' => $first,
                'last_name' => $last,
                'phone' => '07'.mt_rand(10000000, 29999999),
                'specialization' => $subject,
                'employment_date' => now()->subYears(mt_rand(1, 8))->toDateString(),
                'status' => 'active',
            ]);
        }

        $classes = [];
        foreach (['Grade 4 Blue', 'Grade 5 Green', 'Grade 6 Red', 'Grade 7 Gold'] as $i => $name) {
            $classes[] = SchoolClass::create([
                'teacher_id' => $teachers[$i]->id,
                'name' => $name,
                'class_code' => 'G'.($i + 4).strtoupper(Str::substr(explode(' ', $name)[2], 0, 1)).$year,
                'academic_year' => (string) $year,
                'description' => 'Class teacher: '.$teachers[$i]->full_name,
                'status' => 'active',
            ]);
        }

        $firstNames = ['Brian', 'Faith', 'Kevin', 'Mercy', 'Ian', 'Lucy', 'Collins', 'Joy', 'Samuel', 'Naomi', 'Victor', 'Ruth', 'Allan', 'Zawadi', 'Hassan', 'Esther', 'Tony', 'Mwajuma', 'Felix', 'Diana'];
        $lastNames = ['Mwangi', 'Odhiambo', 'Kamau', 'Achieng', 'Njoroge', 'Wafula', 'Chebet', 'Mutua', 'Abdalla', 'Karanja'];

        $n = 0;
        foreach ($classes as $class) {
            for ($k = 0; $k < 8; $k++) {
                $n++;
                $student = Student::create([
                    'school_class_id' => $class->id,
                    'admission_number' => 'ADM'.$year.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
                    'first_name' => $firstNames[($n - 1) % count($firstNames)],
                    'last_name' => $lastNames[($n * 3) % count($lastNames)],
                    'gender' => $n % 2 ? 'male' : 'female',
                    'date_of_birth' => now()->subYears(9 + $class->id)->subDays(mt_rand(0, 300))->toDateString(),
                    'phone' => '07'.mt_rand(10000000, 29999999),
                    'address' => 'Nairobi, Kenya',
                    'enrollment_date' => now()->subMonths(mt_rand(2, 30))->toDateString(),
                    'status' => 'active',
                ]);

                $fee = Fee::create([
                    'student_id' => $student->id,
                    'title' => 'Term 3 School Fees',
                    'amount' => 25000,
                    'due_date' => now()->addDays(mt_rand(5, 40))->toDateString(),
                ]);

                // Mix of fully paid, part paid and unpaid
                $paid = [25000, 15000, 10000, 0][mt_rand(0, 3)];
                if ($paid > 0) {
                    Payment::create([
                        'student_id' => $student->id,
                        'fee_id' => $fee->id,
                        'amount' => $paid,
                        'phone' => '2547'.mt_rand(10000000, 29999999),
                        'status' => Payment::STATUS_COMPLETED,
                        'checkout_request_id' => 'ws_CO_DEMO'.$n,
                        'mpesa_receipt' => 'DEMO'.strtoupper(Str::random(6)).$n,
                        'result_code' => 0,
                        'result_desc' => 'Demo payment',
                        'paid_at' => now()->subDays(mt_rand(0, 20)),
                    ]);
                }

                // Two weeks of weekday attendance
                for ($d = 14; $d >= 0; $d--) {
                    $date = today()->subDays($d);
                    if ($date->isWeekend()) {
                        continue;
                    }
                    $roll = mt_rand(1, 100);
                    Attendance::create([
                        'student_id' => $student->id,
                        'school_class_id' => $class->id,
                        'attendance_date' => $date->toDateString(),
                        'status' => $roll <= 88 ? 'present' : ($roll <= 94 ? 'late' : ($roll <= 98 ? 'absent' : 'excused')),
                    ]);
                }
            }
        }
    }
}
