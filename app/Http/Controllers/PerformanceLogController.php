<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertPerformanceLogRequest;
use App\Models\PerformanceLog;
use App\Models\PerformancePeriod;
use App\Models\Regional;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PerformanceLogController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $performanceTypes = ['etape_1', 'etape_2', 'etape_3', 'eom'];

        $existingPeriods = PerformancePeriod::where('year', $year)->get()->keyBy(
            fn($p) => "{$p->month}_{$p->performance_type}"
        );

        $periods = [];

        for ($month = 1; $month <= 12; $month++) {
            $byType = [];
            foreach ($performanceTypes as $type) {
                $key = "{$month}_{$type}";
                $period = $existingPeriods->get($key);
                $byType[$type] = $period ? [
                    'id' => $period->id,
                    'month' => $month,
                    'type' => $type,
                    'start_date' => $period->start_date,
                    'end_date' => $period->end_date,
                ] : [
                    'id' => null,
                    'month' => $month,
                    'type' => $type,
                    'start_date' => null,
                    'end_date' => null,
                ];
            }
            $periods[$month] = $byType;
        }

        $logsRaw = PerformanceLog::whereHas('performancePeriod', fn($q) => $q->where('year', $year))->get();

        $logIndex = [];
        foreach ($logsRaw as $log) {
            $pid = $log->performance_period_id;
            if ($log->regional_id) {
                $logIndex[$pid]['regional'][$log->regional_id] = $log;
            } elseif ($log->area_id) {
                $logIndex[$pid]['area'][$log->area_id] = $log;
            } elseif ($log->branch_id) {
                $logIndex[$pid]['branch'][$log->branch_id] = $log;
            }
        }

        $regionals = Regional::with([
            'areas' => fn($q) => $q->orderBy('name'),
            'areas.branches' => fn($q) => $q->orderBy('name'),
            'branches' => fn($q) => $q->whereNull('area_id')->orderBy('name'),
        ])->orderBy('name')->get();


        return Inertia::render('performance/Rekapitulasi', [
            'year'        => (int) $year,
            'periods'     => $periods,
            'log_index'   => $logIndex,
            'regionals'   => $regionals,
        ]);
    }

    public function upsert(UpsertPerformanceLogRequest $request)
    {
        $validated = $request->validated();

        \DB::transaction(function () use ($validated) {
            if (!empty($validated['period_id'])) {
                $period = PerformancePeriod::findOrFail($validated['period_id']);
            }else {
                $period = PerformancePeriod::firstOrCreate(
                    [
                        'year' => $validated['year'],
                        'month' => $validated['month'],
                        'performance_type' => $validated['performance_type'],
                    ],
                    [
                        'start_date' => $validated['start_date'] ?? null,
                        'end_date' => $validated['end_date'] ?? null,
                    ]
                );
            }

            $entityColumn = match ($validated['entity_type']) {
                'regional' => 'regional_id',
                'area' => 'area_id',
                'branch' => 'branch_id',
            };

            PerformanceLog::updateOrCreate(
                [
                    'performance_period_id' => $period->id,
                    $entityColumn => $validated['entity_id'],
                ],
                [
                    'is_achieved' => $validated['is_achieved'],
                ]
            );
        });

        return redirect()->back()->with('success', 'Data performa berhasil diperbarui.');
    }
}
