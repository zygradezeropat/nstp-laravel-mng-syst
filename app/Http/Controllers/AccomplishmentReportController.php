<?php

namespace App\Http\Controllers;

use App\Models\AccomplishmentReport;
use App\Models\PortalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccomplishmentReportController extends Controller
{
    /**
     * GET /api/accomplishment-reports
     * Returns reports, optionally filtered by instructor_id or status.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AccomplishmentReport::with(['instructor', 'section'])->orderByDesc('submitted_date');

        if ($instructorId = $request->query('instructor_id')) {
            $query->where('instructor_id', $instructorId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $reports = $query->get()->map(fn(AccomplishmentReport $r) => [
            'id'                 => $r->id,
            'instructor_id'      => $r->instructor_id,
            'instructor'         => $r->instructor?->name ?? '',
            'section_id'         => $r->section_id,
            'section'            => $r->section?->section_name ?? '',
            'title'              => $r->title,
            'location'           => $r->location,
            'completed_date'     => $r->completed_date?->format('Y-m-d'),
            'participants_count' => $r->participants_count,
            'accomplishments'    => $r->accomplishments,
            'files_attached'     => $r->files_attached,
            'status'             => $r->status,
            'submitted_date'     => $r->submitted_date?->toDateTimeString(),
        ]);

        return response()->json($reports);
    }

    /**
     * POST /api/accomplishment-reports
     * Submit a new accomplishment report.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'instructor_id'      => 'required|integer|exists:portal_users,id',
            'section_id'         => 'nullable|integer|exists:sections,id',
            'title'              => 'required|string|max:255',
            'location'           => 'nullable|string|max:255',
            'completed_date'     => 'nullable|date',
            'participants_count' => 'nullable|integer|min:0',
            'accomplishments'    => 'nullable|string',
            'files_attached'     => 'nullable|integer|min:0',
            'status'             => 'nullable|in:Draft,Pending,Reviewed,Revision',
        ]);

        $report = AccomplishmentReport::create(array_merge($data, [
            'status'         => $data['status'] ?? 'Pending',
            'submitted_date' => now(),
        ]));

        if ($report->status === 'Pending') {
            $user = auth()->user() ?? $report->instructor;
            $coordinators = PortalUser::where('role', 'coordinator')->get();
            foreach ($coordinators as $coord) {
                PortalNotification::create([
                    'user_id' => $coord->id,
                    'type'    => 'submission',
                    'title'   => 'Accomplishment Report Submitted',
                    'message' => ($user ? "{$user->name} submitted " : "New ") . "accomplishment report \"{$report->title}\" for review.",
                    'is_read' => false,
                ]);
            }
        }

        return response()->json(['success' => true, 'report' => $report], 201);
    }

    /**
     * PATCH /api/accomplishment-reports/{id}/status
     * Coordinator reviews or requests revision for an accomplishment report.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $report = AccomplishmentReport::with('instructor')->findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:Draft,Pending,Reviewed,Revision',
        ]);

        $report->update(['status' => $data['status']]);

        // Notify the instructor
        if ($report->instructor) {
            $statusLabel = $data['status'];
            PortalNotification::create([
                'user_id' => $report->instructor_id,
                'type'    => 'system',
                'title'   => "Accomplishment Report {$statusLabel}",
                'message' => "Your accomplishment report \"{$report->title}\" has been marked as {$statusLabel}.",
                'is_read' => false,
            ]);
        }

        return response()->json(['success' => true, 'report' => $report]);
    }

    /**
     * DELETE /api/accomplishment-reports/{id}
     * Remove a draft report.
     */
    public function destroy(int $id): JsonResponse
    {
        AccomplishmentReport::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
