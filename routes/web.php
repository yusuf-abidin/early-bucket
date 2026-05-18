<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if(Auth::check()) {
        if (Auth::user()->role === 'admin' || Auth::user()->role === 'user') {
            return redirect()->route('dashboard');
        }

        if (Auth::user()->role === 'rlqh')
            return redirect()->route('rlqh.tasks.index');
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {

//        DASHBOARD
        Route::post('change-hero', [\App\Http\Controllers\DashboardController::class, 'changeHero'])->name('dashboard.change-hero');

//        MANAJEMEN USER
        Route::get('users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users.index');
        Route::get('users/create', [\App\Http\Controllers\AdminController::class, 'create'])->name('users.create');
        Route::get('users/{user}/edit', [\App\Http\Controllers\AdminController::class, 'edit'])->name('users.edit');
        Route::post('users', [\App\Http\Controllers\AdminController::class, 'store'])->name('users.store');
        Route::patch('users/{user}', [\App\Http\Controllers\AdminController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [\App\Http\Controllers\AdminController::class, 'destroy'])->name('users.destroy');

        Route::prefix('rlqh')->name('rlqh.')->group(function () {
           Route::get('users', [\App\Http\Controllers\AdminRlqhController::class, 'users'])->name('users.index');
           Route::get('users/create', [\App\Http\Controllers\AdminRlqhController::class, 'create'])->name('users.create');
           Route::get('users/{user}/edit', [\App\Http\Controllers\AdminRlqhController::class, 'edit'])->name('users.edit');
        });

//        MANAJEMEN KATEGORI
        Route::get('categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
        Route::post('categories/bulk-update', [\App\Http\Controllers\CategoryController::class, 'bulkUpdate'])->name('categories.bulkUpdate');
        Route::post('categories/reorder', [\App\Http\Controllers\CategoryController::class, 'reorder'])->name('categories.reorder');
        Route::put('categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');

//        MANAJEMEN REGIONAL
        Route::get('regionals', [\App\Http\Controllers\RegionalController::class, 'index'])->name('regionals.index');
        Route::post('regionals', [\App\Http\Controllers\RegionalController::class, 'store'])->name('regionals.store');
        Route::patch('regionals/{regional}', [\App\Http\Controllers\RegionalController::class, 'update'])->name('regionals.update');

        Route::get('areas', [\App\Http\Controllers\RegionalController::class, 'index'])->name('areas.index');

        //        MANAJEMEN AREA
        Route::post('areas', [\App\Http\Controllers\AreaController::class, 'store'])->name('areas.store');
        Route::patch('areas/{area}', [\App\Http\Controllers\AreaController::class, 'update'])->name('areas.update');
        Route::delete('areas/{area}', [\App\Http\Controllers\AreaController::class, 'destroy'])->name('areas.destroy');

//        MANAJEMEN BRANCH
        Route::post('branches', [\App\Http\Controllers\BranchController::class, 'store'])->name('branches.store');
        Route::patch('branches/{branch}', [\App\Http\Controllers\BranchController::class, 'update'])->name('branches.update');
        Route::delete('branches/{branch}', [\App\Http\Controllers\BranchController::class, 'destroy'])->name('branches.destroy');
    });

    Route::prefix('rlqh')->name('rlqh.')->group(function () {
        Route::get('all-news', [\App\Http\Controllers\ArticleController::class, 'authorIndex'])->name('news.authorIndex');
        Route::get('news/create', [\App\Http\Controllers\ArticleController::class, 'create'])->name('news.create');
        Route::get('news/{article}/edit', [\App\Http\Controllers\ArticleController::class, 'edit'])->name('news.edit');
        Route::post('news', [\App\Http\Controllers\ArticleController::class, 'store'])->name('news.store');
        Route::patch('news/{article}', [\App\Http\Controllers\ArticleController::class, 'update'])->name('news.update');
        Route::delete('news/{article}', [\App\Http\Controllers\ArticleController::class, 'destroy'])->name('news.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| PENDING MATTER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,user'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('', [\App\Http\Controllers\TaskController::class, 'index'])->name('index');
        Route::get('history', [\App\Http\Controllers\TaskController::class, 'taskHistory'])->name('history');
    });

    Route::prefix('memos')->name('memos.')->group(function () {
        Route::get('', [\App\Http\Controllers\MemoController::class, 'index'])->name('index');
        Route::post('', [\App\Http\Controllers\MemoController::class, 'store'])->name('store');
        Route::patch('{memo}', [\App\Http\Controllers\MemoController::class, 'update'])->name('update');
        Route::delete('{memo}', [\App\Http\Controllers\MemoController::class, 'destroy'])->name('destroy');
        Route::get('archive', [\App\Http\Controllers\MemoController::class, 'archive'])->name('archive');
    });

    Route::prefix('debtor-savings')->name('debtor-savings.')->group(function () {
        Route::get('', [\App\Http\Controllers\DebtorSavingsController::class, 'index'])->name('index');
        Route::post('', [\App\Http\Controllers\DebtorSavingsController::class, 'store'])->name('store');
        Route::patch('{debtorSaving}', [\App\Http\Controllers\DebtorSavingsController::class, 'update'])->name('update');
        Route::delete('{debtorSaving}', [\App\Http\Controllers\DebtorSavingsController::class, 'destroy'])->name('destroy');
    });

    Route::get('etape', [\App\Http\Controllers\PerformanceEtapeController::class, 'index'])->name('etape.index');
    Route::post('etape', [\App\Http\Controllers\PerformanceEtapeController::class, 'store'])->name('etape.store');
    Route::post('etape/bulk', [\App\Http\Controllers\PerformanceEtapeController::class, 'bulkStore'])->name('etape.bulk');
    Route::get('eom', [\App\Http\Controllers\PerformanceEtapeController::class, 'endOfMonth'])->name('eom.index');

    Route::prefix('contact-cluster')->name('contact-cluster.')->group(function () {
        Route::get('', [\App\Http\Controllers\ContactClusterController::class, 'index'])->name('index');
        Route::post('', [\App\Http\Controllers\ContactClusterController::class, 'store'])->name('store');
        Route::patch('{contactCluster}', [\App\Http\Controllers\ContactClusterController::class, 'update'])->name('update');
        Route::delete('{contactCluster}', [\App\Http\Controllers\ContactClusterController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('stc-tl-contact')->name('stc-tl-contact.')->group(function () {
        Route::post('', [\App\Http\Controllers\StcTlContactController::class, 'store'])->name('store');
        Route::patch('{stcTlContact}', [\App\Http\Controllers\StcTlContactController::class, 'update'])->name('update');
        Route::delete('{stcTlContact}', [\App\Http\Controllers\StcTlContactController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('performance-log')->name('performance-log.')->group(function () {
        Route::get('', [\App\Http\Controllers\PerformanceLogController::class, 'index'])->name('index');
        Route::post('upsert', [\App\Http\Controllers\PerformanceLogController::class, 'upsert'])->name('upsert');
    });

    Route::prefix('performance-period')->name('performance-period.')->group(function () {
        Route::post('upsert', [\App\Http\Controllers\PerformancePeriodController::class, 'upsert'])->name('upsert');
        Route::post('delete-date/{period}', [\App\Http\Controllers\PerformancePeriodController::class, 'deleteDate'])->name('delete-date');
        Route::post('bulk-update', [\App\Http\Controllers\PerformancePeriodController::class, 'bulkUpdate'])->name('bulk-update');
    });

    Route::prefix('consumer-recap')->name('consumer-recap.')->group(function () {
        Route::get('', [\App\Http\Controllers\ConsumerRecapController::class, 'index'])->name('index');
        Route::patch('upsert', [\App\Http\Controllers\ConsumerRecapController::class, 'upsert'])->name('upsert');
    });
});

Route::middleware(['auth', 'role:rlqh,admin'])->group(function () {
    Route::prefix('rlqh')->name('rlqh.')->group(function () {
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('', [\App\Http\Controllers\RlqhTaskController::class, 'index'])->name('index');
            Route::get('history', [\App\Http\Controllers\RlqhTaskController::class, 'taskHistory'])->name('history');
        });

        Route::prefix('news')->name('news.')->group(function () {
           Route::get('', [\App\Http\Controllers\ArticleController::class, 'index'])->name('index');
           Route::get('show/{article}', [\App\Http\Controllers\ArticleController::class, 'show'])->name('show');
        });
    });
});

Route::middleware(['auth', 'role:admin,user,rlqh'])->group(function () {
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::post('', [\App\Http\Controllers\TaskController::class, 'store'])->name('store');
        Route::patch('{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('update');
        Route::delete('{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('destroy');
    });
});


require __DIR__ . '/settings.php';
