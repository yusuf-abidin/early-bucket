<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertPerformanceEtapeRequest;
use App\Models\Area;
use App\Models\Category;
use App\Models\PerformanceEtape;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PerformanceEtapeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $this->getPerformanceData($request);
        return Inertia::render('performance/Etape', $data);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(UpsertPerformanceEtapeRequest $request)
    {
        try {

            $validated = $request->validated();

            $performance = PerformanceEtape::updateOrCreate(
                [
                    'branch_id' => $validated['branch_id'],
                    'etape_no' => $validated['etape_no'],
                    'year' => $validated['year'],
                    'month' => $validated['month'],
                ],
                [
                    'user_id' => $validated['user_id'],
                    'komitmen_etape_id' => $validated['komitmen_etape_id'],
                    'komitmen_eom_bc_id' => $validated['komitmen_eom_bc_id'],
                    'komitmen_eom_bm_id' => $validated['komitmen_eom_bm_id'],
                    'prognosa_akhir_bulan' => $validated['prognosa_akhir_bulan'],
                    'kendala' => $validated['kendala'],
                ]
            );

            return redirect()->back()->with('success', 'Data berhasil disimpan.');
        }catch (\Exception $exception){
            return back()->withErrors(['message' => 'Gagal menyimpan data: ' . $exception->getMessage()]);
        }
    }

    public function bulkStore(Request $request) {
        $validated = $request->validate([
            'performance' => 'required|array',
            'performance.*.branch_id' => 'required|exists:branches,id',
            'performance.*.etape_no' => ['required', Rule::in(['1', '2', '3', 'eom'])],
            'performance.*.year' => 'required|integer',
            'performance.*.month' => 'required|integer|between:1,12',
            'performance.*.user_id' => 'nullable|exists:users,id',
            'performance.*.komitmen_etape_id' => 'nullable|exists:categories,id',
            'performance.*.komitmen_eom_bc_id' => 'nullable|exists:categories,id',
            'performance.*.komitmen_eom_bm_id' => 'nullable|exists:categories,id',
            'performance.*.prognosa_akhir_bulan' => 'nullable|numeric',
            'performance.*.kendala' => 'nullable|string',
        ]);

        try {
            \DB::beginTransaction();

            PerformanceEtape::upsert(
                $validated['performance'],
                ['branch_id', 'etape_no', 'year', 'month'],
                ['user_id', 'komitmen_etape_id', 'komitmen_eom_bc_id', 'komitmen_eom_bm_id', 'prognosa_akhir_bulan', 'kendala'],
            );

            \DB::commit();
            return redirect()->back()->with('success', 'Data berhasil disimpan.');
        }catch (\Exception $exception){
            \DB::rollBack();

            return back()->withErrors(['message' => 'Gagal menyimpan data: ' . $exception->getMessage()]);
        }

    }

    public function endOfMonth(Request $request)
    {
        $data = $this->getPerformanceData($request);
        return Inertia::render('performance/EndOfMonth', $data);
    }

    private function getPerformanceData($request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $etapeNo = $request->input('etape_no', 1);
        $userIds = $request->input('user_ids', []);

        $previousMonth = $month - 1;
        $previousYear = $year;

        if ($previousMonth < 1) {
            $previousMonth = 12;
            $previousYear = $year - 1;
        }

        $areas = Area::with(['branches' => function ($query) use ($userIds, $month, $year, $etapeNo) {
            $query->orderBy('name', 'asc');

            if (!empty($userIds)) {
                $query->whereHas('performanceEtapes', function ($q) use ($userIds, $month, $year, $etapeNo) {
                    $q->whereIn('user_id', $userIds)
                        ->where('month', $month)
                        ->where('year', $year)
                        ->where('etape_no', $etapeNo);
                });
            }
        }])
            ->orderBy('name', 'asc')
            ->get();

        $performanceEtapes = PerformanceEtape::with([
            'user:id,name,color_id',
            'user.color:id,name,class',

            'komitmenEtape:id,name,color_id',
            'komitmenEtape.color:id,name,class',

            'komitmenEomBc:id,name,color_id',
            'komitmenEomBc.color:id,name,class',
            'komitmenEomBm:id,name,color_id',
            'komitmenEomBm.color:id,name,class',

        ])
            ->where('month', $month)
            ->where('year', $year)
            ->where('etape_no', $etapeNo)
            ->when(!empty($userIds), function ($query) use ($userIds) {
                $query->whereIn('user_id', $userIds);
            })
            ->get()
            ->keyBy('branch_id');

        $branchesWithoutData = $areas->flatMap(function ($area) {
            return $area->branches->pluck('id');
        })->diff($performanceEtapes->keys());
        $previousMonthPics = collect();

        // Hanya query jika ada branch yang belum memiliki data
        if ($branchesWithoutData->isNotEmpty()) {
            $previousMonthPics = PerformanceEtape::select('branch_id', 'user_id')
                ->with('user:id,name,color_id', 'user.color:id,name,class')
                ->where('month', $previousMonth)
                ->where('year', $previousYear)
                ->where('etape_no', $etapeNo)
                ->whereIn('branch_id', $branchesWithoutData)
                ->get()
                ->keyBy('branch_id');
        }


        $displayData = $areas->map(function ($area) use ($performanceEtapes, $previousMonthPics, $month, $year, $etapeNo) {
            $areaBranches = $area->branches->map(function ($branch) use ($performanceEtapes, $previousMonthPics, $month, $year, $etapeNo) {
                $performance = $performanceEtapes->get($branch->id);
                $isNew = !$performance;

                $previousMonthPic = null;
                if ($isNew) {
                    $previousMonthPic = $previousMonthPics->get($branch->id);
                }

                $displayUserId = $performance?->user_id ?? $previousMonthPic?->user_id;
                $displayUser = $performance?->user ?? $previousMonthPic?->user;

                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'area_id' => $branch->area_id,
                    'performance_id' => $performance?->id,
                    'user_id' => $displayUserId,
                    'user_name' => $displayUser?->name,
                    'user' => $displayUser,
                    'komitmen_etape_id' => $performance?->komitmen_etape_id,
                    'komitmen_etape' => $performance?->komitmenEtape,
                    'komitmen_eom_bc_id' => $performance?->komitmen_eom_bc_id,
                    'komitmen_eom_bc' => $performance?->komitmenEomBc,
                    'komitmen_eom_bm_id' => $performance?->komitmen_eom_bm_id,
                    'komitmen_eom_bm' => $performance?->komitmenEomBm,
                    'prognosa_akhir_bulan' => $performance?->prognosa_akhir_bulan,
                    'kendala' => $performance?->kendala,

                    'etape_no' => $etapeNo,
                    'month' => $month,
                    'year' => $year,
                    'is_new' => !$performance,
                    'updated_at' => $performance?->updated_at,
                ];
            })->values();

            // Hitung total prognosa untuk area ini
            $totalPrognosaArea = $areaBranches->sum('prognosa_akhir_bulan');
            $totalBranchesFilled = $areaBranches->filter(fn($b) => !$b['is_new'])->count();

            return [
                'id' => $area->id,
                'name' => $area->name,
                'branches' => $areaBranches,
                'total_prognosa' => $totalPrognosaArea,
                'total_branches_filled' => $totalBranchesFilled,
            ];
        })->filter(function ($area) {
            return $area['branches']->isNotEmpty();
        })->values();


        $totalPrognosaNasional = $performanceEtapes->sum('prognosa_akhir_bulan');

        $users = User::select('id', 'name')->get();
        $categories = [
            'komitmen_etape' => Category::where('type', 'komitmen_etape')
                ->orderBy('order')
                ->with('color:id,name,class')
                ->get(['id', 'name', 'color_id']),
            'komitmen_eom_bc' => Category::where('type', 'komitmen_EOM_(BC)')
                ->orderBy('order')
                ->with('color:id,name,class')
                ->get(['id', 'name', 'color_id']),
            'komitmen_eom_bm' => Category::where('type', 'komitmen_EOM_(BM)')
                ->orderBy('order')
                ->with('color:id,name,class')
                ->get(['id', 'name', 'color_id']),
        ];

        return [
            'areas' => $displayData,
            'users' => $users,
            'categories' => $categories,
            'nasional' => [
                'total_prognosa' => $totalPrognosaNasional,
                'total_branches' => $performanceEtapes->count(),
            ],
            'filters' => [
                'month' => $month,
                'year' => $year,
                'etapeNo' => $etapeNo,
                'userIds' => $userIds,
            ],
            'metadata' => [
                'total_areas' => $displayData->count(),
                'total_branches' => $displayData->sum(fn($area) => $area['branches']->count()),
                'total_filled' => $performanceEtapes->count(),
            ],
        ];
    }
}
