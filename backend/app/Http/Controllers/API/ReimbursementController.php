<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Helpers\HTTPResponse;

class ReimbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reimbursements = Reimbursement::with(['category', 'user'])->latest()->get();
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
            ]);

            $reimbursement = Reimbursement::create([
                'user_id'     => Auth::id(),
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'amount'      => $validated['amount'],
                'category_id' => $validated['category_id'],
                'submitted_at'=> now(),
                'status'      => 'pending',
            ]);

            activity()
                ->performedOn($reimbursement)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'submitted'])
                ->log('Reimbursement submission');

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
                'title'       => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'amount'      => 'sometimes|numeric|min:0',
                'category_id' => 'sometimes|exists:categories,id',
                'status'      => ['required', Rule::in(['approved', 'rejected'])],
            ]);

            $reimbursement->fill($validated);

            if ($validated['status'] === 'approved') {
                $reimbursement->approved_at = now();
            }

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
        $reimbursement->load(['category', 'user']);
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
}
