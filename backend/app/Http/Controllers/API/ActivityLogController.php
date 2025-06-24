<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $logs = Activity::with(['causer', 'subject'])
            ->when(
                $request->has('log_name'),
                fn($q) =>
                $q->where('log_name', $request->log_name)
            )
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->response()
            ->success($logs, 'Activity logs retrieved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $log = Activity::with(['causer', 'subject'])->findOrFail($id);

        return $this->response()
            ->success($log, 'Activity log retrieved successfully.');
    }
}
