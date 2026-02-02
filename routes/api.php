<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/status', function () {
    return ['ok' => true];
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciais inválidas'], 401);
    }

    return ['token' => $user->createToken('api')->plainTextToken];
});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logout ok']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('cobrancas', \App\Http\Controllers\CobrancaController::class);
});
Route::post('/debug-body', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'content_type' => $request->header('content-type'),
        'raw' => $request->getContent(),
        'all' => $request->all(),
        'json' => $request->json()->all(),
    ]);
});

