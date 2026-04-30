<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertPerformancePeriodRequest;
use App\Models\PerformancePeriod;
use Illuminate\Http\Request;

class PerformancePeriodController extends Controller
{
    public function upsert(UpsertPerformancePeriodRequest $request) {

        $data = $request->validated();
        PerformancePeriod::updateOrCreate(
            [
                'month' => $data['month'],
                'year' => $data['year'],
                'performance_type' => $data['performance_type'],
            ],
            [
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]
        );
        return redirect()->route('performance-log.index')->with('success', 'Period berhasil diperbarui.');
    }

    public function deleteDate(Request $request, PerformancePeriod $period) {
        $period->update([
            'start_date' => null,
            'end_date' => null,
        ]);
        return redirect()->route('performance-log.index')->with('success', 'Tanggal periode berhasil dihapus');
    }
}
