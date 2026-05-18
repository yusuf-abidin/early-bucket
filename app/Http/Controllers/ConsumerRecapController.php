<?php

namespace App\Http\Controllers;

use App\Models\ConsumerRecap;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ConsumerRecapController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $recaps = ConsumerRecap::where('year', $year)
            ->get()
            ->groupBy('month');


        $recapPerformance = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthData = $recaps->get($month, collect());

            $monthDataKeyedByDay = $monthData->keyBy(function ($item) {
                return (int) $item->date;
            });

            $daysData = [];

            $totalDaysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

            for ($day = 1; $day <= $totalDaysInMonth; $day++) {
                $existingDayData = $monthDataKeyedByDay->get($day);
                $daysData[] = [
                    'id' => $existingDayData ? $existingDayData->id : null,
                    'date' => $day,
                    'consumer' => $existingDayData ? $existingDayData->consumer : null,
                    'percent' => $existingDayData ? $existingDayData->percent : null,
                ];
            }

            $recapPerformance[] = [
                'value' => $month,
                'isEditing' => false,
                'days' => $daysData
            ];
        }

        return Inertia::render('performance/ConsumerRecap', [
            'consumer_recaps' => $recapPerformance,
            'selected_year' =>(int) $year
        ]);
    }

    public function upsert(Request $request) {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
            'date' => ['required', 'integer', 'between:1,31'],
            'field' => 'required|in:consumer,percent',
            'value' => 'nullable',
        ]);

        $request->validate([
            'value' => [
                'nullable',
                Rule::when($request->field === 'consumer', ['integer']),
                Rule::when($request->field === 'percent', ['numeric', 'between:0,100']),
            ]
        ]);

        ConsumerRecap::updateOrCreate(
            [
                'year' => $validated['year'],
                'month' => $validated['month'],
                'date' => $validated['date'],
            ],
            [
                $validated['field'] => $validated['value']
            ]
        );

        return back();
    }

}
