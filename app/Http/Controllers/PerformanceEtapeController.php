<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertPerformanceEtapeRequest;
use App\Models\Category;
use App\Models\PerformanceEtape;
use App\Models\Regional;
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
                    'branch_id'  => $validated['branch_id'],
                    'etape_no'   => $validated['etape_no'],
                    'year'       => $validated['year'],
                    'month'      => $validated['month'],
                ],
                [
                    'user_id'                => $validated['user_id'],
                    'komitmen_etape_bc_id'   => $validated['komitmen_etape_bc_id'],
                    'komitmen_etape_bm_id'   => $validated['komitmen_etape_bm_id'],
                    'prognosa_akhir_bulan'   => $validated['prognosa_akhir_bulan'],
                    'kendala'                => $validated['kendala'],
                ]
            );

            return redirect()->back()->with('success', 'Data berhasil disimpan.');
        } catch (\Exception $exception) {
            return back()->withErrors(['message' => 'Gagal menyimpan data: ' . $exception->getMessage()]);
        }
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'performance'                           => 'required|array',
            'performance.*.branch_id'               => 'required|exists:branches,id',
            'performance.*.etape_no'                => ['required', Rule::in(['1', '2', '3', 'eom', 'program_khusus'])],
            'performance.*.year'                    => 'required|integer',
            'performance.*.month'                   => 'required|integer|between:1,12',
            'performance.*.user_id'                 => 'nullable|exists:users,id',
            'performance.*.komitmen_etape_bc_id'    => 'nullable|exists:categories,id',
            'performance.*.komitmen_etape_bm_id'    => 'nullable|exists:categories,id',
            'performance.*.prognosa_akhir_bulan'    => 'nullable|numeric',
            'performance.*.kendala'                 => 'nullable|string',
        ]);

        try {
            \DB::beginTransaction();

            PerformanceEtape::upsert(
                $validated['performance'],
                ['branch_id', 'etape_no', 'year', 'month'],
                ['user_id', 'komitmen_etape_bc_id', 'komitmen_etape_bm_id', 'prognosa_akhir_bulan', 'kendala'],
            );

            \DB::commit();
            return redirect()->back()->with('success', 'Data berhasil disimpan.');
        } catch (\Exception $exception) {
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
        $month   = $request->input('month', now()->month);
        $year    = $request->input('year', now()->year);
        $etapeNo = $request->input('etape_no', 1);
        $userIds = $request->input('user_ids', []);

        $previousMonth = $month - 1;
        $previousYear  = $year;

        if ($previousMonth < 1) {
            $previousMonth = 12;
            $previousYear  = $year - 1;
        }

        // --- Load Regional > Area > Branch hierarchy ---
        // Setiap area pasti punya regional.
        // Setiap branch bisa punya area (branch->area_id != null)
        // atau langsung di bawah regional (branch->area_id == null, branch->regional_id != null).

        $regionals = Regional::with([
            'areas' => function ($q) {
                $q->orderBy('name', 'asc');
            },
            'areas.branches' => function ($q) {
                $q->orderBy('name', 'asc');
            },
            // Relasi branches() di model mencakup semua cabang,
            // kita filter whereNull('area_id') untuk cabang langsung di bawah regional
            'branches' => function ($q) {
                $q->whereNull('area_id')->orderBy('name', 'asc');
            },
        ])
            ->orderBy('name', 'asc')
            ->get();

        // --- Kumpulkan semua branch_id yang terlibat ---
        // regional->branches sudah difilter whereNull('area_id') di eager load,
        // regional->areas->branches mencakup semua cabang yang punya area
        $allBranchIds = collect();
        foreach ($regionals as $regional) {
            foreach ($regional->areas as $area) {
                $allBranchIds = $allBranchIds->merge($area->branches->pluck('id'));
            }
            // $regional->branches di sini hanya direct branches (whereNull area_id)
            $allBranchIds = $allBranchIds->merge($regional->branches->pluck('id'));
        }

        // --- Query performance bulan ini ---
        $performanceEtapes = PerformanceEtape::with([
            'user:id,name,color_id',
            'user.color:id,name,class',
            'komitmenEtapeBC:id,name,color_id',
            'komitmenEtapeBC.color:id,name,class',
            'komitmenEtapeBM:id,name,color_id',
            'komitmenEtapeBM.color:id,name,class',
        ])
            ->where('month', $month)
            ->where('year', $year)
            ->where('etape_no', $etapeNo)
            ->when(!empty($userIds), fn($q) => $q->whereIn('user_id', $userIds))
            ->get()
            ->keyBy('branch_id');

        // --- Query PIC bulan sebelumnya (hanya jika tidak ada filter user_ids) ---
        // Ketika user_ids difilter, cabang tanpa data bulan ini tidak ditampilkan,
        // sehingga tidak perlu fallback ke bulan sebelumnya.
        $previousMonthPics = collect();

        if (empty($userIds)) {
            $branchesWithoutData = $allBranchIds->diff($performanceEtapes->keys());

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
        }

        // --- Helper: bangun data satu branch ---
        // Mengembalikan null jika cabang tidak lolos filter user_ids (is_new saat filter aktif).
        $buildBranchData = function ($branch) use ($performanceEtapes, $previousMonthPics, $month, $year, $etapeNo, $userIds) {
            $performance = $performanceEtapes->get($branch->id);
            $isNew       = !$performance;

            // Kalau filter user_ids aktif dan cabang tidak punya data bulan ini -> lewati
            if (!empty($userIds) && $isNew) {
                return null;
            }

            $previousMonthPic = $isNew ? $previousMonthPics->get($branch->id) : null;

            $displayUserId = $performance?->user_id ?? $previousMonthPic?->user_id;
            $displayUser   = $performance?->user    ?? $previousMonthPic?->user;

            return [
                'branch_id'              => $branch->id,
                'branch_name'            => $branch->name,
                'area_id'                => $branch->area_id,
                'regional_id'            => $branch->regional_id,
                'performance_id'         => $performance?->id,
                'user_id'                => $displayUserId,
                'user_name'              => $displayUser?->name,
                'user'                   => $displayUser,
                'komitmen_etape_bc_id'   => $performance?->komitmen_etape_bc_id,
                'komitmen_etape_bc'      => $performance?->komitmenEtapeBc,
                'komitmen_etape_bm_id'   => $performance?->komitmen_etape_bm_id,
                'komitmen_etape_bm'      => $performance?->komitmenEtapeBm,
                'prognosa_akhir_bulan'   => $performance?->prognosa_akhir_bulan,
                'kendala'                => $performance?->kendala,
                'etape_no'               => $etapeNo,
                'month'                  => $month,
                'year'                   => $year,
                'is_new'                 => $isNew,
                'updated_at'             => $performance?->updated_at,
            ];
        };

        // --- Susun display data: Regional > Area > Branch ---
        $displayData = $regionals->map(function ($regional) use ($buildBranchData) {

            // Area-area di bawah regional ini, beserta cabangnya
            $areasData = $regional->areas->map(function ($area) use ($buildBranchData) {
                // Filter null (cabang yang dilewati karena filter user_ids)
                $areaBranches         = $area->branches->map($buildBranchData)->filter()->values();
                $totalPrognosaArea    = $areaBranches->sum('prognosa_akhir_bulan');
                $totalBranchesFilled  = $areaBranches->filter(fn($b) => !$b['is_new'])->count();

                return [
                    'id'                    => $area->id,
                    'name'                  => $area->name,
                    'branches'              => $areaBranches,
                    'total_prognosa'        => $totalPrognosaArea,
                    'total_branches_filled' => $totalBranchesFilled,
                ];
            })->filter(fn($a) => $a['branches']->isNotEmpty())->values();

            // Cabang yang langsung di bawah regional (tanpa area)
            // Filter null (cabang yang dilewati karena filter user_ids)
            $directBranches = $regional->branches->map($buildBranchData)->filter()->values();

            // Total prognosa regional = jumlah dari semua area + cabang langsung
            $totalPrognosaRegional = $areasData->sum('total_prognosa')
                + $directBranches->sum('prognosa_akhir_bulan');

            $totalFilledRegional = $areasData->sum('total_branches_filled')
                + $directBranches->filter(fn($b) => !$b['is_new'])->count();

            // PIC regional diambil dari cabang pertama yang punya user
            // (sesuai kesepakatan: PIC dipilih di level regional dan disebarkan ke semua cabang)
            $picRegional = null;
            foreach ($areasData as $area) {
                foreach ($area['branches'] as $branch) {
                    if ($branch['user']) { $picRegional = $branch['user']; break 2; }
                }
            }
            if (!$picRegional) {
                foreach ($directBranches as $branch) {
                    if ($branch['user']) { $picRegional = $branch['user']; break; }
                }
            }

            return [
                'id'                    => $regional->id,
                'name'                  => $regional->name,
                'areas'                 => $areasData,
                'direct_branches'       => $directBranches,
                'pic'                   => $picRegional,
                'total_prognosa'        => $totalPrognosaRegional,
                'total_branches_filled' => $totalFilledRegional,
            ];
        })->filter(function ($regional) {
            return $regional['areas']->isNotEmpty() || $regional['direct_branches']->isNotEmpty();
        })->values();

        $totalPrognosaNasional = $performanceEtapes->sum('prognosa_akhir_bulan');

        $users      = User::select('id', 'name')->get();
        $categories = [
            'komitmen_etape_bc' => Category::where('type', PerformanceEtape::TYPE_ETAPE_BC)
                ->orderBy('order')
                ->with('color:id,name,class')
                ->get(['id', 'name', 'color_id']),
            'komitmen_etape_bm' => Category::where('type', PerformanceEtape::TYPE_ETAPE_BM)
                ->orderBy('order')
                ->with('color:id,name,class')
                ->get(['id', 'name', 'color_id']),
        ];

        return [
            'areas'    => $displayData,   // key tetap 'areas' agar kompatibel dengan prop Vue
            'users'    => $users,
            'categories' => $categories,
            'nasional' => [
                'total_prognosa'  => $totalPrognosaNasional,
                'total_branches'  => $performanceEtapes->count(),
            ],
            'filters' => [
                'month'   => $month,
                'year'    => $year,
                'etapeNo' => $etapeNo,
                'userIds' => $userIds,
            ],
            'metadata' => [
                'total_regionals' => $displayData->count(),
                'total_areas'     => $displayData->sum(fn($r) => $r['areas']->count()),
                'total_branches'  => $displayData->sum(
                    fn($r) => $r['areas']->sum(fn($a) => $a['branches']->count())
                        + $r['direct_branches']->count()
                ),
                'total_filled'    => $performanceEtapes->count(),
            ],
        ];
    }
}
