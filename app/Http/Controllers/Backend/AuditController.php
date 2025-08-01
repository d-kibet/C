<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditTrail::with(['user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('model_type')) {
            $query->where('auditable_type', $request->model_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('auditable_id', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $audits = $query->paginate(25);

        // Get filter options
        $users = User::select('id', 'name')->orderBy('name')->get();
        $events = AuditTrail::select('event')->distinct()->orderBy('event')->pluck('event');
        $modelTypes = AuditTrail::select('auditable_type')->distinct()->orderBy('auditable_type')->pluck('auditable_type');

        return view('backend.audit.index', compact('audits', 'users', 'events', 'modelTypes'));
    }

    public function show(AuditTrail $audit)
    {
        $audit->load(['user', 'auditable']);
        return view('backend.audit.show', compact('audit'));
    }

    public function stats(Request $request)
    {
        $days = $request->input('days', 30);
        $dateFrom = now()->subDays($days);

        $stats = [
            'total_activities' => AuditTrail::where('created_at', '>=', $dateFrom)->count(),
            'unique_users' => AuditTrail::where('created_at', '>=', $dateFrom)
                ->distinct('user_id')
                ->whereNotNull('user_id')
                ->count('user_id'),
            'events_breakdown' => AuditTrail::where('created_at', '>=', $dateFrom)
                ->select('event', DB::raw('count(*) as count'))
                ->groupBy('event')
                ->orderBy('count', 'desc')
                ->get(),
            'models_breakdown' => AuditTrail::where('created_at', '>=', $dateFrom)
                ->select('auditable_type', DB::raw('count(*) as count'))
                ->groupBy('auditable_type')
                ->orderBy('count', 'desc')
                ->get(),
            'daily_activity' => AuditTrail::where('created_at', '>=', $dateFrom)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'top_users' => AuditTrail::with('user')
                ->where('created_at', '>=', $dateFrom)
                ->whereNotNull('user_id')
                ->select('user_id', DB::raw('count(*) as count'))
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return view('backend.audit.stats', compact('stats', 'days'));
    }

    public function export(Request $request)
    {
        $query = AuditTrail::with(['user'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('model_type')) {
            $query->where('auditable_type', $request->model_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $audits = $query->limit(1000)->get();

        $filename = 'audit_trail_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($audits) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Date/Time', 'User', 'Event', 'Model', 'Record ID', 
                'IP Address', 'Changes Summary'
            ]);

            foreach ($audits as $audit) {
                $changes = '';
                if ($audit->old_values || $audit->new_values) {
                    $changes = 'Old: ' . json_encode($audit->old_values) . ' | New: ' . json_encode($audit->new_values);
                }

                fputcsv($file, [
                    $audit->id,
                    $audit->created_at->format('Y-m-d H:i:s'),
                    $audit->user ? $audit->user->name : 'System',
                    $audit->event_display,
                    $audit->model_display,
                    $audit->display_id,
                    $audit->ip_address,
                    $changes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}