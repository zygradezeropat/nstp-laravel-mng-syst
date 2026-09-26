<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Http\Controllers\SectionController;

$controller = new SectionController();

$sampleRows = [
    ['name' => 'Juan Carlos Dela Cruz Jr.', 'studentNo' => '2023-0001', 'program' => 'Bachelor of Science in Criminology'],
    ['name' => 'Angelica Mendoza', 'studentNo' => '2023-0002', 'program' => 'Bachelor of Science in Nursing'],
    ['name' => 'Mark Anthony Ramirez', 'studentNo' => '2023-0003', 'program' => 'Bachelor of Science in Information Technology'],
    ['name' => 'Beatrice Mae Alcantara', 'studentNo' => '2023-0004', 'program' => 'Bachelor of Science in Business Administration'],
    ['name' => 'Christian Dave Bautista', 'studentNo' => '2023-0005', 'program' => 'Bachelor of Science in Civil Engineering'],
    ['name' => 'Jenny Rose Castillo', 'studentNo' => '2023-0006', 'program' => 'Bachelor of Elementary Education'],
    ['name' => 'Joshua Domingo III', 'studentNo' => '2023-0007', 'program' => 'Bachelor of Science in Computer Science'],
    ['name' => 'Maria Lourdes Espinosa', 'studentNo' => '2023-0008', 'program' => 'Bachelor of Science in Hospitality Management'],
    ['name' => 'Kevin Ray Fernandez', 'studentNo' => '2023-0009', 'program' => 'Bachelor of Science in Agriculture'],
    ['name' => 'Patricia Anne Gonzales', 'studentNo' => '2023-0010', 'program' => 'Bachelor of Secondary Education Major in English'],
];

$req = new \Illuminate\Http\Request();
$req->replace([
    'token' => 'test_token',
    'program' => 'ROTC',
    'students' => $sampleRows
]);

$response = $controller->compareClassList($req);
$resData = $response->getData(true);
echo "Total Matched: " . $resData['matched_count'] . "\n";
echo "Total Needs Review: " . $resData['needs_review_count'] . "\n";
echo "Total Unmatched: " . $resData['unmatched_count'] . "\n";
