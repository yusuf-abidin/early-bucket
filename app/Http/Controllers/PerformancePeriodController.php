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

    public function bulkUpdate(Request $request) {
        try {
            $data = $request->validate([
                'list_periods' => ['present', 'array'],
                'list_periods.*.id' => ['nullable', 'exists:performance_periods,id'],
                'list_periods.*.month' => ['required', 'integer', 'between:1,12'],
                'list_periods.*.performance_type' => ['required', 'string', 'max:30'],
                'list_periods.*.start_date' => ['nullable', 'integer', 'min:1', 'max:31'],
                'list_periods.*.end_date' => ['nullable', 'integer', 'min:1', 'max:31'],
                'list_periods.*.year' => ['required', 'integer'],
                'list_periods.*.order' => ['nullable', 'integer', 'min:0'],
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui periode: ' . $e->getMessage());
        }

        $incomingData = collect($data['list_periods']);

        \DB::beginTransaction();
        try {
            if ($incomingData->isEmpty()) {
                $month = $request->input('month');
                $year = $request->input('year');
                PerformancePeriod::where('month', $month)
                    ->where('year', $year)
                    ->delete();
                \DB::commit();
                return back()->with('success', 'Periode berhasil dihapus');
            }
            $idsToKeep = $incomingData->pluck('id')->filter()->toArray();

            $years = $incomingData->pluck('year')->unique()->toArray();
            $months = $incomingData->pluck('month')->unique()->toArray();
            PerformancePeriod::whereIn('year', $years)
                ->whereIn('month', $months)
                ->whereNotIn('id', $idsToKeep)
                ->delete();

            $upsertData = $incomingData->map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'month' => $item['month'],
                    'year' => $item['year'],
                    'performance_type' => $item['performance_type'],
                    'start_date' => $item['start_date'] ?? null,
                    'end_date' => $item['end_date'] ?? null,
                    'order' => $item['order'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            PerformancePeriod::upsert($upsertData, ['id'], [
                'month',
                'year',
                'performance_type',
                'start_date',
                'order',
                'end_date',
                'created_at',
                'updated_at'
            ]);

            \DB::commit();
            return back()->with('success', 'Periode berhasil diperbarui');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Gagal memperbarui periode: ' . $e->getMessage());
        }

    }

    public function deleteDate(Request $request, PerformancePeriod $period) {
        $period->update([
            'start_date' => null,
            'end_date' => null,
        ]);
        return redirect()->route('performance-log.index')->with('success', 'Tanggal periode berhasil dihapus');
    }
}
