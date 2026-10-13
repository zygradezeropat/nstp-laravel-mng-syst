<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $table = 'sections';

    public $timestamps = true;

    protected $fillable = [
        'section_name',
        'component',
        'school_year',
        'semester',
        'room',
        'schedule',
        'status',
        'instructor_id',
    ];

    /** The instructor assigned to this section. */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(PortalUser::class, 'instructor_id');
    }

    /** Enrollments in this section. */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'section_id');
    }

    /** Students enrolled in this section. */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments', 'section_id', 'student_id');
    }

    /** Get the instructor's name, or fallback to the ROTC officer if component is ROTC. */
    public function getInstructorNameAttribute(): string
    {
        if ($this->instructor) {
            return $this->instructor->name;
        }

        if ($this->component === 'ROTC') {
            $officer = Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
                ->where('enrollments.section_id', $this->id)
                ->where('students.component', 'ROTC')
                ->whereNotNull('students.rank')
                ->where('students.rank', '!=', '')
                ->select('students.first_name', 'students.last_name', 'students.rank')
                ->first();

            if ($officer) {
                return trim(ucfirst($officer->rank) . '. ' . $officer->first_name . ' ' . $officer->last_name);
            }
        }

        return 'TBA';
    }

    /** Count of students enrolled in this section via the students+enrollments tables. */
    public function studentCount(): int
    {
        // sections link to students via the section_name / section_code column
        return \DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->join('sections as s2', 's2.id', '=', 'enrollments.section_id')
            ->where('s2.id', $this->id)
            ->count();
    }

    /** Simple fallback using section_code on the students table. */
    public function directStudentCount(): int
    {
        return \DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->join('sections as s2', 's2.id', '=', 'enrollments.section_id')
            ->where('s2.section_name', $this->section_name)
            ->count();
    }
}
