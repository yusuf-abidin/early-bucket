<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Branch;
use App\Models\Category;
use App\Models\PerformanceEtape;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PerformanceEtapeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $etapeNo = $request->input('etape_no', 1);
        $userIds = $request->input('user_ids', []);

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
        }])->get();

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


        $displayData = $areas->map(function ($area) use ($performanceEtapes, $month, $year, $etapeNo) {
            $areaBranches = $area->branches->map(function ($branch) use ($performanceEtapes, $month, $year, $etapeNo) {
                $performance = $performanceEtapes->get($branch->id);

                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'area_id' => $branch->area_id,
                    'performance_id' => $performance?->id,
                    'user_id' => $performance?->user_id,
                    'user_name' => $performance?->user?->name,
                    'user' => $performance?->user,
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
                'total_prognosa' => $totalPrognosaArea,  // Tambahkan ini
                'total_branches_filled' => $totalBranchesFilled,  // Tambahkan ini
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


        return Inertia::render('performance/Etape', [
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
                'etape_no' => $etapeNo,
                'user_ids' => $userIds,
            ],
            'metadata' => [
                'total_areas' => $displayData->count(),
                'total_branches' => $displayData->sum(fn($area) => $area['branches']->count()),
                'total_filled' => $performanceEtapes->count(),
            ]
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'branch_id' => ['required' , 'exists:branches,id'],
                'etape_no' => ['required', 'integer', 'in:1,2,3'],
                'year' => ['required', 'integer'],
                'month' => ['required', 'integer', 'between:1,12'],
                'user_id' => ['nullable', 'exists:users,id'],
                'komitmen_etape_id' => ['nullable', 'exists:categories,id'],
                'komitmen_eom_bc_id' => ['nullable', 'exists:categories,id'],
                'komitmen_eom_bm_id' => ['nullable', 'exists:categories,id'],
                'prognosa_akhir_bulan' => ['nullable', 'numeric'],
                'kendala' => ['nullable', 'string'],
            ]);

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
            'performance.*.etape_no' => 'required|integer|in:1,2,3',
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

}
