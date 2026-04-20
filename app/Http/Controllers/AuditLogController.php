<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(50);
        return view('audit-logs.index', compact('logs'));
    }
}