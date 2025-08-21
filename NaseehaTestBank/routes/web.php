<?php
// routes/web.php
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::middleware([\App\Http\Middleware\StaticFilesCors::class])->group(function () {
    // هذا للملفات الثابتة
});
// إضافة مسار لصفحة تسجيل الدخول إذا كنت تريدها
Route::get('/login', function () {
    return view('login'); // إذا كان لديك blade template
});

// مسار لاختبار أن الـ web routes تعمل
Route::get('/web-test', function () {
    return response()->json([
        'message' => 'Web routes تعمل!',
        'timestamp' => now()
    ]);
});