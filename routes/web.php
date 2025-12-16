<?php
\URL::forceScheme('https');

use Illuminate\Support\Facades\Route;

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
Route::get('/ads-redirect', [App\Http\Controllers\MainController::class, 'redirectUrl'])->name('ads.redirect');
Route::post('/ads-redirect', [App\Http\Controllers\MainController::class, 'redirectUrlPost'])->name('ads.redirect.post');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/',[App\Http\Controllers\MainController::class,'index'])->name('main.index');

Route::group(['prefix' => 'traning'], function () {
    Route::get('/', [App\Http\Controllers\TraningController::class, 'index'])->name('traning.index');
});
Route::group(['prefix' => 'webview'], function () {
    Route::get('/activity', [App\Http\Controllers\WebviewController::class, 'activity'])->name('webview.activity');
});

Route::post('/admin/tasks/import', [App\Http\Controllers\Admin\TaskController::class, 'import'])->name('admin.tasks.import');
Route::post('/admin/material_stages/import', [App\Http\Controllers\Admin\MaterialStageController::class, 'import'])->name('admin.material_stages.import');

Route::group(['prefix' => 'admin'], function () {
    Route::any('/login', [App\Http\Controllers\Admin\AdminController::class, 'login'])->name('admin.admin.login');
    Route::any('/logout', [App\Http\Controllers\Admin\AdminController::class, 'logout'])->name('admin.admin.logout');

    Route::middleware([App\Http\Middleware\Admin::class])->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard.index');

        // 獎勵小工具
        Route::get('/reward-help', [App\Http\Controllers\Admin\ItemController::class, 'rewardHelp'])->name('admin.reward.help');

        Route::group(['prefix' => 'account'], function () {

            Route::get('/list', [App\Http\Controllers\Admin\AdminController::class, 'list'])->name('admin.account.list');
            Route::get('/edit/{id?}', [App\Http\Controllers\Admin\AdminController::class, 'edit'])->name('admin.account.edit');

            Route::group(['prefix' => 'role'], function () {
                Route::get('/list', [App\Http\Controllers\Admin\AdminRoleController::class, 'list'])->name('admin.account.role.list');
                Route::get('/edit/{id?}', [App\Http\Controllers\Admin\AdminRoleController::class, 'edit'])->name('admin.account.role.edit');
            });
        });

        Route::group(['prefix' => 'user'], function () {
            Route::get('/list', [App\Http\Controllers\Admin\UserController::class, 'list'])->name('admin.user.list');
            Route::get('/add/{id?}', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.user.add');
            Route::get('/edit/{id?}', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.user.edit');
        });

        Route::group(['prefix' => 'item'], function () {
            Route::get('/price', [App\Http\Controllers\Admin\ItemController::class, 'price'])->name('admin.item.price');
            Route::get('/price_upload', [App\Http\Controllers\Admin\ItemController::class, 'price_upload'])->name('admin.item.price_upload');
        });

        // 扭蛋機管理列表
        Route::group(['prefix' => 'gachas'], function () {
            Route::get('/list', [App\Http\Controllers\Admin\GachaController::class, 'index'])->name('admin.gacha.list');
            Route::get('/setting_to_ticket', [App\Http\Controllers\Admin\GachaController::class, 'setting_to_ticket'])->name('admin.gacha.setting_to_ticket');

            Route::get('/add', [App\Http\Controllers\Admin\GachaController::class, 'add'])->name('admin.gacha.add');
            Route::get('/edit/{id?}', [App\Http\Controllers\Admin\GachaController::class, 'edit'])->name('admin.gacha.edit');
        });

        // 扭蛋機道具管理
        Route::group(['prefix' => 'gacha-items'], function () {
            Route::get('/list', [App\Http\Controllers\Admin\GachaItemController::class, 'index'])->name('admin.gacha_items.list');
            Route::get('/add', [App\Http\Controllers\Admin\GachaItemController::class, 'add'])->name('admin.gacha_items.add');
            Route::get('/edit/{id?}', [App\Http\Controllers\Admin\GachaItemController::class, 'edit'])->name('admin.gacha_items.edit');

            // 匯入扭蛋資料
            Route::post('/import', [App\Http\Controllers\Admin\GachaItemController::class, 'import'])->name('admin.gacha_items.import');
        });

        // 任務管理列表
        Route::group(['prefix' => 'tasks'], function () {
            Route::get('/list', [App\Http\Controllers\Admin\TaskController::class, 'index'])->name('admin.tasks.list');
            Route::get('/add', [App\Http\Controllers\Admin\TaskController::class, 'add'])->name('admin.tasks.add');
            Route::get('/edit/{id?}', [App\Http\Controllers\Admin\TaskController::class, 'edit'])->name('admin.tasks.edit');

            // 匯入任務資料
            // Route::post('/import', [App\Http\Controllers\Admin\TaskController::class, 'import'])->name('admin.tasks.import');
        });

        // 遊戲信件後台管理
        Route::group(['prefix' => 'inbox'], function () {
            Route::get('/list', [App\Http\Controllers\Admin\InboxMessageController::class, 'index'])->name('admin.inbox.list');
            Route::get('/add', [App\Http\Controllers\Admin\InboxMessageController::class, 'add'])->name('admin.inbox.add');
            Route::get('/edit/{id?}', [App\Http\Controllers\Admin\InboxMessageController::class, 'edit'])->name('admin.inbox.edit');
        });

        // 系統設定
        Route::group(['prefix' => 'system'], function () {
            Route::get('/', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.system.index');
        });

        // 兌換碼管理
        Route::group(['prefix' => 'redeem'], function () {
            Route::get('/list', [App\Http\Controllers\Admin\RedeemController::class, 'index'])->name('admin.redeem.list');
        });

    });

});

// Route::group(['prefix' => 'store'], function () {
//     Route::any('/login', [App\Http\Controllers\Store\StoreController::class,'login'])->name('store.store.login');
//     Route::any('/logout', [App\Http\Controllers\Store\StoreController::class,'logout'])->name('store.store.logout');

//     Route::any('/employee_login/{store_id}', [App\Http\Controllers\Store\StoreController::class,'employee_login'])->name('store.store.employee_login');

//     Route::get('/setlocale/{locale?}', function (string $locale) {
//         if (! in_array($locale, ['zh_tw', 'zh_hk', 'en_gb', 'zh_cn'])) {
//             abort(400);
//         }
//         session(['store_locale' => $locale]);
//         return redirect()->back();
//     })->name('store.setLocale');

//     Route::middleware([App\Http\Middleware\Store::class])->group(function () {
//         Route::get('/',[App\Http\Controllers\Store\DashboardController::class,'index'])->name('store.dashboard.index');

//         Route::group(['prefix' => 'order'], function () {
//             Route::get('/add/{id?}',[App\Http\Controllers\Store\OrderController::class,'edit'])->name('store.order.add');
//             Route::get('/edit/{id?}',[App\Http\Controllers\Store\OrderController::class,'edit'])->name('store.order.edit');
//             Route::get('/list',[App\Http\Controllers\Store\OrderController::class,'list'])->name('store.order.list');
//             Route::get('/catch',[App\Http\Controllers\Store\OrderController::class,'catch'])->name('store.order.catch');
//             Route::get('/finish',[App\Http\Controllers\Store\OrderController::class,'finish'])->name('store.order.finish');
//             Route::get('/waitpay',[App\Http\Controllers\Store\OrderController::class,'waitpay'])->name('store.order.waitpay');
//             Route::get('/close',[App\Http\Controllers\Store\OrderController::class,'close'])->name('store.order.close');

//             Route::get('/check_update',[App\Http\Controllers\Store\OrderController::class,'check_update'])->name('store.order.check_update');
//         });

//         Route::group(['prefix' => 'employee'], function () {
//             Route::group(['prefix' => 'employee_role'], function () {
//                 Route::get('/list',[App\Http\Controllers\Store\StoreEmployeeRoleController::class,'list'])->name('store.employee.role.list');
//                 Route::get('/edit/{id?}',[App\Http\Controllers\Store\StoreEmployeeRoleController::class,'edit'])->name('store.employee.role.edit');
//             });

//             Route::get('/employee_list',[App\Http\Controllers\Store\StoreEmployeeController::class,'list'])->name('store.employee.list');
//             Route::get('/employee_add/{id?}',[App\Http\Controllers\Store\StoreEmployeeController::class,'edit'])->name('store.employee.add');
//             Route::get('/employee_edit/{id?}',[App\Http\Controllers\Store\StoreEmployeeController::class,'edit'])->name('store.employee.edit');

//             Route::get('/schedule/{id?}',[App\Http\Controllers\Store\StoreEmployeeController::class,'schedule'])->name('store.employee.schedule');
//             Route::get('/service/{id?}',[App\Http\Controllers\Store\StoreEmployeeController::class,'service'])->name('store.employee.service');
//             Route::get('/booking/{id?}',[App\Http\Controllers\Store\StoreEmployeeController::class,'booking'])->name('store.employee.booking');
//         });

//         Route::group(['prefix' => 'setting'], function () {
//             Route::get('/base',[App\Http\Controllers\Store\StoreSettingController::class,'base'])->name('store.setting.base');
//         });

//         Route::group(['prefix' => 'service'], function () {
//             Route::get('/category',[App\Http\Controllers\Store\StoreServiceController::class,'category'])->name('store.service.category');
//             Route::get('/list',[App\Http\Controllers\Store\StoreServiceController::class,'list'])->name('store.service.list');
//             Route::get('/add/{id?}',[App\Http\Controllers\Store\StoreServiceController::class,'edit'])->name('store.service.add');
//             Route::get('/edit/{id?}',[App\Http\Controllers\Store\StoreServiceController::class,'edit'])->name('store.service.edit');
//         });

//         Route::group(['prefix' => 'store_customer'], function () {
//             Route::get('/plist',[App\Http\Controllers\Store\StoreCustomerController::class,'plist'])->name('store.customer.plist');
//             Route::get('/clist',[App\Http\Controllers\Store\StoreCustomerController::class,'clist'])->name('store.customer.clist');

//             // Route::get('/add/{id?}',[App\Http\Controllers\Store\StoreCustomerController::class,'edit'])->name('store.customer.add');
//             Route::get('/pedit/{id?}',[App\Http\Controllers\Store\StoreCustomerController::class,'pedit'])->name('store.customer.pedit');
//             Route::get('/cedit/{id?}',[App\Http\Controllers\Store\StoreCustomerController::class,'cedit'])->name('store.customer.cedit');

//             Route::get('/check',[App\Http\Controllers\Store\StoreCustomerController::class,'check'])->name('store.customer.check');
//             Route::get('/update',[App\Http\Controllers\Store\StoreCustomerController::class,'update'])->name('store.customer.update');
//         });

//     });
// });

// Route::get('/', function () {
//     return view('welcome');
// });
