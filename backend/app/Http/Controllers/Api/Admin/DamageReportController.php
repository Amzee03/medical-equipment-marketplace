<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DamageReportController extends Controller
{
    /**
     * List semua damage reports, filter by status.
     */
    public function index(Request $request)
    {
        $query = DamageReport::with(['rentalItem.orderItem.order.user', 'rentalItem.equipmentUnit', 'reportedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(20);

        return response()->json($reports);
    }

    /**
     * Admin isi biaya dan update status damage report.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'repair_cost' => 'nullable|numeric|min:0',
            'replacement_cost' => 'nullable|numeric|min:0',
            'status' => ['required', Rule::in(['charged', 'waived'])],
            'description' => 'nullable|string',
        ]);

        $report = DamageReport::findOrFail($id);

        if ($report->status !== 'pending') {
            return response()->json(['message' => 'Laporan ini sudah diproses sebelumnya.'], 422);
        }

        $report->fill([
            'repair_cost' => $request->repair_cost,
            'replacement_cost' => $request->replacement_cost,
            'status' => $request->status,
        ]);

        if ($request->filled('description')) {
            $report->description = $request->description;
        }

        $report->save();

        return response()->json([
            'message' => 'Damage report berhasil diperbarui.',
            'data' => $report,
        ]);
    }
}
