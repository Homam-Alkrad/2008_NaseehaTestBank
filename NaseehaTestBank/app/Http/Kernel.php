<?php
// 1. تحديث app/Http/Kernel.php
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     */
    protected $middleware = [
        // تأكد من ترتيب الـ middleware
        \App\Http\Middleware\Cors::class,  // إضافة هنا
        \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class, // مهم جداً
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // إزالة الـ middleware المخصص وخلي اللاروال يتعامل مع CORS
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\Cors::class,  // إضافة هنا أيضاً
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}

// 2. إنشاء config/cors.php إذا مش موجود
<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // في الإنتاج، ضع النطاقات المسموحة فقط
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];

// 3. تحديث routes/api.php - مبسط ونظيف
<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\MainTopicController;
use App\Http\Controllers\SubTopicController;
use App\Http\Controllers\QuestionController;

// مسار اختبار API
Route::get('/test', function () {
    return response()->json([
        'message' => 'API يعمل بنجاح!',
        'timestamp' => now()->toISOString(),
        'version' => '1.0',
        'status' => 'success'
    ]);
});

// مسارات المصادقة
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// مسارات الـ API الرئيسية
Route::apiResource('users', UserController::class);
Route::apiResource('units', UnitController::class);
Route::apiResource('lessons', LessonController::class);
Route::apiResource('main-topics', MainTopicController::class);
Route::apiResource('sub-topics', SubTopicController::class);
Route::apiResource('questions', QuestionController::class);

// مسارات إضافية
Route::get('/units/{unit_id}/lessons', function($unit_id) {
    return response()->json(
        \App\Models\Lesson::where('unit_id', $unit_id)->get()
    );
});

Route::get('/lessons/{lesson_id}/main-topics', function($lesson_id) {
    return response()->json(
        \App\Models\MainTopic::where('lesson_id', $lesson_id)->get()
    );
});

Route::get('/main-topics/{main_topic_id}/sub-topics', function($main_topic_id) {
    return response()->json(
        \App\Models\SubTopic::where('main_topic_id', $main_topic_id)->get()
    );
});

Route::get('/users/{user_id}/questions', function($user_id) {
    return response()->json(
        \App\Models\Question::where('user_id', $user_id)
            ->with(['unit', 'lesson', 'mainTopic', 'subTopic'])
            ->get()
    );
});

// مسار للمستخدم المصادق عليه
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

// 4. تحديث AuthController مبسط
<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // إجبار النوع JSON
        $request->headers->set('Accept', 'application/json');
        
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = User::where('username', $request->username)->first();
           
            if ($user && Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role
                    ],
                    'message' => 'تم تسجيل الدخول بنجاح'
                ]);
            }
           
            return response()->json([
                'success' => false,
                'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في الخادم',
                'error' => $e->getMessage()
            ], 500);
        }
    }
   
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'sometimes|string|in:Admin,Teacher,Student,User'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'User'
            ]);
           
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role
                ],
                'message' => 'تم إنشاء المستخدم بنجاح'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إنشاء المستخدم',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}