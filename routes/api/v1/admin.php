<?php

use App\Http\Controllers\API\V1\Admin\AccountController;
use App\Http\Controllers\API\V1\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\API\V1\Admin\Auth\LoginController;
use App\Http\Controllers\API\V1\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\API\V1\Admin\Auth\VerificationController;
use App\Http\Controllers\API\V1\Admin\CategoryController;
use App\Http\Controllers\API\V1\Admin\CommentController;
use App\Http\Controllers\API\V1\Admin\DirectoryCompanyController;
use App\Http\Controllers\API\V1\Admin\DirectoryCompanyLocationController;
use App\Http\Controllers\API\V1\Admin\DirectoryCompanySocialController;
use App\Http\Controllers\API\V1\Admin\PermissionController;
use App\Http\Controllers\API\V1\Admin\PostController;
use App\Http\Controllers\API\V1\Admin\RolesAndPermissionsController;
use App\Http\Controllers\API\V1\Admin\SubCategoryController;
use App\Http\Controllers\API\V1\User\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Route::group(['prefix' => 'auth'], function () {
//     Route::post('/login', [LoginController::class, 'login'])->name('admin.login');
//     Route::post('/register', [RegisterController::class, 'register'])->name('user.register');
//     Route::put('/editUser/{user_id}', [RegisterController::class, 'editUser'])->name('user.editUser');
//     Route::delete('/user/{id}', [RegisterController::class, 'deleteUser'])->name('user-delete');
//     Route::put('/user_password/{user_id}', [RegisterController::class, 'editUserPassword'])->name('user.user_password');
//     Route::get('/fetch-user', [RegisterController::class, 'getUser']);
//     Route::get('/getUserPermissions', [RolesAndPermissionsController::class, 'getUserPermissions'])->name('getUserPermissions');
//     // Route::get('permissions', [RolesAndPermissionsController::class, 'getPermissions'])->name('getPermissions');

//     Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail'])
//         ->name('admin.password.sendResetLink');
//     Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('admin.password.update');
// });

// Route::prefix('roles')->group(function () {
//     Route::get('/', [RolesAndPermissionsController::class, 'getRoles'])->name('getRoles');
//     Route::get('permissions', [RolesAndPermissionsController::class, 'getPermissions'])->name('getPermissions');
//     Route::post('create', [RolesAndPermissionsController::class, 'createRole'])->name('createRole');
//     Route::post('update', [RolesAndPermissionsController::class, 'updateRole'])->name('updateRole');
//     Route::post('change', [RolesAndPermissionsController::class, 'changeRole'])->name('changeRole');

//     Route::get('{role}/permissions', [RolesAndPermissionsController::class, 'getRolePermissions'])->name('getRolePermissions');
//     Route::put('{role}/permissions', [RolesAndPermissionsController::class, 'updateRolePermissions'])->name('updateRolePermissions');
// });

// Route::apiResource('/permissions', PermissionController::class)->only('index', 'store');

Route::group(['middleware' => 'auth:admin'], function () {
    Route::prefix('auth')->group(function () {
        Route::get('/email/verify', [VerificationController::class, 'verify'])->name('admin.verification.verify');
        Route::get('/email/resend-verification', [VerificationController::class, 'resend']);
        Route::post('/logout', [LoginController::class, 'logout']);
    });
    Route::get('/email/resend', [VerificationController::class, 'resend']);
    // Route::get('/fetch-user', [RegisterController::class, 'getUser']);

    Route::get('/profile', [AccountController::class, 'index']);
    Route::post('/profile', [AccountController::class, 'update']);
    Route::post('/change-password', [AccountController::class, 'updatePassword']);

    // Route::prefix('roles')->group(function () {
    //     Route::get('roles', [RolesAndPermissionsController::class, 'getRoles'])->name('getRoles');
    //     Route::get('permissions', [RolesAndPermissionsController::class, 'getPermissions'])->name('getPermissions');
    //     Route::post('create-role', [RolesAndPermissionsController::class, 'createRole'])->name('createRole');
    //     Route::post('update-role', [RolesAndPermissionsController::class, 'updateRole'])->name('updateRole');
    //     Route::post('change-role', [RolesAndPermissionsController::class, 'changeRole'])->name('changeRole');
    // });


    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);

        Route::prefix('/{category}')->group(function () {
            Route::get('/', [CategoryController::class, 'show']);
            Route::put('/', [CategoryController::class, 'update']);
            Route::delete('/', [CategoryController::class, 'destroy']);
            Route::post('/restore', [CategoryController::class, 'restore'])->withTrashed();

            Route::prefix('sub-categories')->group(function () {
                Route::get('/', [SubCategoryController::class, 'index']);
                Route::post('/', [SubCategoryController::class, 'store']);

                Route::prefix('/{subCategory}')->group(function () {
                    Route::get('/', [SubCategoryController::class, 'show']);
                    Route::put('/', [SubCategoryController::class, 'update']);
                    Route::delete('/', [SubCategoryController::class, 'destroy']);
                    Route::post('/restore', [SubCategoryController::class, 'restore'])->withTrashed();
                });
            });
        });
    });

    Route::prefix('/posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store']);
        Route::prefix('/{post}')->group(function () {
            Route::get('/', [PostController::class, 'show']);
            Route::put('/', [PostController::class, 'update']);
            Route::delete('/', [PostController::class, 'destroy']);
            Route::post('/restore', [PostController::class, 'restore'])->withTrashed();
            Route::patch('/toggle-approval', [PostController::class, 'togglePostApprovalStatus']);
            Route::patch('/toggle-featured', [PostController::class, 'togglePostFeaturedStatus']);
            Route::patch('/toggle-published', [PostController::class, 'togglePostPublishedStatus']);
            Route::patch('/toggle-active', [PostController::class, 'togglePostActiveStatus']);
        });
    });

    Route::prefix('comments')->group(function () {
        Route::get('/', [CommentController::class, 'index']);
        Route::get('/{comment}', [CommentController::class, 'show']);
        Route::patch('/{comment}/toggle-approval', [CommentController::class, 'toggleCommentApproval']);
        Route::delete('/{comment}', [CommentController::class, 'destroy']);
        Route::post('/{comment}/restore', [CommentController::class, 'destroy'])->withTrashed();
    });

    Route::prefix('directory-companies')->group(function () {
        Route::get('/', [DirectoryCompanyController::class, 'index']);
        Route::post('/', [DirectoryCompanyController::class, 'store']);
        Route::prefix('/{directoryCompany}')->group(function () {
            Route::get('/', [DirectoryCompanyController::class, 'show']);
            Route::put('/', [DirectoryCompanyController::class, 'update']);
            Route::delete('/', [DirectoryCompanyController::class, 'destroy']);
            Route::post('/restore', [DirectoryCompanyController::class, 'restore'])->withTrashed();

            Route::prefix('/directory-company-locations')->group(function () {
                Route::get('/', [DirectoryCompanyLocationController::class, 'index']);
                Route::post('/', [DirectoryCompanyLocationController::class, 'store']);
                Route::prefix('/{directoryCompanyLocation}')->group(function () {
                    Route::get('/', [DirectoryCompanyLocationController::class, 'show']);
                    Route::put('/', [DirectoryCompanyLocationController::class, 'update']);
                    Route::delete('/', [DirectoryCompanyLocationController::class, 'destroy']);
                    Route::post('/restore', [DirectoryCompanyLocationController::class, 'restore'])->withTrashed();
                });
            });

            Route::prefix('/directory-company-socials')->group(function () {
                Route::get('/', [DirectoryCompanySocialController::class, 'index']);
                Route::post('/', [DirectoryCompanySocialController::class, 'store']);
                Route::prefix('/{directoryCompanySocial}')->group(function () {
                    Route::get('/', [DirectoryCompanySocialController::class, 'show']);
                    Route::put('/', [DirectoryCompanySocialController::class, 'update']);
                    Route::delete('/', [DirectoryCompanySocialController::class, 'destroy']);
                    Route::post('/restore', [DirectoryCompanySocialController::class, 'restore'])->withTrashed();
                });
            });
        });
    });
});
