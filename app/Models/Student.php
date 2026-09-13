<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Student extends Model
{
    use SoftDeletes;

    protected $table = 'students';

    public $timestamps = true;

    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'course',
        'year_level',
        'component',
        'enrollment_status',
        'grade',
        'numerical_grade',
        'date_of_birth',
        'place_of_birth',
        'sex',
        'contact_number',
        'email',
        'complete_address',
        'deleted_at',
        'rank',
        'specialty',
        'serial_no',
        'middle_name',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'year_level'    => 'integer',
        'deleted_at'    => 'datetime',
    ];

    public static function portalQuery()
    {
        return static::query()
            ->leftJoin('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->leftJoin('sections', 'enrollments.section_id', '=', 'sections.id')
            ->select([
                'students.student_id',
                'students.first_name',
                'students.last_name',
                'students.course',
                'students.component',
                'students.grade',
                'students.date_of_birth',
                'students.place_of_birth',
                'students.sex',
                'students.contact_number',
                'students.email',
                'students.complete_address',
                'students.enrollment_status',
                'sections.section_name as section_code',
                'sections.school_year',
                'enrollments.final_grade',
                'enrollments.status as enrollment_grade_status',
            ]);
    }

    public function toPortalArray(): array
    {
        $name = trim($this->last_name . ', ' . $this->first_name, ', ');

        $grade = $this->grade;
        if (! $grade && isset($this->enrollment_grade_status)) {
            $grade = match ($this->enrollment_grade_status) {
                'Passed' => 'pass',
                'Failed' => 'fail',
                default  => null,
            };
        }

        return [
            'id'           => $this->student_id,
            'student_no'   => $this->student_id,
            'name'         => $name,
            'section_code' => $this->section_code ?? '',
            'program'      => $this->course ?? '',
            'gender'       => $this->sex ?? '',
            'dob'          => $this->date_of_birth?->format('Y-m-d') ?? '',
            'birth_place'  => $this->place_of_birth ?? '',
            'address'      => $this->complete_address ?? '',
            'cell_no'      => $this->contact_number ?? '',
            'email'        => $this->email ?? '',
            'instructor'   => '',
            'school_year'  => $this->school_year ?? '2025-2026',
            'room'         => '',
            'grade'           => $grade,
            'numerical_grade' => $this->numerical_grade ?? null,
        ];
    }

    public static function parseName(string $fullName): array
    {
        $fullName = trim($fullName);
        if (str_contains($fullName, ',')) {
            [$last, $first] = array_map('trim', explode(',', $fullName, 2));

            return ['last_name' => $last, 'first_name' => $first];
        }

        return ['last_name' => $fullName, 'first_name' => ''];
    }

    public static function syncEnrollment(string $studentId, ?string $sectionCode, ?string $grade): void
    {
        if (! $sectionCode || ! DB::getSchemaBuilder()->hasTable('enrollments')) {
            return;
        }

        $section = DB::table('sections')->where('section_name', $sectionCode)->first();
        if (! $section) {
            return;
        }

        $student = DB::table('students')->where('student_id', $studentId)->first();
        if (! $student) {
            return;
        }

        $status = 'Pending';
        if (is_numeric($grade)) {
            $g = floatval($grade);
            if ($g >= 1.0 && $g <= 3.0) {
                $status = 'Passed';
            } elseif ($g > 3.0 && $g <= 5.0) {
                $status = 'Failed';
            } elseif ($g >= 75.0 && $g <= 100.0) {
                $status = 'Passed';
            } elseif ($g >= 50.0 && $g < 75.0) {
                $status = 'Failed';
            }
        } else {
            $cleanGrade = strtolower(trim((string)$grade));
            if (in_array($cleanGrade, ['pass', 'passed', 'pass/fail', 'p']) || str_starts_with($cleanGrade, 'pass')) {
                $status = 'Passed';
            } elseif (in_array($cleanGrade, ['fail', 'failed', 'f']) || str_starts_with($cleanGrade, 'fail')) {
                $status = 'Failed';
            }
        }

        // Sync both student record and enrollment status/grade/component
        $studentUpdates = [];
        if (empty(trim((string)$student->component)) && !empty($section->component)) {
            $studentUpdates['component'] = strtoupper(trim($section->component));
        }
        $gradeEnum = $status === 'Passed' ? 'pass' : ($status === 'Failed' ? 'fail' : null);
        if ($gradeEnum) {
            $studentUpdates['grade'] = $gradeEnum;
        }
        if (is_numeric($grade)) {
            $studentUpdates['numerical_grade'] = floatval($grade);
        }
        if (!empty($studentUpdates)) {
            DB::table('students')->where('id', $student->id)->update($studentUpdates);
        }

        $existing = DB::table('enrollments')
            ->where('student_id', $student->id)
            ->where('section_id', $section->id)
            ->first();

        $data = [
            'status'     => $status,
            'updated_at' => now(),
        ];
        if (is_numeric($grade)) {
            $data['final_grade'] = floatval($grade);
        }

        if ($existing) {
            DB::table('enrollments')->where('id', $existing->id)->update($data);
        } else {
            $data['student_id'] = $student->id;
            $data['section_id'] = $section->id;
            $data['created_at'] = now();
            DB::table('enrollments')->insert($data);
        }
    }
}
