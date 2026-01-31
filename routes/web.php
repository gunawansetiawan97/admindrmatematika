<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\ClassroomController as AdminClassroomController;
use App\Http\Controllers\XenditWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Auth Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login']);
    });

    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout')->middleware('admin');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Packages
    Route::get('/packages/import', [AdminPackageController::class, 'import'])->name('packages.import');
    Route::post('/packages/import', [AdminPackageController::class, 'processImport'])->name('packages.process-import');
    Route::get('/packages/template', [AdminPackageController::class, 'downloadTemplate'])->name('packages.template');
    Route::resource('packages', AdminPackageController::class);

    // Questions
    Route::get('/packages/{package}/questions/create', [AdminQuestionController::class, 'create'])->name('questions.create');
    Route::post('/packages/{package}/questions', [AdminQuestionController::class, 'store'])->name('questions.store');
    Route::get('/packages/{package}/questions/{question}/edit', [AdminQuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/packages/{package}/questions/{question}', [AdminQuestionController::class, 'update'])->name('questions.update');
    Route::delete('/packages/{package}/questions/{question}', [AdminQuestionController::class, 'destroy'])->name('questions.destroy');

    // Students
    Route::get('/students', [AdminStudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [AdminStudentController::class, 'show'])->name('students.show');

    // Results
    Route::get('/packages/{package}/results', [AdminResultController::class, 'index'])->name('results.index');
    Route::get('/results/{attempt}', [AdminResultController::class, 'show'])->name('results.show');
    Route::post('/answers/{answer}/grade', [AdminResultController::class, 'gradeEssay'])->name('results.grade');

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/pending-payments', [AdminOrderController::class, 'pendingPayments'])->name('orders.pending-payments');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/payments/{payment}/verify', [AdminOrderController::class, 'verifyPayment'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [AdminOrderController::class, 'rejectPayment'])->name('payments.reject');

    // Subscriptions
    Route::get('/subscriptions/subscribers', [AdminSubscriptionController::class, 'subscribers'])->name('subscriptions.subscribers');
    Route::resource('subscriptions', AdminSubscriptionController::class);

    // Classrooms
    Route::resource('classrooms', AdminClassroomController::class);
    Route::post('/classrooms/{classroom}/members', [AdminClassroomController::class, 'addMember'])->name('classrooms.members.add');
    Route::delete('/classrooms/{classroom}/members/{user}', [AdminClassroomController::class, 'removeMember'])->name('classrooms.members.remove');
    Route::post('/classrooms/{classroom}/activities', [AdminClassroomController::class, 'storeActivity'])->name('classrooms.activities.store');
    Route::delete('/activities/{activity}', [AdminClassroomController::class, 'destroyActivity'])->name('activities.destroy');
    Route::post('/activities/{activity}/pin', [AdminClassroomController::class, 'togglePinActivity'])->name('activities.pin');
});

/*
|--------------------------------------------------------------------------
| Xendit Webhook Routes
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/xendit/invoice', [XenditWebhookController::class, 'handleInvoice'])
    ->name('webhooks.xendit.invoice');
