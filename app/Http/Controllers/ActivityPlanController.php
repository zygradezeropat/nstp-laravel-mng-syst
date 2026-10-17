<?php

namespace App\Http\Controllers;

use App\Models\ActivityPlan;
use App\Models\PortalNotification;
use App\Models\PortalUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityPlanController extends Controller
{
    /**
     * GET /api/activity-plans
     * Returns activity plans, optionally filtered by instructor_id or status.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ActivityPlan::with(['instructor', 'section'])->orderByDesc('submitted_date');

        if ($instructorId = $request->query('instructor_id')) {
            $query->where('instructor_id', $instructorId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $plans = $query->get()->map(fn(ActivityPlan $p) => [
            'id'             => $p->id,
            'instructor_id'  => $p->instructor_id,
            'instructor'     => $p->instructor?->name ?? '',
            'section_id'     => $p->section_id,
            'section'        => $p->section?->section_name ?? '',
            'title'          => $p->title,
            'description'    => $p->description,
            'location'       => $p->location,
            'scheduled_date' => $p->scheduled_date?->format('Y-m-d'),
            'objectives'     => $p->objectives,
            'files_attached' => $p->files_attached,
            'status'         => $p->status,
            'submitted_date' => $p->submitted_date?->toDateTimeString(),
        ]);

        return response()->json($plans);
    }

    /**
     * POST /api/activity-plans
     * Submit a new activity plan.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'instructor_id'  => 'required|integer|exists:portal_users,id',
            'section_id'     => 'nullable|integer|exists:sections,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'location'       => 'nullable|string|max:255',
            'scheduled_date' => 'nullable|date',
            'objectives'     => 'nullable|string',
            'files_attached' => 'nullable|integer|min:0',
            'status'         => 'nullable|in:Draft,Pending,Approved,Rejected',
        ]);

        $plan = ActivityPlan::create(array_merge($data, [
            'status'         => $data['status'] ?? 'Pending',
            'submitted_date' => now(),
        ]));

        if ($plan->status === 'Pending') {
            $user = auth()->user() ?? $plan->instructor;
            $coordinators = PortalUser::where('role', 'coordinator')->get();
            foreach ($coordinators as $coord) {
                PortalNotification::create([
                    'user_id' => $coord->id,
                    'type'    => 'submission',
                    'title'   => 'Activity Plan Submitted',
                    'message' => ($user ? "{$user->name} submitted " : "New ") . "activity plan \"{$plan->title}\" for approval.",
                    'is_read' => false,
                ]);
            }
        }

        return response()->json(['success' => true, 'plan' => $plan], 201);
    }

    /**
     * PATCH /api/activity-plans/{id}/status
     * Coordinator approves or rejects an activity plan.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $plan = ActivityPlan::with('instructor')->findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:Draft,Pending,Approved,Rejected',
        ]);

        $plan->update(['status' => $data['status']]);

        // Notify the instructor
        if ($plan->instructor) {
            $statusLabel = $data['status'];
            PortalNotification::create([
                'user_id' => $plan->instructor_id,
                'type'    => 'system',
                'title'   => "Activity Plan {$statusLabel}",
                'message' => "Your activity plan \"{$plan->title}\" has been {$statusLabel}.",
                'is_read' => false,
            ]);
        }

        return response()->json(['success' => true, 'plan' => $plan]);
    }

    /**
     * DELETE /api/activity-plans/{id}
     * Remove a draft activity plan.
     */
    public function destroy(int $id): JsonResponse
    {
        ActivityPlan::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
