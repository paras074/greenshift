<?php
// use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GatherCompanyDataController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Settings\LeadSourceController;
use App\Http\Controllers\Settings\LeadStatusController;
use App\Http\Controllers\Settings\PriorityStatusController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Livewire\LeadFunnel;
use App\Http\Controllers\LeadStepController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\DialpadWebhookController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\SignableWebhookController;
use App\Http\Controllers\LeadAssignmentController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\RfqQuoteController;
use App\Http\Controllers\DialpadController;


// // ── By Role ──────────────────────────────────────────────
// Route::middleware(['role:superadmin'])->group(function () {
//     Route::resource('/roles', RoleController::class);
//     Route::resource('/permissions', PermissionController::class);
// });

// // ── By Permission ─────────────────────────────────────────
// Route::middleware(['permission:view users'])->group(function () {
//     Route::get('/users', [UserController::class, 'index']);
// });

// // ── Multiple Roles ────────────────────────────────────────
// Route::middleware(['role:superadmin|admin'])->group(function () {
//     Route::resource('/users', UserController::class);
//     Route::resource('/settings', SettingController::class);
// });

// // ── Multiple Permissions ──────────────────────────────────
// Route::middleware(['permission:view clients|create clients'])->group(function () {
//     Route::resource('/clients', ClientController::class);
// });

Route::get('/run-migration-rescue', function () {
    try {
        // This creates the jobs table structure if it doesn't exist
        Artisan::call('queue:table'); 
        // This executes the migration
        Artisan::call('migrate');
        return 'Migration successful!';
    } catch (\Exception $e) {
        return 'Error or already migrated: ' . $e->getMessage();
    }
});

Route::match(['GET', 'POST'], '/dialpad-webhook', [DialpadWebhookController::class, 'handleWebhook'])->name('dialpad-webhook');
Route::get('/test-dialpad-download', [DialpadWebhookController::class, 'testWebhook']);

Route::match(['GET', 'POST'], '/generate-pdf-lead', [PDFController::class, 'GenerateLoaPdflead'])->name('loa.generate-pdf-lead');
Route::match(['GET', 'POST'], '/generate-pdf', [PDFController::class, 'GenerateLoaPdf'])->name('loa.generate-pdf');
Route::match(['GET', 'POST'], '/gs-pdf', [PDFController::class, 'GenerateSendLoaPdf'])->name('loa.generate-send-pdf');
Route::match(['GET', 'POST'], '/signable-webhook', [SignableWebhookController::class, 'handle'])->name('signable-webhook');

Route::match(['GET', 'POST'], '/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

// Cache Clear Routes
Route::get('/clear-all', function () {
    try {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        
        return redirect()->back()->with('success', 'Cache cleared successfully!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
    }
});

Route::middleware(['auth', 'role:superadmin|admin|manager'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});

Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::resource('roles', RoleController::class);
});

Route::get('/sendgrid-test', function () {
    try {
        Mail::raw('This is a email from the Greenshift CRM using SendGrid API.', function ($message) {
            $message->to('vaishali.perfectweb@gmail.com')
                    ->subject('SendGrid Success!');
        });

        return "<h1>Success!</h1><p>The email was sent to vaishali.perfectweb@gmail.com. Check the inbox and spam folder.</p>";
    } catch (\Exception $e) {
        return "<h1>Error!</h1><p>The email failed to send.</p><p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    }
});

Route::match(['GET', 'POST'], '/verify/{data}', [LeadController::class, 'loa_verify'])->where('data', '.*')->name('loa.verify');
Route::match(['GET', 'POST'], '/verify-loa-doc', [LeadController::class, 'loa_upload_verify'])->name('loa.upload_verify');

Route::middleware('auth')->group(function () {
    Route::match(['GET', 'POST'], '/leads/verify-lead-loa', [LeadController::class, 'VerifyLeadLoa'])->name('leads.verify-lead-loa');
    Route::match(['GET', 'POST'], '/leads/{lead}/rfq', [LeadController::class, 'go_to_rfq'])->name('leads.to-rfq');
    Route::match(['GET', 'POST'], '/leads/proceed-to-rfq', [LeadController::class, 'proceed_to_rfq'])->name('leads.proceed-to-rfq');
    Route::match(['GET', 'POST'], '/leads/{lead}/add-rfq', [LeadController::class, 'add_rfq'])->name('leads.add_rfq');
     
    // ======= Main Dashboard Route =======
    Route::match(['GET', 'POST'], '/dashboard', [DashboardController::class, 'main_dashboard'])->name('dashboard');
    Route::match(['GET', 'POST'], '/dashboard/stats/{type}', [DashboardController::class, 'getChartData'])->name('dashboard.stats');

    // ======= Leads Routes =======
    Route::match(['GET', 'POST'], '/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::match(['GET', 'POST'], '/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::match(['GET', 'POST'], '/leads/store', [LeadController::class, 'manage_lead'])->name('leads.store');
    Route::match(['GET', 'POST'], '/leads/bulk-delete', [LeadController::class, 'bulkDelete'])->name('leads.bulk-delete');
    Route::get('/leads/funnel', LeadFunnel::class)->name('leads.funnel');
    Route::match(['GET', 'POST'], '/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::match(['GET', 'POST'], '/leads/{lead}/update', [LeadController::class, 'manage_lead'])->name('leads.update');
    Route::match(['GET', 'POST'], '/leads/{lead}/delete', [LeadController::class, 'manage_lead'])->name('leads.delete');
    Route::match(['GET', 'POST'], '/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::match(['GET', 'POST'], '/leads/{lead}/assigned-members', [LeadController::class, 'getAssignedMembers'])->name('leads.assigned.members');
    Route::match(['GET', 'POST'], '/leads/{lead}/edit/details', [LeadController::class, 'edit_details'])->name('leads.editdetails');
    Route::match(['POST'], '/leads/{lead}/attach', [LeadController::class, 'attachFile'])->name('leads.attach');
    Route::delete('/attachments/{attachment}', [LeadController::class, 'deleteAttachment'])->name('attachments.delete');
    Route::match(['GET', 'POST'], '/leads/{lead}/verify-loa', [LeadController::class, 'verifyLoa'])->name('leads.verify-loa');

    Route::post('/send-verification-email', [LeadController::class, 'sendVerificationEmail'])->name('send.verification.email');
    Route::post('/verify-passkey', [LeadController::class, 'verifyPasskey'])->name('verify.passkey');
    // ======= Gather Companies Data  =======
    Route::match(['GET', 'POST'], '/search-companies-data-google', [GatherCompanyDataController::class, 'search_companies_data'])->name('gather_companies_data.google_api.search_companies_data');
    Route::match(['GET', 'POST'], '/search-companies-data-housing', [GatherCompanyDataController::class, 'search_companies_data_housing'])->name('gather_companies_data.housing_api.search_companies_data_housing');
    Route::match(['GET', 'POST'], '/save-temp-lead-google', [GatherCompanyDataController::class, 'save_temp_lead'])->name('gather_companies_data.google_api.save_temp_lead');
    Route::match(['GET', 'POST'], '/save-temp-call-note', [GatherCompanyDataController::class, 'save_temp_call_note'])->name('gather_companies_data.google_api.save_temp_call_note');
    Route::match(['GET', 'POST'], '/get-temp-call-note', [GatherCompanyDataController::class, 'get_temp_lead_notes'])->name('gather_companies_data.google_api.get_temp_lead_notes');
    Route::match(['GET', 'POST'], '/make-into-leads', [GatherCompanyDataController::class, 'make_into_leads'])->name('gather_companies_data.google_api.make_into_leads');
    Route::match(['GET', 'POST'], '/save-all-main-leads-google', [GatherCompanyDataController::class, 'save_all_main_leads'])->name('gather_companies_data.google_api.save_all_main_leads');
    Route::match(['GET', 'POST'], '/save-update-temp-lead-google', [GatherCompanyDataController::class, 'save_update_temp_lead'])->name('gather_companies_data.google_api.save_update_temp_lead');
    Route::match(['GET', 'POST'], '/save-all-temp-leads-google', [GatherCompanyDataController::class, 'save_all_temp_leads'])->name('gather_companies_data.google_api.save_all_temp_leads');

    Route::match(['GET', 'POST'], '/save-call-note', [GatherCompanyDataController::class, 'save_call_note'])->name('gather_companies_data.google_api.save_call_note');

    Route::get('/fetch/temp-leads', [GatherCompanyDataController::class, 'existing_temp_leads'])->name('gather_companies_data.existing_temp_leads');
    Route::delete('/temp-leads/bulk-delete', [GatherCompanyDataController::class, 'bulk_delete_temp_leads'])->name('gather_companies_data.temp_leads.bulk_delete');
    Route::delete('/temp-leads/{id}', [GatherCompanyDataController::class, 'destroy_temp_lead'])->name('gather_companies_data.temp_leads.destroy');


    // ======= Profile Routes =======
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ------------ Notification Routes ------------------------
    Route::match(['GET', 'POST'], '/notifications/all', [NotificationController::class, 'index'])->name('notifications.index');
    Route::match(['POST'], '/notifications/mark-read/{id}', [NotificationController::class, 'markRead'])->name('notifications.markRead');

    //---------------Notes routes -----------------------
    Route::match(['POST'], '/note/save', [NoteController::class, 'store'])->name('notes.store');
    Route::match(['GET', 'POST'], '/note/all', [NoteController::class, 'getNotes'])->name('notes.getAll');
    Route::match(['GET', 'POST'], '/users/search', [NoteController::class, 'searchUsers'])->name('users.search');

    //---------------admin settings routes -----------------------
    Route::match(['GET', 'POST'], '/settings/main', [SettingController::class, 'index'])->name('settings.index');
    Route::match(['GET', 'POST'], '/settings/main/store', [SettingController::class, 'store'])->name('settings.store');

    //---------------Tasks Routes -----------------------
    Route::match(['GET', 'POST'], '/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::match(['GET', 'POST'], '/tasks/schedule-create', [TaskController::class, 'Schedulecreate'])->name('tasks.schedulecreate');
    Route::match(['GET', 'POST'], '/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::match(['GET', 'POST'], '/tasks/store', [TaskController::class, 'manage_task'])->name('tasks.store');
    Route::match(['GET', 'POST'], '/tasks/store-note/{taskId}', [TaskController::class, 'storeNote'])->name('tasks.store-note');
    Route::match(['GET', 'POST'], '/tasks/add-note/{taskId}', [TaskController::class, 'AddNote'])->name('tasks.add-note');
    Route::match(['GET', 'POST'], '/tasks/{task}', [TaskController::class, 'index'])->name('tasks.show');
    Route::match(['GET', 'POST'], '/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::match(['GET', 'POST'], '/tasks/{task}/delete', [TaskController::class, 'manage_task'])->name('tasks.delete');
    Route::match(['GET', 'POST'], '/tasks/{task}/update', [TaskController::class, 'manage_task'])->name('tasks.update');
    Route::match(['GET', 'POST'], '/load-task/{id}', [TaskController::class, 'FetchTask'])->name('loadtask.per');
    Route::match(['GET', 'POST'], '/tasks/update-status/{taskId}', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::match(['GET', 'POST'], '/tasks/update-status/{taskId}', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    // timeline route 
    Route::match(['GET', 'POST'], '/activities', [TimelineController::class, 'index'])->name('activities.index');

    // API routes
    Route::match(['GET', 'POST'], '/fetch/api', function () {
        return view('api.index');
    })->middleware('permission:fetch-leads leads')
    ->name('api.index');

    // PDF Generation Route

    // Dialer Route
    Route::match(['GET', 'POST'], '/dialer', [DashboardController::class, 'dialer_index'])->name('dialer.index');
    Route::view('/dialpad/iframe', 'dialer.iframe')->name('dialpad.iframe');

    // lead bulk assign route
    Route::match(['GET', 'POST'], '/bulk-assign-leads', [LeadAssignmentController::class, 'bulkAssign'])->name('leads.bulk-assign');
    
    // report Routes
    Route::match(['GET', 'POST'], '/reports', [DashboardController::class, 'reports'])->name('reports.index');
    
    Route::post('/rfq-quotes', [RfqQuoteController::class, 'store'])->name('rfq_quotes.store');
    
    Route::match(['GET', 'POST'], '/leads/{lead}/view-quotes', [RfqQuoteController::class, 'view'])->name('rfq_quotes.view');
    Route::put('/lead/rfq-quotes/{id}', [RfqQuoteController::class, 'update'])->name('rfq_quotes.update');
    Route::delete('/rfq-quotes/{id}', [RfqQuoteController::class, 'destroy'])->name('rfq_quotes.destroy');
    Route::post('/rfq-quotes/{id}/select', [RfqQuoteController::class, 'select'])->name('rfq_quotes.select');
    Route::match(['GET', 'POST'], '/lead/assign-supplier', [RfqQuoteController::class, 'assignSupplier'])->name('rfq_quotes.assign_supplier');
    
    // check blocked phone route 
    Route::post('/dialpad/check-blocked-number', [DialpadController::class, 'checkBlockedNumber'])->name('dialpad.check-blocked-number');

});


Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    // ======= Lead Sources =======
    Route::resource('lead-sources', LeadSourceController::class);
    Route::patch('lead-sources/{leadSource}/toggle-status', [LeadSourceController::class, 'toggleStatus'])->name('lead-sources.toggle-status');


    // ======= Lead Statuses =======
    Route::resource('lead-status', LeadStatusController::class)->names('lead-statuses');
    Route::patch('lead-status/{leadStatus}/toggle-status', [LeadStatusController::class, 'toggleStatus'])->name('lead-statuses.toggle-status');
    
    // ======= Lead Statuses =======
    // Route::resource('lead-statuses', LeadStatusController::class);
    // Route::patch('lead-statuses/{leadStatus}/toggle-status', [LeadStatusController::class, 'toggleStatus'])->name('lead-statuses.toggle-status');

    // ======= Priority Statuses =======
    Route::resource('priority-status', PriorityStatusController::class)->names('priority-statuses');
    Route::patch('priority-status/{priorityStatus}/toggle-status', [PriorityStatusController::class, 'toggleStatus'])->name('priority-statuses.toggle-status');
    // ======= Lead Steps =======
    Route::post('lead-steps/reorder', [LeadStepController::class, 'reorder'])->name('lead-steps.reorder');
    Route::resource('lead-steps', LeadStepController::class)->except(['show']);
});

require __DIR__.'/auth.php';