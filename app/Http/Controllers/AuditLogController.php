<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\AuditLogLabels;
use App\Support\Queries\AuditLogListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $base = AuditLog::query()
            ->select(['id', 'user_id', 'action', 'module', 'record_id', 'description', 'ip_address', 'created_at'])
            ->with(['user:id,name,email']);

        $filtered = AuditLogListQuery::apply(clone $base, $request);

        $auditStats = [
            'matching' => (clone $filtered)->count(),
            'exports' => (clone $filtered)->whereIn('action', ['export', 'download'])->count(),
            'destructive' => (clone $filtered)->where('action', 'delete')->count(),
        ];

        $logs = AuditLogListQuery::apply(clone $base, $request)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        $viewData = [
            'logs' => $logs,
            'users' => $users,
            'modules' => AuditLogLabels::modules(),
            'actions' => AuditLogLabels::actions(),
            'auditStats' => $auditStats,
            'pageTitle' => __('audit.page_title'),
        ];

        if ($request->ajax()) {
            return view('audit-logs.partials.content', $viewData);
        }

        return view('audit-logs.index', $viewData);
    }
}
