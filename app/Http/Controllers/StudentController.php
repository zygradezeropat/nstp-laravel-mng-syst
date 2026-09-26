<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\DashboardMetricsVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL STUDENTS
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): JsonResponse
    {
        $query = Student::portalQuery()
            ->orderBy('sections.section_name')
            ->orderBy('students.last_name');

        if ($section = $request->query('section')) {
            $query->where(
                'sections.section_name',
                $section
            );
        }

        if ($grade = $request->query('grade')) {
            $query->where(
                'students.grade',
                $grade
            );
        }

        $rows = $query->get()->map(function ($row) {

            $student = new Student;

            $student->forceFill(
                $row->getAttributes()
            );

            $student->section_code =
                $row->section_code ?? '';

            $student->school_year =
                $row->school_year ?? null;

            $student->enrollment_grade_status =
                $row->enrollment_grade_status ?? null;

            return $student->toPortalArray();
        });

        return response()->json($rows);
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE SINGLE STUDENT
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $nameParts = Student::parseName(
            $data['name']
        );

        $student = Student::create([

            'student_id' =>
                $data['student_no'],

            'first_name' =>
                $nameParts['first_name'],

            'last_name' =>
                $nameParts['last_name'],

            'course' =>
                $data['program'] ?? null,

            'component' =>
                $this->componentFromSection(
                    $data['section_code'] ?? ''
                ),

            'enrollment_status' =>
                'Active',

            'grade' =>
                $data['grade'] ?? null,

            'date_of_birth' =>
                $data['dob'] ?: null,

            'place_of_birth' =>
                $data['birth_place'] ?? null,

            'sex' =>
                $data['gender'] ?: null,

            'contact_number' =>
                $data['cell_no'] ?? null,

            'email' =>
                $data['email'] ?? null,

            'complete_address' =>
                $data['address'] ?? null,

            'created_at' =>
                now(),
        ]);

        Student::syncEnrollment(
            $student->student_id,
            $data['section_code'] ?? null,
            $data['grade'] ?? null
        );

        DashboardMetricsVersion::bump();

        $fresh = Student::portalQuery()
            ->where(
                'students.student_id',
                $student->student_id
            )
            ->first();

        $student = new Student;

        $student->forceFill(
            $fresh->getAttributes()
        );

        $student->section_code =
            $fresh->section_code ?? '';

        $student->school_year =
            $fresh->school_year ?? null;

        $student->enrollment_grade_status =
            $fresh->enrollment_grade_status ?? null;

        return response()->json([

            'success' =>
                true,

            'student' =>
                $student->toPortalArray(),

        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE SINGLE STUDENT
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        string $student
    ): JsonResponse {

        $record =
            Student::findOrFail(
                $student
            );

        $data =
            $this->validated(
                $request,
                partial: true
            );

        $updates = [];


        if (
            isset(
                $data['student_no']
            )
        ) {

            $updates['student_id'] =
                $data['student_no'];
        }


        if (
            isset(
                $data['name']
            )
        ) {

            $parts =
                Student::parseName(
                    $data['name']
                );

            $updates['first_name'] =
                $parts['first_name'];

            $updates['last_name'] =
                $parts['last_name'];
        }


        if (
            array_key_exists(
                'program',
                $data
            )
        ) {

            $updates['course'] =
                $data['program'];
        }


        if (
            array_key_exists(
                'grade',
                $data
            )
        ) {

            $updates['grade'] =
                $data['grade'];
        }


        if (
            array_key_exists(
                'dob',
                $data
            )
        ) {

            $updates['date_of_birth'] =
                $data['dob']
                    ?: null;
        }


        if (
            array_key_exists(
                'birth_place',
                $data
            )
        ) {

            $updates['place_of_birth'] =
                $data['birth_place'];
        }


        if (
            array_key_exists(
                'gender',
                $data
            )
        ) {

            $updates['sex'] =
                $data['gender']
                    ?: null;
        }


        if (
            array_key_exists(
                'cell_no',
                $data
            )
        ) {

            $updates['contact_number'] =
                $data['cell_no'];
        }


        if (
            array_key_exists(
                'email',
                $data
            )
        ) {

            $updates['email'] =
                $data['email'];
        }


        if (
            array_key_exists(
                'address',
                $data
            )
        ) {

            $updates['complete_address'] =
                $data['address'];
        }


        $record->update(
            $updates
        );


        if (
            isset(
                $data['section_code']
            )
            ||
            array_key_exists(
                'grade',
                $data
            )
        ) {

            Student::syncEnrollment(

                $record->student_id,

                $data['section_code']
                    ?? null,

                $data['grade']
                    ?? $record->grade
            );
        }


        DashboardMetricsVersion::bump();


        $fresh =
            Student::portalQuery()
                ->where(
                    'students.student_id',
                    $record->student_id
                )
                ->first();


        $student = new Student;

        $student->forceFill(
            $fresh->getAttributes()
        );

        $student->section_code =
            $fresh->section_code ?? '';

        $student->school_year =
            $fresh->school_year ?? null;

        $student->enrollment_grade_status =
            $fresh->enrollment_grade_status
                ?? null;


        return response()->json([

            'success' =>
                true,

            'student' =>
                $student->toPortalArray(),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE STUDENT
    |--------------------------------------------------------------------------
    */
    public function destroy(
        string $student
    ): JsonResponse {

        $record =
            Student::findOrFail(
                $student
            );

        $record->delete();

        DashboardMetricsVersion::bump();

        return response()->json([
            'success' => true,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */
    private function validated(
        Request $request,
        bool $partial = false
    ): array {

        return $request->validate([

            'student_no' => [
                $partial
                    ? 'sometimes'
                    : 'required',

                'string',
                'max:64',
            ],

            'name' => [
                $partial
                    ? 'sometimes'
                    : 'required',

                'string',
                'max:255',
            ],

            'section_code' => [
                'nullable',
                'string',
                'max:64',
            ],

            'program' => [
                'nullable',
                'string',
                'max:255',
            ],

            'gender' => [
                'nullable',
                'string',
                'max:16',
            ],

            'dob' => [
                'nullable',
                'string',
                'max:64',
            ],

            'birth_place' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'cell_no' => [
                'nullable',
                'string',
                'max:32',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'instructor' => [
                'nullable',
                'string',
                'max:255',
            ],

            'school_year' => [
                'nullable',
                'string',
                'max:32',
            ],

            'room' => [
                'nullable',
                'string',
                'max:64',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | MASTER LIST IMPORT
    |--------------------------------------------------------------------------
    |
    | IMPORT MATCHING LOGIC
    |
    | CASE 1:
    | Excel has Student ID column
    | --------------------------------
    | Match by Student ID when a value exists.
    |
    | CASE 2:
    | Excel does not have Student ID column
    | --------------------------------
    | Match by:
    |
    | First Name
    | Last Name
    | Date of Birth
    |
    | EMAIL IS NOT USED FOR MATCHING.
    |
    | RESULT:
    |
    | Created
    | -> No existing student was found.
    |
    | Updated
    | -> Existing student was found and actual data changed.
    |
    | Unchanged / Skipped
    | -> Existing student was found but all imported data
    |    is exactly the same.
    |
    | ADDRESS:
    |
    | City Address + Provincial Address
    | -> complete_address
    |
    |--------------------------------------------------------------------------
    */
    public function import(
        Request $request
    ): JsonResponse {

        // ============================================================
        // 1. VALIDATE FILE
        // ============================================================

        $validator = Validator::make(
            $request->all(),
            [
                'file' =>
                    'required|file|max:25600',
            ]
        );

        if ($validator->fails()) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Validation failed for the uploaded file.',

                'errors' =>
                    $validator
                        ->errors()
                        ->toArray(),

            ], 422);
        }


        $file =
            $request->file('file');


        // ============================================================
        // 2. VALIDATE FILE EXTENSION
        // ============================================================

        $extension =
            strtolower(
                $file->getClientOriginalExtension()
            );


        if (
            !in_array(
                $extension,
                [
                    'xlsx',
                    'xls',
                    'csv',
                    'ods',
                    'tsv',
                    'xlsb',
                    'xlsm',
                    'txt',
                    'xml'
                ],
                true
            )
        ) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Invalid file format. Please upload a valid spreadsheet file (XLSX, XLS, CSV, ODS, TSV, XLSB, XLSM).',

            ], 422);
        }


        try {

            // ========================================================
            // 3. LOAD EXCEL
            // ========================================================

            $spreadsheet =
                IOFactory::load(
                    $file->getRealPath()
                );


            $worksheet =
                $spreadsheet
                    ->getActiveSheet();


            $rows =
                $worksheet->toArray();


            if (
                empty($rows)
            ) {

                return response()->json([

                    'success' =>
                        false,

                    'message' =>
                        'The uploaded Excel file is empty.',

                ], 422);
            }


            // ========================================================
            // 4. HEADER NORMALIZER
            // ========================================================

            $normalizeHeader =
                function ($value) {

                    return trim(

                        strtolower(

                            preg_replace(
                                '/\s+/',
                                ' ',
                                str_replace(
                                    [
                                        '_',
                                        '-',
                                        '.'
                                    ],
                                    ' ',
                                    (string) $value
                                )
                            )
                        )
                    );
                };


            // ========================================================
            // 5. FIND HEADER ROW
            // ========================================================

            $knownHeaders = [

                'student id',
                'student no',
                'student number',
                'student_id',
                'student_no',
                'student_number',

                'serial no',
                'serial number',
                'serial',
                'serial_no',
                'serial_number',

                'last name',
                'lastname',
                'surname',
                'last',

                'first name',
                'firstname',
                'first',

                'middle name',
                'middlename',
                'middle',

                'student name',
                'student_name',
                'name',
                'full name',
                'fullname',

                'gender',
                'sex',

                'date of birth',
                'dob',
                'birthday',
                'birth date',
                'birth_date',

                'program',
                'course',
                'college program',
                'college_program',

                'main program name',
                'main_program_name',

                'nstp program',
                'nstp_program',
                'component',

                'email',
                'email address',
                'email_address',

                'contact no',
                'contact number',
                'cell no',
                'cell #',
                'phone',

                'place of birth',
                'place_of_birth',
                'birthplace',

                'address',
                'residential address',
                'residential_address',

                'city address',
                'city_address',

                'provincial address',
                'provincial_address',

                'province address',
                'province_address',

                'province',
            ];


            $normalizedKnownHeaders =
                array_map(
                    $normalizeHeader,
                    $knownHeaders
                );


            $headerRowIndex = -1;


            foreach (
                $rows as $idx => $row
            ) {

                foreach (
                    $row as $cellVal
                ) {

                    if (
                        $cellVal === null
                        ||
                        trim(
                            (string) $cellVal
                        ) === ''
                    ) {
                        continue;
                    }


                    $cleanVal =
                        $normalizeHeader(
                            $cellVal
                        );


                    if (
                        in_array(
                            $cleanVal,
                            $normalizedKnownHeaders,
                            true
                        )
                    ) {

                        $headerRowIndex =
                            $idx;

                        break 2;
                    }
                }
            }


            // ========================================================
            // 6. GET HEADER AND DATA ROWS
            // ========================================================

            if (
                $headerRowIndex !== -1
            ) {

                $headerRow =
                    $rows[
                        $headerRowIndex
                    ];


                $dataRows =
                    array_slice(
                        $rows,
                        $headerRowIndex + 1
                    );

            } else {

                $headerRowIndex =
                    0;


                $headerRow =
                    array_shift(
                        $rows
                    );


                $dataRows =
                    $rows;
            }


            // ========================================================
            // 7. NORMALIZE HEADERS
            // ========================================================

            $headerKeys =
                array_map(
                    $normalizeHeader,
                    $headerRow
                );


            // ========================================================
            // 8. EXCEL VALUE HELPER
            // ========================================================

            $getVal =
                function (
                    $row,
                    array $keys
                ) use (
                    $headerKeys,
                    $normalizeHeader
                ) {

                    foreach (
                        $keys as $key
                    ) {

                        $normalizedKey =
                            $normalizeHeader(
                                $key
                            );


                        $idx =
                            array_search(
                                $normalizedKey,
                                $headerKeys,
                                true
                            );


                        if (
                            $idx !== false
                            &&
                            array_key_exists(
                                $idx,
                                $row
                            )
                        ) {

                            $value =
                                $row[$idx];


                            if (
                                $value !== null
                                &&
                                trim(
                                    (string) $value
                                ) !== ''
                            ) {

                                return trim(
                                    (string) $value
                                );
                            }
                        }
                    }


                    return null;
                };


            // ========================================================
            // 9. DETECT COLUMNS
            // ========================================================

            $hasStudentIdColumn =
                false;


            foreach (
                [
                    'student id',
                    'student no',
                    'student number',
                    'student_id',
                    'student_no',
                    'student_number',
                ] as $possibleHeader
            ) {

                if (
                    in_array(
                        $normalizeHeader(
                            $possibleHeader
                        ),
                        $headerKeys,
                        true
                    )
                ) {

                    $hasStudentIdColumn =
                        true;

                    break;
                }
            }


            $hasSerialNoColumn =
                false;


            foreach (
                [
                    'serial no',
                    'serial number',
                    'serial',
                    'serial_no',
                    'serial_number',
                ] as $possibleHeader
            ) {

                if (
                    in_array(
                        $normalizeHeader(
                            $possibleHeader
                        ),
                        $headerKeys,
                        true
                    )
                ) {

                    $hasSerialNoColumn =
                        true;

                    break;
                }
            }


            // ========================================================
            // 10. COUNTERS
            // ========================================================

            $createdStudents =
                0;

            $updatedStudents =
                0;

            $unchangedStudents =
                0;

            $skippedStudents =
                0;

            $importedSections =
                0;

            $errors =
                [];


            // ========================================================
            // 11. DEFAULT NSTP COMPONENT
            // ========================================================

            $sheetName =
                $worksheet->getTitle();


            $filenameToCheck =
                strtoupper(
                    $file->getClientOriginalName()
                    . ' '
                    . $sheetName
                );


            $defaultNstpProg =
                'CWTS';


            if (
                str_contains(
                    $filenameToCheck,
                    'ROTC'
                )
            ) {

                $defaultNstpProg =
                    'ROTC';

            } elseif (
                str_contains(
                    $filenameToCheck,
                    'LTS'
                )
            ) {

                $defaultNstpProg =
                    'LTS';
            }


            // ========================================================
            // 12. BEGIN TRANSACTION
            // ========================================================

            DB::beginTransaction();


            // ========================================================
            // 13. PROCESS EVERY ROW
            // ========================================================

            foreach (
                $dataRows as $rowIndex => $row
            ) {

                $excelRowNumber =
                    $rowIndex
                    +
                    $headerRowIndex
                    +
                    2;


                // ----------------------------------------------------
                // SKIP EMPTY ROW
                // ----------------------------------------------------

                if (
                    empty(
                        array_filter(
                            $row,
                            fn ($value) =>
                                $value !== null
                                &&
                                trim(
                                    (string) $value
                                ) !== ''
                        )
                    )
                ) {

                    continue;
                }


                // ====================================================
                // 14. STUDENT ID
                // ====================================================

                $studentNo =
                    null;


                if (
                    $hasStudentIdColumn
                ) {

                    $studentNo =
                        $getVal(
                            $row,
                            [
                                'student id',
                                'student no',
                                'student number',
                                'student_id',
                                'student_no',
                                'student_number',
                            ]
                        );


                    $studentNo =
                        $studentNo
                            ? trim(
                                $studentNo
                            )
                            : null;
                }


                // ====================================================
                // 15. SERIAL NUMBER
                // ====================================================

                $serialNo =
                    null;


                if (
                    $hasSerialNoColumn
                ) {

                    $serialNo =
                        $getVal(
                            $row,
                            [
                                'serial no',
                                'serial number',
                                'serial',
                                'serial_no',
                                'serial_number',
                            ]
                        );


                    $serialNo =
                        $serialNo
                            ? trim(
                                $serialNo
                            )
                            : null;
                }


                // ====================================================
                // 16. NAME
                // ====================================================

                $fullName =
                    $getVal(
                        $row,
                        [
                            'student name',
                            'student_name',
                            'name',
                            'full name',
                            'fullname',
                        ]
                    );


                $lastName =
                    $getVal(
                        $row,
                        [
                            'last name',
                            'lastname',
                            'surname',
                            'last',
                        ]
                    );


                $firstName =
                    $getVal(
                        $row,
                        [
                            'first name',
                            'firstname',
                            'first',
                        ]
                    );


                $middleName =
                    $getVal(
                        $row,
                        [
                            'middle name',
                            'middlename',
                            'middle',
                        ]
                    );


                // ====================================================
                // 17. BUILD FULL NAME
                // ====================================================

                if (
                    !$fullName
                ) {

                    if (
                        $lastName
                        ||
                        $firstName
                    ) {

                        $nameParts =
                            [];


                        if (
                            $lastName
                        ) {

                            $nameParts[] =
                                trim(
                                    $lastName
                                ) . ',';
                        }


                        if (
                            $firstName
                        ) {

                            $nameParts[] =
                                trim(
                                    $firstName
                                );
                        }


                        if (
                            $middleName
                        ) {

                            $nameParts[] =
                                trim(
                                    $middleName
                                );
                        }


                        $fullName =
                            trim(
                                implode(
                                    ' ',
                                    $nameParts
                                )
                            );
                    }
                }


                // ====================================================
                // 18. REQUIRE NAME
                // ====================================================

                if (
                    !$fullName
                ) {

                    $skippedStudents++;


                    $errors[] = [

                        'row' =>
                            $excelRowNumber,

                        'message' =>
                            'Student name is missing.',
                    ];


                    continue;
                }


                // ====================================================
                // 19. OTHER DATA
                // ====================================================

                $collegeProgram =
                    $getVal(
                        $row,
                        [
                            'main program name',
                            'main_program_name',
                            'program',
                            'course',
                            'college program',
                            'college_program',
                        ]
                    );


                $sectionCode =
                    $getVal(
                        $row,
                        [
                            'section code',
                            'section',
                            'class',
                        ]
                    );


                $nstpProgram =
                    $getVal(
                        $row,
                        [
                            'nstp program',
                            'nstp_program',
                            'component',
                        ]
                    );


                $dob =
                    $getVal(
                        $row,
                        [
                            'date of birth',
                            'dob',
                            'birthday',
                            'birth date',
                            'birth_date',
                        ]
                    );


                $birthPlace =
                    $getVal(
                        $row,
                        [
                            'place of birth',
                            'place_of_birth',
                            'birthplace',
                            'pob',
                        ]
                    );


                $gender =
                    $getVal(
                        $row,
                        [
                            'gender',
                            'sex',
                        ]
                    );


                $cellNo =
                    $getVal(
                        $row,
                        [
                            'cell no',
                            'cell number',
                            'cell #',
                            'phone',
                            'contact no',
                            'contact number',
                            'cell_no',
                        ]
                    );


                $email =
                    $getVal(
                        $row,
                        [
                            'email address',
                            'email',
                            'email_address',
                        ]
                    );


                if (
                    $email
                ) {

                    $email =
                        strtolower(
                            trim(
                                $email
                            )
                        );
                }


                // ====================================================
                // 20. CITY ADDRESS
                // ====================================================

                $cityAddress =
                    $getVal(
                        $row,
                        [
                            'city address',
                            'city_address',
                            'city',
                        ]
                    );


                // ====================================================
                // 21. PROVINCIAL ADDRESS
                // ====================================================

                $provincialAddress =
                    $getVal(
                        $row,
                        [
                            'provincial address',
                            'provincial_address',
                            'province address',
                            'province_address',
                            'province',
                        ]
                    );


                // ====================================================
                // 22. COMBINE ADDRESS
                // ====================================================

                $addressParts =
                    [];


                if (
                    $cityAddress
                ) {

                    $addressParts[] =
                        trim(
                            $cityAddress
                        );
                }


                if (
                    $provincialAddress
                ) {

                    $addressParts[] =
                        trim(
                            $provincialAddress
                        );
                }


                $address =
                    !empty(
                        $addressParts
                    )
                        ? implode(
                            ', ',
                            $addressParts
                        )
                        : null;


                // ====================================================
                // 23. DETERMINE NSTP COMPONENT
                // ====================================================

                if (
                    $nstpProgram
                ) {

                    $nstpProg =
                        strtoupper(
                            trim(
                                $nstpProgram
                            )
                        );


                    if (
                        str_contains(
                            $nstpProg,
                            'ROTC'
                        )
                    ) {

                        $nstpProg =
                            'ROTC';

                    } elseif (
                        str_contains(
                            $nstpProg,
                            'LTS'
                        )
                    ) {

                        $nstpProg =
                            'LTS';

                    } elseif (
                        str_contains(
                            $nstpProg,
                            'CWTS'
                        )
                    ) {

                        $nstpProg =
                            'CWTS';

                    } else {

                        $nstpProg =
                            $defaultNstpProg;
                    }

                } elseif (
                    $sectionCode
                ) {

                    $sc =
                        strtoupper(
                            trim(
                                $sectionCode
                            )
                        );


                    if (
                        str_contains(
                            $sc,
                            'ROTC'
                        )
                    ) {

                        $nstpProg =
                            'ROTC';

                    } elseif (
                        str_contains(
                            $sc,
                            'LTS'
                        )
                    ) {

                        $nstpProg =
                            'LTS';

                    } elseif (
                        str_contains(
                            $sc,
                            'CWTS'
                        )
                    ) {

                        $nstpProg =
                            'CWTS';

                    } else {

                        $nstpProg =
                            $defaultNstpProg;
                    }

                } else {

                    $nstpProg =
                        $defaultNstpProg;
                }


 // ====================================================
// 24. BUILD STUDENT NAME DATA
// ====================================================
//
// Excel:
//
// Surname       = YAMID
// First Name    = KENNETH JAMES
// Middle Name   = GENOBIAGON
//
// Database:
//
// last_name     = YAMID
// first_name    = KENNETH JAMES GENOBIAGON
// middle_name   = GENOBIAGON
//
// The middle name is intentionally appended to
// first_name because the existing student system
// expects the complete given name in first_name.
// ====================================================

$cleanLastName =
$lastName
    ? trim($lastName)
    : null;

$cleanFirstName =
$firstName
    ? trim($firstName)
    : null;

$cleanMiddleName =
$middleName
    ? trim($middleName)
    : null;


// ----------------------------------------------------
// FALLBACK: PARSE SINGLE-COLUMN FULL NAME
// ----------------------------------------------------
// Handles formats such as:
// "BERNALES, JOSE RANDY GASCO" -> Last: BERNALES, First: JOSE RANDY GASCO
// "JOSE RANDY GASCO BERNALES" -> Last: BERNALES, First: JOSE RANDY GASCO
// ----------------------------------------------------
if (
    !$cleanLastName
    &&
    !$cleanFirstName
    &&
    $fullName
) {

    $trimmedFullName =
        trim(
            $fullName
        );


    if (
        str_contains(
            $trimmedFullName,
            ','
        )
    ) {

        $parts =
            explode(
                ',',
                $trimmedFullName,
                2
            );


        $cleanLastName =
            trim(
                $parts[0]
            );


        $cleanFirstName =
            trim(
                $parts[1]
            );

    } else {

        $parts =
            preg_split(
                '/\s+/',
                $trimmedFullName
            );


        if (
            count($parts) > 1
        ) {

            $cleanLastName =
                array_pop(
                    $parts
                );


            $cleanFirstName =
                implode(
                    ' ',
                    $parts
                );

        } else {

            $cleanLastName =
                $trimmedFullName;


            $cleanFirstName =
                $trimmedFullName;
        }
    }
}


// ----------------------------------------------------
// APPEND MIDDLE NAME TO FIRST NAME IF SEPARATE
// ----------------------------------------------------

$databaseFirstName =
trim(
    implode(
        ' ',
        array_filter([
            $cleanFirstName,
            $cleanMiddleName,
        ])
    )
);


// ----------------------------------------------------
// ENSURE FIRST NAME IS NOT NULL
// ----------------------------------------------------

if (
$databaseFirstName === ''
) {

$databaseFirstName = null;
}


$parsed = [

'last_name' =>
    $cleanLastName,

'first_name' =>
    $databaseFirstName,

'middle_name' =>
    $cleanMiddleName,
];


                // ====================================================
                // 25. FORMAT DOB
                // ====================================================

                $formattedDob =
                    null;


                if (
                    $dob
                ) {

                    $time =
                        strtotime(
                            $dob
                        );


                    if (
                        $time !== false
                    ) {

                        $formattedDob =
                            date(
                                'Y-m-d',
                                $time
                            );
                    }
                }


                // ====================================================
                // 26. FIND EXISTING STUDENT
                // ====================================================

                $student =
                    null;


                $matchedBy =
                    null;


                // ----------------------------------------------------
                // MATCH BY STUDENT ID
                // ----------------------------------------------------

                if (
                    $hasStudentIdColumn
                    &&
                    $studentNo
                ) {

                    $student =
                        Student::where(
                            'student_id',
                            $studentNo
                        )->first();


                    if (
                        $student
                    ) {

                        $matchedBy =
                            'student_id';
                    }
                }


                // ----------------------------------------------------
                // MATCH BY NAME + DOB
                // ----------------------------------------------------
                //
                // This is used when:
                //
                // - Excel does not have Student ID column
                // OR
                // - Excel Student ID column exists but is empty
                //
                // ----------------------------------------------------

                if (
                    !$student
                    &&
                    $formattedDob
                    &&
                    !empty(
                        $parsed['first_name']
                    )
                    &&
                    !empty(
                        $parsed['last_name']
                    )
                ) {

                    $student =
                        Student::whereRaw(
                            'LOWER(TRIM(first_name)) = ?',
                            [
                                strtolower(
                                    trim(
                                        $parsed['first_name']
                                    )
                                )
                            ]
                        )
                        ->whereRaw(
                            'LOWER(TRIM(last_name)) = ?',
                            [
                                strtolower(
                                    trim(
                                        $parsed['last_name']
                                    )
                                )
                            ]
                        )
                        ->whereDate(
                            'date_of_birth',
                            $formattedDob
                        )
                        ->first();


                    if (
                        $student
                    ) {

                        $matchedBy =
                            'name_and_date_of_birth';
                    }
                }


                // ====================================================
                // 27. PREPARE IMPORTED DATA
                // ====================================================

                $incomingData = [

    'first_name' =>
        $parsed['first_name'],

    'middle_name' =>
        $parsed['middle_name'],

    'last_name' =>
        $parsed['last_name'],

    'course' =>
        $collegeProgram
            ?: null,

    'component' =>
        $nstpProg
            ?: null,

    'enrollment_status' =>
        'Active',

    'date_of_birth' =>
        $formattedDob,

    'place_of_birth' =>
        $birthPlace
            ?: null,

    'sex' =>
        $gender
            ?: null,

    'contact_number' =>
        $cellNo
            ?: null,

    'email' =>
        $email
            ?: null,

    'complete_address' =>
        $address,
];


                // ====================================================
                // 28. ADD STUDENT ID IF AVAILABLE
                // ====================================================

                if (
                    $hasStudentIdColumn
                    &&
                    $studentNo
                ) {

                    $incomingData[
                        'student_id'
                    ] =
                        $studentNo;
                }


                // ====================================================
                // 29. ADD SERIAL NUMBER IF AVAILABLE
                // ====================================================

                if (
                    $hasSerialNoColumn
                    &&
                    $serialNo
                ) {

                    $incomingData[
                        'serial_no'
                    ] =
                        $serialNo;
                }


                // ====================================================
                // 30. CREATE NEW STUDENT
                // ====================================================

                if (
                    !$student
                ) {

                    if (
                        !$hasStudentIdColumn
                        ||
                        !$studentNo
                    ) {

                        $incomingData[
                            'student_id'
                        ] =
                            null;
                    }


                    if (
                        !$hasSerialNoColumn
                        ||
                        !$serialNo
                    ) {

                        $incomingData[
                            'serial_no'
                        ] =
                            null;
                    }


                    Student::create(
                        $incomingData
                    );


                    $createdStudents++;


                    continue;
                }


                // ====================================================
                // 31. PRESERVE EXISTING STUDENT ID
                // ====================================================

                if (
                    !isset(
                        $incomingData[
                            'student_id'
                        ]
                    )
                ) {

                    $incomingData[
                        'student_id'
                    ] =
                        $student->student_id;
                }


                // ====================================================
                // 32. PRESERVE EXISTING SERIAL NUMBER
                // ====================================================

                if (
                    !isset(
                        $incomingData[
                            'serial_no'
                        ]
                    )
                ) {

                    $incomingData[
                        'serial_no'
                    ] =
                        $student->serial_no;
                }


                // ====================================================
                // 33. CHECK FOR REAL CHANGES
                // ====================================================

                $changes =
                    [];


                foreach (
                    $incomingData
                    as $field => $newValue
                ) {

                    $oldValue =
                        $student->{$field};


                    // ----------------------------------------------
                    // NORMALIZE DATE
                    // ----------------------------------------------

                    if (
                        $field ===
                        'date_of_birth'
                    ) {

                        $oldNormalized =
                            $oldValue
                                ? date(
                                    'Y-m-d',
                                    strtotime(
                                        $oldValue
                                    )
                                )
                                : null;


                        $newNormalized =
                            $newValue
                                ? date(
                                    'Y-m-d',
                                    strtotime(
                                        $newValue
                                    )
                                )
                                : null;

                    } else {

                        // ------------------------------------------
                        // NORMALIZE TEXT
                        // ------------------------------------------

                        $oldNormalized =
                            $oldValue === null
                                ? null
                                : strtolower(
                                    trim(
                                        (string)
                                        $oldValue
                                    )
                                );


                        $newNormalized =
                            $newValue === null
                                ? null
                                : strtolower(
                                    trim(
                                        (string)
                                        $newValue
                                    )
                                );
                    }


                    // ----------------------------------------------
                    // DETECT ACTUAL CHANGE
                    // ----------------------------------------------

                    if (
                        $oldNormalized
                        !==
                        $newNormalized
                    ) {

                        $changes[
                            $field
                        ] = [

                            'old' =>
                                $oldValue,

                            'new' =>
                                $newValue,
                        ];
                    }
                }


                // ====================================================
                // 34. UPDATED OR UNCHANGED
                // ====================================================

                if (
                    empty(
                        $changes
                    )
                ) {

                    // ----------------------------------------------
                    // NOTHING CHANGED
                    // ----------------------------------------------

                    $unchangedStudents++;


                    \Log::info(
                        'Student skipped because imported data is unchanged.',
                        [

                            'student_id' =>
                                $student->student_id,

                            'serial_no' =>
                                $student->serial_no,

                            'matched_by' =>
                                $matchedBy,

                            'row' =>
                                $excelRowNumber,
                        ]
                    );

                } else {

                    // ----------------------------------------------
                    // SOMETHING CHANGED
                    // ----------------------------------------------

                    $student->update(
                        $incomingData
                    );


                    $updatedStudents++;


                    \Log::info(
                        'Student updated during master list import.',
                        [

                            'student_id' =>
                                $student->student_id,

                            'serial_no' =>
                                $student->serial_no,

                            'matched_by' =>
                                $matchedBy,

                            'changes' =>
                                $changes,

                            'row' =>
                                $excelRowNumber,
                        ]
                    );
                }
            }


            // ========================================================
            // 35. COMMIT TRANSACTION
            // ========================================================

            DB::commit();


            DashboardMetricsVersion::bump();


            // ========================================================
            // 36. RETURN RESULTS
            // ========================================================

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Master list imported successfully.',

                'created_students' =>
                    $createdStudents,

                'updated_students' =>
                    $updatedStudents,

                'unchanged_students' =>
                    $unchangedStudents,

                'skipped_students' =>
                    $skippedStudents,

                'imported_sections' =>
                    $importedSections,

                'errors' =>
                    $errors,

            ]);

        } catch (
            \Exception $e
        ) {

            // ========================================================
            // ROLLBACK
            // ========================================================

            if (
                DB::transactionLevel() > 0
            ) {

                DB::rollBack();
            }


            \Log::error(
                'Student Master List Import Failed',
                [

                    'error' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );


            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Failed to import master list: '
                    .
                    $e->getMessage(),

                'debug' => [

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                ],

            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DETERMINE NSTP COMPONENT
    |--------------------------------------------------------------------------
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