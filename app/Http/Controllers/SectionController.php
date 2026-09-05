<?php

namespace App\Http\Controllers;

use App\Models\PortalNotification;
use App\Models\PortalUser;
use App\Models\Section;
use App\Models\Student;
use App\Support\DashboardMetricsVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    /**
     * GET /api/sections
     *
     * Returns all sections with instructor name and student count.
     */
    public function index(): JsonResponse
    {
        $sections = Section::with('instructor')->get();

        $rows = $sections->map(function (Section $s) {

            $studentCount = DB::table('students')
                ->join(
                    'enrollments',
                    'students.student_id',
                    '=',
                    'enrollments.student_id'
                )
                ->join(
                    'sections as sec',
                    'sec.id',
                    '=',
                    'enrollments.section_id'
                )
                ->where(
                    'sec.id',
                    $s->id
                )
                ->count();

            return [
                'id'            => $s->id,
                'code'          => $s->section_name,
                'program'       => $s->component,
                'schoolYear'    => $s->school_year,
                'semester'      => $s->semester,
                'room'          => $s->room,
                'schedule'      => $s->schedule,
                'status'        => $s->status,
                'instructor'    => $s->instructor_name,
                'instructor_id' => $s->instructor_id,
                'students'      => $studentCount,
            ];
        });

        return response()->json($rows);
    }


    /**
     * POST /api/sections
     *
     * Create a new section.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([

            'code' =>
                'required|string|max:64|unique:sections,section_name',

            'program' =>
                'nullable|in:CWTS,LTS,ROTC',

            'school_year' =>
                'nullable|string|max:32',

            'semester' =>
                'nullable|string|max:64',

            'room' =>
                'nullable|string|max:64',

            'schedule' =>
                'nullable|string|max:128',

            'status' =>
                'nullable|string|max:32',

            'instructor_name' =>
                'nullable|string|max:255',

            /*
             * This is the temporary token created
             * during class-list comparison.
             */
            'upload_token' =>
                'nullable|string',

            /*
             * Optional direct students array.
             */
            'students' =>
                'nullable|array',
        ]);


        /*
        |--------------------------------------------------------------------------
        | FIND INSTRUCTOR
        |--------------------------------------------------------------------------
        */

        $instructor = null;

        if (!empty($data['instructor_name'])) {

            $instructor = PortalUser::where(
                'name',
                $data['instructor_name']
            )
            ->where(
                'role',
                'instructor'
            )
            ->first();
        }


        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | CREATE SECTION
            |--------------------------------------------------------------------------
            */

            $section = Section::create([

                'section_name' =>
                    $data['code'],

                'component' =>
                    $data['program'] ?? 'CWTS',

                'school_year' =>
                    $data['school_year'] ?? '2025-2026',

                'semester' =>
                    $data['semester'] ?? '1st Semester',

                'room' =>
                    $data['room'] ?? 'TBA',

                'schedule' =>
                    $data['schedule'] ?? null,

                'status' =>
                    $data['status'] ?? 'Active',

                'instructor_id' =>
                    $instructor?->id,
            ]);


            $importedCount = 0;


            /*
            |--------------------------------------------------------------------------
            | OPTION 1
            |
            | USE PREVIOUSLY COMPARED CLASS LIST
            |
            | The temporary records are already stored in
            | class_list_students using the upload token.
            |--------------------------------------------------------------------------
            */

            if (!empty($data['upload_token'])) {

                $tempStudents = DB::table(
                    'class_list_students'
                )
                ->where(
                    'section_name',
                    $data['upload_token']
                )
                ->get();


                foreach ($tempStudents as $ts) {

                    /*
                    |--------------------------------------------------------------------------
                    | Make sure the temporary record has a Student ID.
                    |--------------------------------------------------------------------------
                    */

                    if (empty($ts->student_id)) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Find the master student.
                    |--------------------------------------------------------------------------
                    */

                    $student = Student::where(
                        'student_id',
                        $ts->student_id
                    )->first();


                    /*
                    |--------------------------------------------------------------------------
                    | Safety check.
                    |
                    | Never create an enrollment if the student
                    | does not exist in the master list.
                    |--------------------------------------------------------------------------
                    */

                    if (!$student) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update class_list_students section name.
                    |--------------------------------------------------------------------------
                    */

                    DB::table(
                        'class_list_students'
                    )
                    ->where(
                        'id',
                        $ts->id
                    )
                    ->update([

                        'section_name' =>
                            $section->section_name,

                        'updated_at' =>
                            now(),
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Sync student enrollment.
                    |--------------------------------------------------------------------------
                    */

                    Student::syncEnrollment(

                        $student->student_id,

                        $section->section_name,

                        null
                    );


                    $importedCount++;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | OPTION 2
            |
            | DIRECT STUDENTS ARRAY
            |
            | This is only used if the frontend sends students directly.
            |--------------------------------------------------------------------------
            */

            elseif (!empty($data['students'])) {

                foreach ($data['students'] as $s) {

                    $sName =
                        trim(
                            $s['name'] ?? ''
                        );


                    if ($sName === '') {
                        continue;
                    }


                    $sNo =
                        trim(
                            $s['studentNo'] ?? ''
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | We require a Student ID here.
                    |
                    | Records without Student ID should have been resolved
                    | during compareClassList().
                    |--------------------------------------------------------------------------
                    */

                    if ($sNo === '') {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Find master student.
                    |--------------------------------------------------------------------------
                    */

                    $student = Student::where(
                        'student_id',
                        $sNo
                    )->first();

                    if (!$student) {
                        $student = Student::where(
                            'serial_no',
                            $sNo
                        )->first();
                    }

                    if (!$student && !empty($sName)) {
                        $dbStudents = Student::all();
                        $student = $dbStudents->first(function ($dbS) use ($sName) {
                            return $this->studentNameMatches($dbS, $sName);
                        });
                    }

                    if ($student && empty($student->student_id) && !empty($sNo) && !str_starts_with($sNo, 'STUDENT-')) {
                        DB::table('students')->where('id', $student->id)->update(['student_id' => $sNo]);
                        $student->student_id = $sNo;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Never create a student from class-list upload.
                    |--------------------------------------------------------------------------
                    */

                    if (!$student) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Prevent duplicate class-list records.
                    |--------------------------------------------------------------------------
                    */

                    $alreadyExists = DB::table(
                        'class_list_students'
                    )
                    ->where(
                        'section_name',
                        $section->section_name
                    )
                    ->where(
                        'student_id',
                        $student->student_id
                    )
                    ->exists();


                    if ($alreadyExists) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Sync enrollment.
                    |--------------------------------------------------------------------------
                    */

                    Student::syncEnrollment(

                        $student->student_id,

                        $section->section_name,

                        null
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Copy MASTER STUDENT information
                    | into class_list_students.
                    |--------------------------------------------------------------------------
                    */

                    DB::table(
                        'class_list_students'
                    )
                    ->insert([

                        'section_name' =>
                            $section->section_name,

                        'student_id' =>
                            $student->student_id,

                        'name' =>
                            $this->formatStudentName(
                                $student
                            ),

                        'course' =>
                            $this->normalizeCourse(
                                $student->course
                            ),

                        'gender' =>
                            $student->sex,

                        'dob' =>
                            $this->formatDate(
                                $student->date_of_birth
                            ),

                        'place_of_birth' =>
                            $student->place_of_birth,

                        'address' =>
                            $student->complete_address,

                        'cell_no' =>
                            $student->contact_number,

                        'email' =>
                            $student->email,

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);


                    $importedCount++;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | SEND INSTRUCTOR NOTIFICATION
            |--------------------------------------------------------------------------
            */

            if ($instructor) {

                PortalNotification::create([

                    'user_id' =>
                        $instructor->id,

                    'type' =>
                        'assignment',

                    'title' =>
                        'New Section Assigned',

                    'message' =>
                        "You have been assigned to handle section " .
                        "{$section->section_name}. " .
                        "Please check your My Classes page for details.",

                    'is_read' =>
                        false,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | COMMIT TRANSACTION
            |--------------------------------------------------------------------------
            */

            DB::commit();


            DashboardMetricsVersion::bump();


            return response()->json([

                'success' =>
                    true,

                'message' =>
                    "Section {$section->section_name} created successfully " .
                    "with {$importedCount} students.",

                'section' => [

                    'id' =>
                        $section->id,

                    'code' =>
                        $section->section_name,

                    'program' =>
                        $section->component,

                    'schoolYear' =>
                        $section->school_year,

                    'room' =>
                        $section->room,

                    'status' =>
                        $section->status,

                    'instructor' =>
                        $section->instructor_name,

                    'students' =>
                        $importedCount,
                ],

            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();


            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Failed to create section: ' .
                    $e->getMessage(),

            ], 500);
        }
    }


    /**
     * POST /api/sections/assign
     *
     * Assign an instructor to a section.
     */
    public function assign(
        Request $request
    ): JsonResponse {

        $data = $request->validate([

            'section_code' =>
                'required|string|exists:sections,section_name',

            'instructor_email' =>
                'required|email|exists:portal_users,email',
        ]);


        $section =
            Section::where(
                'section_name',
                $data['section_code']
            )
            ->firstOrFail();


        $instructor =
            PortalUser::where(
                'email',
                $data['instructor_email']
            )
            ->where(
                'role',
                'instructor'
            )
            ->firstOrFail();


        $section->instructor_id =
            $instructor->id;

        $section->save();


        /*
        |--------------------------------------------------------------------------
        | CREATE NOTIFICATION
        |--------------------------------------------------------------------------
        */

        PortalNotification::create([

            'user_id' =>
                $instructor->id,

            'type' =>
                'assignment',

            'title' =>
                'New Section Assigned',

            'message' =>
                "You have been assigned to handle section " .
                "{$section->section_name}. " .
                "Please check your My Classes page for details.",

            'is_read' =>
                false,
        ]);


        DashboardMetricsVersion::bump();


        return response()->json([

            'success' =>
                true,

            'section' =>
                $section->section_name,

            'instructor' =>
                $instructor->name,
        ]);
    }


    /**
     * POST /api/sections/compare-class-list
     *
     * Compare an uploaded class list against the master students table.
     *
     * EXPECTED EXCEL COLUMNS:
     *
     * Student No.
     * Name
     * Program
     *
     * MATCHING PRIORITY:
     *
     * 1. Student No.
     *
     * 2. If Student No. is missing:
     *    Name + Program
     *
     * RESULTS:
     *
     * matched
     * needs_review
     * unmatched
     *
     * IMPORTANT:
     *
     * This method NEVER creates a new student.
     *
     * class_list_students gets the complete student information
     * from the master students table.
     */
    public function compareClassList(
        Request $request
    ): JsonResponse {

        $data = $request->validate([

            'token' =>
                'required|string',

            'students' =>
                'required|array',
        ]);


        $token =
            $data['token'];


        $studentsList =
            $data['students'];


        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | DELETE PREVIOUS TEMPORARY RECORDS
            |--------------------------------------------------------------------------
            */

            DB::table(
                'class_list_students'
            )
            ->where(
                'section_name',
                $token
            )
            ->delete();


            /*
            |--------------------------------------------------------------------------
            | FETCH MASTER STUDENTS
            |--------------------------------------------------------------------------
            */

            $dbStudents =
                Student::all();


            /*
            |--------------------------------------------------------------------------
            | RESULT ARRAYS
            |--------------------------------------------------------------------------
            */

            $matchedRows = [];

            $needsReview = [];

            $unmatchedRows = [];


            /*
            |--------------------------------------------------------------------------
            | PROCESS EVERY EXCEL ROW
            |--------------------------------------------------------------------------
            */

            error_log("=================================================");
            error_log("[COMPARE CLASS LIST DEBUG] Starting comparison for " . count($studentsList) . " rows against " . $dbStudents->count() . " master DB students.");
            error_log("=================================================");

            foreach (
                $studentsList as $index => $s
            ) {

                /*
                |--------------------------------------------------------------------------
                | READ ONLY THE THREE EXPECTED EXCEL FIELDS
                |--------------------------------------------------------------------------
                */

                $sName =
                    trim(
                        $s['name'] ?? ''
                    );


                $sNo =
                    trim(
                        $s['studentNo'] ?? ''
                    );


                $program =
                    trim(
                        $s['program'] ?? ''
                    );


                /*
                |--------------------------------------------------------------------------
                | Ignore completely empty rows.
                |--------------------------------------------------------------------------
                */

                if (
                    $sName === '' &&
                    $sNo === '' &&
                    $program === ''
                ) {
                    error_log("[ROW " . ($index + 1) . "] Ignored completely empty row.");
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | NAME IS REQUIRED FOR SAFE MATCHING
                |--------------------------------------------------------------------------
                */

                if ($sName === '') {
                    error_log("[ROW " . ($index + 1) . "] Skipped: Name is missing from uploaded Excel row.");

                    $unmatchedRows[] = [

                        'row' =>
                            $index + 1,

                        'name' =>
                            '',

                        'studentNo' =>
                            $sNo ?: null,

                        'program' =>
                            $program,

                        'reason' =>
                            'Name is missing from the class list.',
                    ];

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Normalize Student Number
                |--------------------------------------------------------------------------
                */

                if ($sNo === '') {
                    $sNo = null;
                }


                /*
                |--------------------------------------------------------------------------
                | Normalize Course
                |--------------------------------------------------------------------------
                */

                $classListCourse =
                    $this->normalizeCourse(
                        $program
                    );


                /*
                |--------------------------------------------------------------------------
                | CANDIDATES
                |--------------------------------------------------------------------------
                */

                $candidates =
                    collect();


                $matchedBy =
                    null;


                /*
                |--------------------------------------------------------------------------
                | MATCH 1
                |
                | Student ID
                |
                | This is the strongest match.
                |--------------------------------------------------------------------------
                */

                if ($sNo) {

                    $studentById =
                        $dbStudents->first(
                            function (
                                $student
                            ) use (
                                $sNo
                            ) {

                                return $this->normalizeStudentId(
                                    $student->student_id
                                )
                                ===
                                $this->normalizeStudentId(
                                    $sNo
                                );
                            }
                        );


                    if ($studentById) {

                        /*
                        |--------------------------------------------------------------------------
                        | OPTIONAL SAFETY CHECK
                        |
                        | If Student ID exists but Name and Program
                        | are clearly inconsistent, send to review.
                        |--------------------------------------------------------------------------
                        */

                        $nameMatches =
                            $this->studentNameMatches(
                                $studentById,
                                $sName
                            );


                        $courseMatches =
                            $this->courseMatches(
                                $studentById->course,
                                $classListCourse
                            );


                        if (
                            !$nameMatches ||
                            (
                                $classListCourse &&
                                !$courseMatches
                            )
                        ) {
                            error_log("[ROW " . ($index + 1) . "] REVIEW REQUIRED: ID '{$sNo}' found in DB, but safety check failed.");
                            error_log("  -> Uploaded: Name='{$sName}', Course='{$classListCourse}'");
                            error_log("  -> DB Record: Name='{$studentById->last_name}, {$studentById->first_name}', Course='{$studentById->course}'");
                            error_log("  -> Check Results: nameMatches=" . ($nameMatches ? 'YES' : 'NO') . ", courseMatches=" . ($courseMatches ? 'YES' : 'NO'));

                            $needsReview[] = [

                                'row' =>
                                    $index + 1,

                                'name' =>
                                    $sName,

                                'studentNo' =>
                                    $sNo,

                                'program' =>
                                    $classListCourse,

                                'reason' =>
                                    'Student ID was found, but the uploaded name or program does not match the master student record.',

                                'candidate' =>
                                    $this->studentCandidateData(
                                        $studentById
                                    ),
                            ];

                            continue;
                        }


                        $candidates =
                            collect([
                                $studentById
                            ]);


                        $matchedBy =
                            'student_id';

                        error_log("[ROW " . ($index + 1) . "] MATCHED BY STUDENT ID: ID='{$sNo}', Name='{$sName}'");
                    } else {
                        error_log("[ROW " . ($index + 1) . "] Student ID '{$sNo}' not found in DB. Falling back to Name/Course search...");
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | MATCH 2
                |
                | NAME + COURSE
                |
                | Only performed when Student No. is missing.
                |--------------------------------------------------------------------------
                */

                if (
                    $candidates->isEmpty()
                ) {

    /*
    |--------------------------------------------------------------------------
    | MATCH WITHOUT STUDENT ID
    |
    | Excel has:
    | - Name
    | - Program
    |
    | We compare these against the master students table.
    |--------------------------------------------------------------------------
    */

    $candidates =
        $dbStudents->filter(
            function ($student) use (
                $sName,
                $classListCourse
            ) {

                /*
                |--------------------------------------------------------------------------
                | STEP 1: NAME MATCH
                |--------------------------------------------------------------------------
                */

                $nameMatches =
                    $this->studentNameMatches(
                        $student,
                        $sName
                    );


                /*
                |--------------------------------------------------------------------------
                | If name does not match,
                | this student is not a candidate.
                |--------------------------------------------------------------------------
                */

                if (
                    !$nameMatches
                ) {

                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 2: COURSE MATCH
                |
                | Only check course if Excel provided a Program.
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($classListCourse)
                ) {

                    $courseMatches =
                        $this->courseMatches(
                            $student->course,
                            $classListCourse
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Name matched but course did not.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$courseMatches
                    ) {

                        return false;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | NAME + COURSE MATCHED
                |--------------------------------------------------------------------------
                */

                return true;
            }
        )
        ->values();


    /*
    |--------------------------------------------------------------------------
    | EXACTLY ONE MATCH
    |--------------------------------------------------------------------------
    */

    if (
        $candidates->count() === 1
    ) {

        $matchedBy =
            'name_and_course';
    }
}   


                /*
                |--------------------------------------------------------------------------
                | MULTIPLE MATCHES
                |--------------------------------------------------------------------------
                */

                if (
                    $candidates->count() > 1
                ) {
                    error_log("[ROW " . ($index + 1) . "] MULTIPLE MATCHES ({$candidates->count()} students) for Name='{$sName}'. Manual review required.");

                    $needsReview[] = [

                        'row' =>
                            $index + 1,

                        'name' =>
                            $sName,

                        'studentNo' =>
                            $sNo,

                        'program' =>
                            $classListCourse,

                        'reason' =>
                            'Multiple students matched the same name and program. Manual selection is required.',

                        'candidates' =>
                            $candidates
                                ->map(
                                    function (
                                        $student
                                    ) {

                                        return $this->studentCandidateData(
                                            $student
                                        );
                                    }
                                )
                                ->values()
                                ->toArray(),
                    ];


                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | NO MATCH
                |--------------------------------------------------------------------------
                */

                if (
                    $candidates->isEmpty()
                ) {
                    $reasonText = $sNo
                        ? "Student ID '{$sNo}' was not found in the master student list."
                        : "No unique student matched by name ('{$sName}') and program ('{$classListCourse}').";
                    error_log("[ROW " . ($index + 1) . "] UNMATCHED: {$reasonText}");

                    $unmatchedRows[] = [

                        'row' =>
                            $index + 1,

                        'name' =>
                            $sName,

                        'studentNo' =>
                            $sNo,

                        'program' =>
                            $classListCourse,

                        'reason' =>
                            $sNo
                                ? 'Student ID was not found in the master student list.'
                                : 'No unique student matched by name and program.',
                    ];


                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | EXACTLY ONE STUDENT FOUND
                |--------------------------------------------------------------------------
                */

                $dbStudent =
                    $candidates->first();


                /*
                |--------------------------------------------------------------------------
                | IMPORTANT:
                |
                | If Excel had no Student ID,
                | use the Student ID from the master list.
                |--------------------------------------------------------------------------
                */

                if (empty($dbStudent->student_id) && !empty($sNo)) {
                    DB::table('students')
                        ->where('id', $dbStudent->id)
                        ->update(['student_id' => $sNo]);
                    $dbStudent->student_id = $sNo;
                }

                if (empty($dbStudent->course) && !empty($classListCourse)) {
                    DB::table('students')
                        ->where('id', $dbStudent->id)
                        ->update(['course' => $classListCourse]);
                    $dbStudent->course = $classListCourse;
                }

                $resolvedStudentId =
                    !empty($dbStudent->student_id)
                        ? $dbStudent->student_id
                        : (!empty($dbStudent->serial_no)
                            ? $dbStudent->serial_no
                            : ('STUDENT-' . $dbStudent->id));

                /*
                |--------------------------------------------------------------------------
                | RESOLVE COURSE
                |--------------------------------------------------------------------------
                |
                | Master student course is preferred.
                |
                | If master course is empty,
                | use the normalized Excel program.
                |--------------------------------------------------------------------------
                */

                $resolvedCourse =
                    !empty($dbStudent->course)
                        ? $this->normalizeCourse($dbStudent->course)
                        : $classListCourse;


                /*
                |--------------------------------------------------------------------------
                | PREVENT DUPLICATE TEMPORARY RECORD
                |--------------------------------------------------------------------------
                */

                $alreadyExists =
                    DB::table(
                        'class_list_students'
                    )
                    ->where(
                        'section_name',
                        $token
                    )
                    ->where(
                        'student_id',
                        $resolvedStudentId
                    )
                    ->exists();


                if (
                    $alreadyExists
                ) {

                    $needsReview[] = [

                        'row' =>
                            $index + 1,

                        'name' =>
                            $sName,

                        'studentNo' =>
                            $sNo,

                        'program' =>
                            $classListCourse,

                        'reason' =>
                            'This student appears more than once in the uploaded class list.',

                        'candidate' =>
                            $this->studentCandidateData(
                                $dbStudent
                            ),
                    ];


                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | INSERT INTO class_list_students
                |
                | IMPORTANT:
                |
                | These values come from the MASTER students table.
                |
                | The Excel only provided:
                |
                | Student No.
                | Name
                | Program
                |--------------------------------------------------------------------------
                */

                DB::table(
                    'class_list_students'
                )
                ->insert([

                    'section_name' =>
                        $token,

                    'student_id' =>
                        $resolvedStudentId,

                    'name' =>
                        $this->formatStudentName(
                            $dbStudent
                        ),

                    'course' =>
                        $resolvedCourse,

                    'gender' =>
                        $dbStudent->sex,

                    'dob' =>
                        $this->formatDate(
                            $dbStudent->date_of_birth
                        ),

                    'place_of_birth' =>
                        $dbStudent->place_of_birth,

                    'address' =>
                        $dbStudent->complete_address,

                    'cell_no' =>
                        $dbStudent->contact_number,

                    'email' =>
                        $dbStudent->email,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);


                /*
                |--------------------------------------------------------------------------
                | ADD TO MATCHED RESULT
                |--------------------------------------------------------------------------
                */

                $matchedRows[] = [

                    'row' =>
                        $index + 1,

                    'name' =>
                        $this->formatStudentName(
                            $dbStudent
                        ),

                    'studentNo' =>
                        $resolvedStudentId,

                    'gender' =>
                        $dbStudent->sex
                        ?? '',

                    'dob' =>
                        $this->formatDate(
                            $dbStudent->date_of_birth
                        )
                        ?? '',

                    'birthPlace' =>
                        $dbStudent->place_of_birth
                        ?? '',

                    'address' =>
                        $dbStudent->complete_address
                        ?? '',

                    'cellNo' =>
                        $dbStudent->contact_number
                        ?? '',

                    'email' =>
                        $dbStudent->email
                        ?? '',

                    'program' =>
                        $resolvedCourse
                        ?? '',

                    'matchedBy' =>
                        $matchedBy,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | RETURN RESULTS
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' =>
                    true,

                'matched' =>
                    $matchedRows,

                'needs_review' =>
                    $needsReview,

                'unmatched' =>
                    $unmatchedRows,

                'matched_count' =>
                    count(
                        $matchedRows
                    ),

                'needs_review_count' =>
                    count(
                        $needsReview
                    ),

                'unmatched_count' =>
                    count(
                        $unmatchedRows
                    ),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();
            error_log("[COMPARE ERROR EXCEPTION] " . $e->getMessage());


            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Failed to compare class list: ' .
                    $e->getMessage(),

            ], 500);
        }
    }


    /**
     * Normalize Student ID.
     *
     * Removes spaces and converts to uppercase.
     */
    private function normalizeStudentId(
        ?string $studentId
    ): string {

        if (!$studentId) {
            return '';
        }


        return strtoupper(
            preg_replace(
                '/\s+/',
                '',
                trim(
                    $studentId
                )
            )
        );
    }


    /**
     * Normalize name.
     *
     * Handles:
     *
     * Juan Dela Cruz
     * Juan, Dela Cruz
     * Dela Cruz, Juan
     * Dela Cruz Juan
     */
    private function normalizeName(
        ?string $name
    ): string {

        if (!$name) {
            return '';
        }


        $name =
            mb_strtolower(
                trim(
                    $name
                ),
                'UTF-8'
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize punctuation
        |--------------------------------------------------------------------------
        */

        $name =
            str_replace(
                [
                    ',',
                    '.',
                    '-',
                    '_',
                ],
                ' ',
                $name
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize spaces
        |--------------------------------------------------------------------------
        */

        $name =
            preg_replace(
                '/\s+/u',
                ' ',
                $name
            );


        return trim(
            $name
        );
    }


    /**
     * Compact normalized name.
     *
     * Example:
     *
     * Juan Dela Cruz
     * ->
     * juandelacruz
     */
    private function compactName(
        ?string $name
    ): string {

        return preg_replace(
            '/[^a-z0-9ñ]/u',
            '',
            $this->normalizeName(
                $name
            )
        );
    }


    /**
     * Check if uploaded name matches master student.
     *
     * Supports:
     *
     * Excel:
     * Juan Dela Cruz
     *
     * Database:
     * Dela Cruz, Juan
     *
     * Database:
     * Juan Dela Cruz
     */
    private function studentNameMatches(
    Student $student,
    string $uploadedName
): bool {

    $uploadedName = trim($uploadedName);

    if ($uploadedName === '') {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize helper
    |--------------------------------------------------------------------------
    */

    $normalize = function (?string $value): string {

        if (!$value) {
            return '';
        }

        $value = mb_strtolower(
            trim($value),
            'UTF-8'
        );

        /*
        | Convert punctuation to spaces
        */

        $value = str_replace(
            [
                ',',
                '.',
                '-',
                '_',
            ],
            ' ',
            $value
        );

        /*
        | Remove duplicate spaces
        */

        $value = preg_replace(
            '/\s+/u',
            ' ',
            $value
        );

        return trim($value);
    };


    /*
    |--------------------------------------------------------------------------
    | Normalize compact version
    |--------------------------------------------------------------------------
    */

    $compact = function (?string $value) use ($normalize): string {

        return preg_replace(
            '/[^a-z0-9ñ]/u',
            '',
            $normalize($value)
        );
    };


    /*
    |--------------------------------------------------------------------------
    | Database values
    |--------------------------------------------------------------------------
    */

    $dbLastName =
        $compact(
            $student->last_name
        );

    $dbFirstName =
        $compact(
            $student->first_name
        );


    if (
        !$dbLastName ||
        !$dbFirstName
    ) {

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | EXCEL FORMAT
    |
    | LAST NAME, FIRST NAME MIDDLE NAME
    |
    | Example:
    |
    | YAMID, KENNETH JAMES GENOBIAG
    |
    | We extract:
    |
    | Last Name  = YAMID
    | First Name = KENNETH
    |
    | Middle Name is ignored because
    | students table has no middle_name column.
    |--------------------------------------------------------------------------
    */

    if (
        str_contains(
            $uploadedName,
            ','
        )
    ) {

        $parts =
            explode(
                ',',
                $uploadedName,
                2
            );


        $uploadedLastName =
            $compact(
                trim(
                    $parts[0] ?? ''
                )
            );


        $firstNamePart =
            trim(
                $parts[1] ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | Get first word after comma
        |
        | KENNETH JAMES GENOBIAG
        |
        | becomes:
        |
        | KENNETH
        |--------------------------------------------------------------------------
        */

        $firstNameWords =
            preg_split(
                '/\s+/u',
                $firstNamePart
            );


        $firstWordUploaded =
            $compact(
                $firstNameWords[0] ?? ''
            );

        $fullUploadedFirstName =
            $compact(
                $firstNamePart
            );


        $lastNameMatch = (
            $uploadedLastName === $dbLastName
        );

        $firstNameMatch = (
            $firstWordUploaded === $dbFirstName
            ||
            $fullUploadedFirstName === $dbFirstName
            ||
            (strlen($firstWordUploaded) >= 2 && str_contains($dbFirstName, $firstWordUploaded))
            ||
            (strlen($dbFirstName) >= 2 && str_contains($fullUploadedFirstName, $dbFirstName))
        );

        if ($lastNameMatch && $firstNameMatch) {
            return true;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FALLBACK FORMAT
    |
    | If Excel does not use:
    |
    | LAST NAME, FIRST NAME
    |
    | Try:
    |
    | FIRST NAME LAST NAME
    |
    |--------------------------------------------------------------------------
    */

    $words =
        preg_split(
            '/\s+/u',
            trim($uploadedName)
        );


    if (
        count($words) < 2
    ) {
        return false;
    }


    $uploadedFirstName =
        $compact(
            $words[0]
        );


    $uploadedLastName =
        $compact(
            $words[
                count($words) - 1
            ]
        );


    $fullUploadedCompact = $compact($uploadedName);
    $fullDbCompact = $compact(($student->last_name ?? '') . ' ' . ($student->first_name ?? ''));
    $fullDbCompactAlt = $compact(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));

    if ($fullUploadedCompact === $fullDbCompact || $fullUploadedCompact === $fullDbCompactAlt) {
        return true;
    }

    return (
        $uploadedLastName === $dbLastName
        &&
        (
            $uploadedFirstName === $dbFirstName
            ||
            (strlen($uploadedFirstName) >= 2 && str_contains($dbFirstName, $uploadedFirstName))
        )
    );
}


    /**
     * Check whether two courses match
     * after normalization.
     */
    private function courseMatches(
        ?string $dbCourse,
        ?string $uploadedCourse
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | If Excel program is empty,
        | do not reject the match based on course.
        |--------------------------------------------------------------------------
        */

        if (
            !$uploadedCourse
        ) {

            return true;
        }


        if (
            !$dbCourse
        ) {

            return false;
        }


        $normalizedDbCourse =
            $this->normalizeCourse(
                $dbCourse
            );


        $normalizedUploadedCourse =
            $this->normalizeCourse(
                $uploadedCourse
            );


        return
            $normalizedDbCourse ===
            $normalizedUploadedCourse;
    }


    /**
     * Format student name for class_list_students.
     *
     * Result:
     *
     * Last Name, First Name
     */
    private function formatStudentName(
        Student $student
    ): string {

        $lastName =
            trim(
                $student->last_name
                ?? ''
            );


        $firstName =
            trim(
                $student->first_name
                ?? ''
            );


        if (
            $lastName &&
            $firstName
        ) {

            return
                $lastName .
                ', ' .
                $firstName;
        }


        return
            $lastName
            ?: $firstName;
    }


    /**
     * Format database date.
     */
    private function formatDate(
        $date
    ): ?string {

        if (!$date) {
            return null;
        }


        if (
            $date instanceof \DateTimeInterface
        ) {

            return $date->format(
                'Y-m-d'
            );
        }


        $timestamp =
            strtotime(
                $date
            );


        if (
            $timestamp === false
        ) {

            return null;
        }


        return date(
            'Y-m-d',
            $timestamp
        );
    }


    /**
     * Return safe candidate information
     * for needs_review response.
     */
    private function studentCandidateData(
        Student $student
    ): array {

        return [

            'id' =>
                $student->id,

            'student_id' =>
                $student->student_id,

            'name' =>
                $this->formatStudentName(
                    $student
                ),

            'course' =>
                $this->normalizeCourse(
                    $student->course
                ),

            'date_of_birth' =>
                $this->formatDate(
                    $student->date_of_birth
                ),

            'gender' =>
                $student->sex,

            'email' =>
                $student->email,
        ];
    }


    /**
     * Normalize course/program names.
     *
     * The Excel may contain abbreviations.
     *
     * The master students table may contain full course names.
     *
     * Both are converted to the same canonical value.
     */
    private function normalizeCourse(
        ?string $course
    ): ?string {

        if (!$course) {
            return null;
        }


        $original =
            trim(
                $course
            );


        if (
            $original === ''
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Uppercase
        |--------------------------------------------------------------------------
        */

        $normalized =
            strtoupper(
                $original
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize special characters.
        |--------------------------------------------------------------------------
        */

        $normalized =
            str_replace(
                [
                    '–',
                    '—',
                    '_',
                    '/',
                    '\\',
                ],
                '-',
                $normalized
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize spaces.
        |--------------------------------------------------------------------------
        */

        $normalized =
            preg_replace(
                '/\s+/u',
                ' ',
                $normalized
            );


        $normalized =
            trim(
                $normalized
            );


        /*
        |--------------------------------------------------------------------------
        | Remove spaces around hyphens.
        |
        | BSRM - SPAE
        | BSRM-SPAE
        |
        | become the same.
        |--------------------------------------------------------------------------
        */

        $normalized =
            preg_replace(
                '/\s*-\s*/',
                '-',
                $normalized
            );


        /*
        |--------------------------------------------------------------------------
        | COURSE MAP
        |--------------------------------------------------------------------------
        */

        $courseMap = [

            /*
            |--------------------------------------------------------------------------
            | Marine Biology
            |--------------------------------------------------------------------------
            */

            'BSMB' =>
                'Bachelor of Science in Marine Biology',

            'BACHELOR OF SCIENCE IN MARINE BIOLOGY' =>
                'Bachelor of Science in Marine Biology',


            /*
            |--------------------------------------------------------------------------
            | Information Technology
            |--------------------------------------------------------------------------
            */

            'BSIT' =>
                'Bachelor of Science in Information Technology',

            'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY' =>
                'Bachelor of Science in Information Technology',


            /*
            |--------------------------------------------------------------------------
            | Information Systems
            |--------------------------------------------------------------------------
            */

            'BSIS' =>
                'Bachelor of Science in Information Systems',

            'BACHELOR OF SCIENCE IN INFORMATION SYSTEM' =>
                'Bachelor of Science in Information Systems',

            'BACHELOR OF SCIENCE IN INFORMATION SYSTEMS' =>
                'Bachelor of Science in Information Systems',


            /*
            |--------------------------------------------------------------------------
            | Tourism Management
            |--------------------------------------------------------------------------
            */

            'BSTM' =>
                'Bachelor of Science in Tourism Management',

            'BACHELOR OF SCIENCE IN TOURISM MANAGEMENT' =>
                'Bachelor of Science in Tourism Management',


            /*
            |--------------------------------------------------------------------------
            | Disaster and Resilience Management
            |--------------------------------------------------------------------------
            */

            'BSDRM' =>
                'Bachelor of Science in Disaster and Resilience Management',

            'BACHELOR OF SCIENCE IN DISASTER AND RESILIENCE MANAGEMENT' =>
                'Bachelor of Science in Disaster and Resilience Management',


            /*
            |--------------------------------------------------------------------------
            | Entrepreneurship
            |--------------------------------------------------------------------------
            */

            'BSENTREP' =>
                'Bachelor of Science in Entrepreneurship',

            'BSENTREPRENEURSHIP' =>
                'Bachelor of Science in Entrepreneurship',

            'BACHELOR OF SCIENCE IN ENTREPRENEURSHIP' =>
                'Bachelor of Science in Entrepreneurship',


            /*
            |--------------------------------------------------------------------------
            | Public Administration
            |--------------------------------------------------------------------------
            */

            'BPA' =>
                'Bachelor of Public Administration',

            'BACHELOR OF PUBLIC ADMINISTRATION' =>
                'Bachelor of Public Administration',


            /*
            |--------------------------------------------------------------------------
            | Secondary Education
            |--------------------------------------------------------------------------
            */

            'BSED' =>
                'Bachelor of Secondary Education',

            'BACHELOR OF SECONDARY EDUCATION' =>
                'Bachelor of Secondary Education',


            /*
            |--------------------------------------------------------------------------
            | Secondary Education - Mathematics
            |--------------------------------------------------------------------------
            */

            'BSED MATH' =>
                'Bachelor of Secondary Education major in Mathematics',

            'BSED MATHEMATICS' =>
                'Bachelor of Secondary Education major in Mathematics',

            'BACHELOR OF SECONDARY EDUCATION MAJOR IN MATHEMATICS' =>
                'Bachelor of Secondary Education major in Mathematics',


            /*
            |--------------------------------------------------------------------------
            | Secondary Education - Science
            |--------------------------------------------------------------------------
            */

            'BSED SCIENCE' =>
                'Bachelor of Secondary Education major in Science',

            'BSED SCI' =>
                'Bachelor of Secondary Education major in Science',

            'BACHELOR OF SECONDARY EDUCATION MAJOR IN SCIENCE' =>
                'Bachelor of Secondary Education major in Science',


            /*
            |--------------------------------------------------------------------------
            | Social Work
            |--------------------------------------------------------------------------
            */

            'BSSW' =>
                'Bachelor of Science in Social Work',

            'BACHELOR OF SCIENCE IN SOCIAL WORK' =>
                'Bachelor of Science in Social Work',


            /*
            |--------------------------------------------------------------------------
            | Communication
            |--------------------------------------------------------------------------
            */

            'BACOMM' =>
                'Bachelor of Arts in Communication',

            'BA COMM' =>
                'Bachelor of Arts in Communication',

            'BACHELOR OF ARTS IN COMMUNICATION' =>
                'Bachelor of Arts in Communication',


            /*
            |--------------------------------------------------------------------------
            | Technology and Livelihood Education
            |--------------------------------------------------------------------------
            */

            'BTLE' =>
                'Bachelor of Technology and Livelihood Education',

            'BACHELOR OF TECHNOLOGY AND LIVELIHOOD EDUCATION' =>
                'Bachelor of Technology and Livelihood Education',


            /*
            |--------------------------------------------------------------------------
            | Agro-Forestry
            |--------------------------------------------------------------------------
            */

            'BSAF' =>
                'Bachelor of Science in Agro-Forestry',

            'BSAGROFO' =>
                'Bachelor of Science in Agro-Forestry',

            'BS AGROFO' =>
                'Bachelor of Science in Agro-Forestry',

            'BACHELOR OF SCIENCE IN AGRO-FORESTRY' =>
                'Bachelor of Science in Agro-Forestry',

            'BACHELOR OF SCIENCE IN AGRO FORESTRY' =>
                'Bachelor of Science in Agro-Forestry',


            /*
            |--------------------------------------------------------------------------
            | Physical Education
            |--------------------------------------------------------------------------
            */

            'BPE' =>
                'Bachelor of Physical Education',

            'BACHELOR OF PHYSICAL EDUCATION' =>
                'Bachelor of Physical Education',


            /*
            |--------------------------------------------------------------------------
            | Food Technology
            |--------------------------------------------------------------------------
            */

            'BSFT' =>
                'Bachelor of Science in Food Technology',

            'BACHELOR OF SCIENCE IN FOOD TECHNOLOGY' =>
                'Bachelor of Science in Food Technology',


            /*
            |--------------------------------------------------------------------------
            | Fisheries and Aquatic Sciences
            |--------------------------------------------------------------------------
            */

            'BSFAS' =>
                'Bachelor of Science in Fisheries and Aquatic Sciences',

            'BACHELOR OF SCIENCE IN FISHERIES AND AQUATIC SCIENCES' =>
                'Bachelor of Science in Fisheries and Aquatic Sciences',
        ];


        /*
        |--------------------------------------------------------------------------
        | DIRECT COURSE MATCH
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $courseMap[
                    $normalized
                ]
            )
        ) {

            return $courseMap[
                $normalized
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | SPAE PROGRAMS
        |--------------------------------------------------------------------------
        |
        | Supported:
        |
        | BSRM-SPAE
        | BSRM / SPAE
        | BSRM SPAE
        | BSRM_SPAE
        |
        |--------------------------------------------------------------------------
        */

        $spaeNormalized =
            str_replace(
                [
                    ' ',
                    '/',
                    '_',
                    '\\',
                ],
                '-',
                $normalized
            );


        $spaeNormalized =
            preg_replace(
                '/-+/u',
                '-',
                $spaeNormalized
            );


        $spaeNormalized =
            trim(
                $spaeNormalized,
                '-'
            );


        if (
            $spaeNormalized ===
            'BSRM-SPAE'
        ) {

            return 'BSRM-SPAE';
        }


        if (
            $spaeNormalized ===
            'BSDRM-SPAE'
        ) {

            return 'BSDRM-SPAE';
        }


        if (
            $spaeNormalized ===
            'BPA-SPAE'
        ) {

            return 'BPA-SPAE';
        }


        /*
        |--------------------------------------------------------------------------
        | UNKNOWN COURSE
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Do not destroy unknown course values.
        |--------------------------------------------------------------------------
        */

        return $original;
    }


    /**
     * Determine NSTP component from section code.
     */
    private function componentFromSection(
        string $sectionCode
    ): string {

        $upper =
            strtoupper(
                $sectionCode
            );


        if (
            str_contains(
                $upper,
                'ROTC'
            )
        ) {

            return 'ROTC';
        }


        if (
            str_contains(
                $upper,
                'LTS'
            )
        ) {

            return 'LTS';
        }


        return 'CWTS';
    }
}