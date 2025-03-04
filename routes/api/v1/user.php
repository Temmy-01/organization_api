<?php

use App\Http\Controllers\API\V1\Admin\RolesAndPermissionsController;
use App\Http\Controllers\API\V1\User\RepositoryController;
use App\Http\Controllers\API\V1\User\Auth\ForgotPasswordController;
use App\Http\Controllers\API\V1\User\Auth\LoginController;
use App\Http\Controllers\API\V1\User\Auth\RegisterController;
use App\Http\Controllers\API\V1\User\Auth\ResetPasswordController;
use App\Http\Controllers\API\V1\User\Auth\VerificationController;
use App\Http\Controllers\API\V1\User\OrganisationRepositoryController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'register'])->name('user.register');
    Route::post('/login', [LoginController::class, 'login'])->name('user.login');
    Route::get('/get_user_type', [RegisterController::class, 'getUserType'])->name('user.get_user_type');
    Route::put('/user_password/{user_id}', [RegisterController::class, 'editUserPassword'])->name('user.user_password');
    Route::delete('/user/{id}/{sub_account_email}', [RegisterController::class, 'deleteUser'])->name('user-delete');



    Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('user.password.sendResetLink');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('user.password.update');
});


Route::group(['middleware' => ['auth:user', 'log_activity']], function () {
    Route::prefix('auth')->group(function () {
        Route::get('/email/verify', [VerificationController::class, 'verify'])->name('user.verification.verify');
        Route::get('/email/resend-verification', [VerificationController::class, 'resend']);
        Route::post('/logout', [LoginController::class, 'logout'])->name('user.logout');
    });

    Route::prefix('roles')->group(function () {
        Route::get('/', [RolesAndPermissionsController::class, 'getRoles'])->name('getRoles');
        Route::get('permissions', [RolesAndPermissionsController::class, 'getPermissions'])->name('getPermissions');
        Route::post('create', [RolesAndPermissionsController::class, 'createRole'])->name('createRole');
        Route::post('update', [RolesAndPermissionsController::class, 'updateRole'])->name('updateRole');
        Route::post('change', [RolesAndPermissionsController::class, 'changeRole'])->name('changeRole');
        Route::get('/getUserPermissions', [RolesAndPermissionsController::class, 'getUserPermissions'])->name('getUserPermissions');

        Route::get('{role}/permissions', [RolesAndPermissionsController::class, 'getRolePermissions'])->name('getRolePermissions');
        Route::put('{role}/permissions', [RolesAndPermissionsController::class, 'updateRolePermissions'])->name('updateRolePermissions');
    });

    Route::prefix('repository')->group(function () {
        Route::post('add_repository', [RepositoryController::class, 'createRepository'])->name('reposistory.add_repository');
        Route::get('/fetch', [RepositoryController::class, 'fetchRepositories'])->name('reposistory.fetch');
        Route::put('/update/{id}', [RepositoryController::class, 'updateRepository'])->name('reposistory.update');
        Route::delete('/delete/{id}', [RepositoryController::class, 'deleteRepository'])->name('reposistory.delete');
    });

    Route::prefix('org_repository')->group(function () {
        Route::post('add_repository', [OrganisationRepositoryController::class, 'createOrgRepository'])->name('org_repository.add_repository');
        Route::get('/fetch', [OrganisationRepositoryController::class, 'fetchOrganisationRepo'])->name('org_repository.fetch');
        Route::put('/update/{id}', [OrganisationRepositoryController::class, 'updateOrgRepository'])->name('org_repository.update');
        Route::delete('/delete/{id}', [OrganisationRepositoryController::class, 'deleteRepository'])->name('org_repository.delete');
    });
    
    Route::get('/fetch-user', [RegisterController::class, 'getUser']);
    Route::get('/fetch-analytic', [RegisterController::class, 'getAnalytic']);
    Route::put('/editUser/{user_id}', [RegisterController::class, 'editUser'])->name('user.editUser');

  

    Route::get('/profile', [RepositoryController::class, 'index']);
    Route::post('/profile', [RepositoryController::class, 'update']);
    Route::post('/change-password', [RepositoryController::class, 'updatePassword']);

});
