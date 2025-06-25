<?php

namespace App\Http\Controllers\API;

use App\Enums\ReimbursementStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Helpers\HTTPResponse;
use App\Mail\NewReimbursementSubmission;
use App\Models\Category;
use App\Models\User;
use App\Notifications\NewReimbursementSubmission as NotificationsNewReimbursementSubmission;
use App\Notifications\NewReimbursementSubmissionNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class ReimbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Reimbursement::with(['category', 'user', 'reviewedBy'])->latest();

        if (Auth::user()->hasRole('employee')) {
            $query->where('user_id', Auth::id());
        }

        if (Auth::user()->hasRole('admin')) {
            $query->withTrashed();
        }

        $reimbursements = $query->get();

        return $this->response()->success($reimbursements, 'Reimbursements retrieved successfully.');
    }

    /**
     * Store a new reimbursement submission (by employee).
     */
    public function submission(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'amount'      => 'required|numeric|min:0',
                'category_id' => 'required|exists:categories,id',
                'attachment'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            // limit per per month
            $category = Category::findOrFail($validated['category_id']);
            $limit = $category->limit_per_month;

            $currentMonth = Carbon::now()->startOfMonth();
            $totalUsed = Reimbursement::where('user_id', Auth::id())
                ->where('category_id', $validated['category_id'])
                ->whereBetween('submitted_at', [$currentMonth, now()])
                ->sum('amount');

            if (($totalUsed + $validated['amount']) > $limit) {
                return $this->response()->validation([
                    'limit'     => $limit,
                    'used'      => $totalUsed,
                    'remaining' => max(0, $limit - $totalUsed)
                ], 'Reimbursement exceeds monthly limit for this category.');
            }

            // upload file attachment 
            $filePath = null;
            if ($request->hasFile('attachment')) {
                $filePath = $request->file('attachment')->store('attachments', 'public');
            }

            $reimbursement = Reimbursement::create([
                'user_id'             => Auth::id(),
                'title'               => $validated['title'],
                'description'         => $validated['description'] ?? null,
                'amount'              => $validated['amount'],
                'category_id'         => $validated['category_id'],
                'submitted_at'        => now(),
                'status'              => ReimbursementStatusEnum::PENDING,
                'attachment_path' => $filePath,
            ]);

            activity()
                ->performedOn($reimbursement)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'submitted'])
                ->log('Reimbursement submission');

            $managers = User::role('manager')->get();
            foreach ($managers as $manager) {
                $manager->notify(new NewReimbursementSubmissionNotification($reimbursement));
            }

            return $this->response()->created($reimbursement, 'Reimbursement submitted successfully.');
        } catch (\Throwable $e) {
            return $this->response()->error($request, $e);
        }
    }

    /**
     * Approve or reject a reimbursement (by manager).
     */
    public function approval(Request $request, Reimbursement $reimbursement)
    {
        try {
            $validated = $request->validate([
                'approval_reason' => 'nullable',
                'status' => ['required', Rule::in([
                    ReimbursementStatusEnum::APPROVED->value,
                    ReimbursementStatusEnum::REJECTED->value,
                ])],
            ]);

            $reimbursement->fill($validated);

            if ($validated['status'] === 'approved') {
                $reimbursement->approved_at = now();
            }

            $reimbursement->approval_reason = $validated['approval_reason'] ?? '';
            $reimbursement->reviewed_by = auth()->user()->id;
            $reimbursement->status = $validated['status'];
            $reimbursement->save();

            activity()
                ->performedOn($reimbursement)
                ->causedBy(Auth::user())
                ->withProperties(['action' => $validated['status']])
                ->log("Reimbursement {$validated['status']}");

            $action = $validated['status'] === 'approved' ? 'approved' : 'rejected';
            return $this->response()->success($reimbursement, "Reimbursement {$action} successfully.");
        } catch (\Throwable $e) {
            return $this->response()->error($request, $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reimbursement $reimbursement)
    {
        $reimbursement->load(['category', 'user', 'reviewedBy']);
        return $this->response()->success($reimbursement, 'Reimbursement details retrieved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reimbursement $reimbursement)
    {
        try {
            $reimbursement->delete();
            return $this->response()->success([], 'Reimbursement deleted successfully.');
        } catch (\Throwable $e) {
            return $this->response()->error(request(), $e);
        }
    }

    /**
     * Return dashboard metrics.
     */
    public function metrics()
    {
        $user     = Auth::user();
        $isAdmin  = $user->hasRole(['admin', 'manager']); // gunakan Spatie roles

        $baseQuery = Reimbursement::query();
        if (! $isAdmin) {
            $baseQuery->where('user_id', $user->id);
        }

        $totalRequests = (clone $baseQuery)->count();

        $pending  = (clone $baseQuery)
            ->where('status', ReimbursementStatusEnum::PENDING->value)
            ->count();
        $approved = (clone $baseQuery)
            ->where('status', ReimbursementStatusEnum::APPROVED->value)
            ->count();
        $rejected = (clone $baseQuery)
            ->where('status', ReimbursementStatusEnum::REJECTED->value)
            ->count();

        $totalAmount = (clone $baseQuery)
            ->where('status', ReimbursementStatusEnum::APPROVED->value)
            ->sum('amount');

        return $this->response()->success([
            'totalRequests' => $totalRequests,
            'pending'       => $pending,
            'approved'      => $approved,
            'rejected'      => $rejected,
            'totalAmount'   => $totalAmount,
        ], 'Metrics retrieved successfully.');
    }
}
