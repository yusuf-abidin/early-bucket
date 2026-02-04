<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if(Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
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

//        MANAJEMEN KATEGORI
        Route::get('categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
        Route::post('categories/bulk-update', [\App\Http\Controllers\CategoryController::class, 'bulkUpdate'])->name('categories.bulkUpdate');
        Route::post('categories/reorder', [\App\Http\Controllers\CategoryController::class, 'reorder'])->name('categories.reorder');
        Route::put('categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');

//        MANAJEMEN AREA
        Route::get('areas', [\App\Http\Controllers\AreaController::class, 'index'])->name('areas.index');
        Route::post('areas', [\App\Http\Controllers\AreaController::class, 'store'])->name('areas.store');
        Route::patch('areas/{area}', [\App\Http\Controllers\AreaController::class, 'update'])->name('areas.update');
        Route::delete('areas/{area}', [\App\Http\Controllers\AreaController::class, 'destroy'])->name('areas.destroy');

//        MANAJEMEN BRANCH
        Route::post('branches', [\App\Http\Controllers\BranchController::class, 'store'])->name('branches.store');
        Route::patch('branches/{branch}', [\App\Http\Controllers\BranchController::class, 'update'])->name('branches.update');
        Route::delete('branches/{branch}', [\App\Http\Controllers\BranchController::class, 'destroy'])->name('branches.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| PENDING MATTER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('', [\App\Http\Controllers\TaskController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\TaskController::class, 'create'])->name('create');
        Route::get('{task}/edit', [\App\Http\Controllers\TaskController::class, 'edit'])->name('edit');
        Route::post('', [\App\Http\Controllers\TaskController::class, 'store'])->name('store');
        Route::patch('{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('update');
        Route::get('history', [\App\Http\Controllers\TaskController::class, 'taskHistory'])->name('history');
        Route::delete('{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('destroy');

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
});


require __DIR__ . '/settings.php';
