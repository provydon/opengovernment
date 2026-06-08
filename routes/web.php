<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\Government\GovAuthController;
use App\Http\Controllers\Government\GovDashboardController;
use App\Http\Controllers\Government\GovSpendingController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\IssueVoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpendingCommentController;
use App\Http\Controllers\SpendingRecordController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
| Public reading routes — anyone can browse.
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'brand' => config('opengovernment.brand'),
    ]);
})->name('home');

Route::get('/spending', [SpendingRecordController::class, 'index'])->name('spending.index');
Route::get('/spending/{spendingRecord:slug}', [SpendingRecordController::class, 'show'])->name('spending.show');

Route::get('/issues', [IssueController::class, 'index'])->name('issues.index');
Route::get('/issues/{issue:slug}', [IssueController::class, 'show'])->name('issues.show');

Route::get('/donate', [DonationController::class, 'show'])->name('donate');
Route::post('/donate', [DonationController::class, 'initiate'])->name('donate.initiate');
Route::get('/donate/callback', [DonationController::class, 'callback'])->name('donate.callback');

Route::get('/chat', [ChatController::class, 'show'])->name('chat');
Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');

/*
| Citizen-only writing routes. The `auth` middleware here means the default
| (citizen) `web` guard. Government officials are on a different guard so
| they cannot reach these.
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/issues/new/create', [IssueController::class, 'create'])->name('issues.create');
    Route::post('/issues', [IssueController::class, 'store'])->name('issues.store');
    Route::post('/issues/{issue:slug}/vote', [IssueVoteController::class, 'store'])->name('issues.vote');

    Route::post('/spending/{spendingRecord:slug}/comments', [SpendingCommentController::class, 'store'])->name('spending.comments.store');
});

/*
| Government officials — separate guard, separate URL prefix, separate UI.
*/
Route::prefix('government')->name('government.')->group(function () {
    Route::get('login', [GovAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [GovAuthController::class, 'login']);
    Route::post('logout', [GovAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:government')->group(function () {
        Route::get('dashboard', GovDashboardController::class)->name('dashboard');
        Route::get('spending/new', [GovSpendingController::class, 'create'])->name('spending.create');
        Route::post('spending', [GovSpendingController::class, 'store'])->name('spending.store');
    });
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
