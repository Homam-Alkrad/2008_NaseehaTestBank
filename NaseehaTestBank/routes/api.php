<?php
// routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\MainTopicController;
use App\Http\Controllers\SubTopicController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SubjectController;        // 👈 أضف هذا السطر
use App\Http\Controllers\QuestionRatingController;  // 👈 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// إضافة middleware للتأكد من أن جميع responses تكون JSON
Route::middleware(['api'])->group(function () {
    
    // ==================== مسارات الاختبار والتطوير ====================
    
    // مسار اختبار API - مع إعداد headers صحيح
    Route::get('/test', function () {
        return response()->json([
            'message' => 'API يعمل بنجاح!',
            'timestamp' => now()->toISOString(),
            'version' => '2.0',
            'status' => 'success',
            'routes_count' => count(Route::getRoutes()),
            'environment' => app()->environment(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION
        ], 200, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With'
        ]);
    });

    // مسار debug مفصل
    Route::get('/debug', function () {
        return response()->json([
            'message' => 'Debug endpoint',
            'system_info' => [
                'app_url' => config('app.url'),
                'app_env' => config('app.env'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'current_time' => now(),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale')
            ],
            'memory_info' => [
                'usage' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit')
            ],
            'request_info' => [
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Access-Control-Allow-Origin' => '*'
        ]);
    });

    // ==================== مسارات المصادقة ====================
    Route::match(['post', 'options'], '/login', [AuthController::class, 'login']);
    Route::match(['post', 'options'], '/register', [AuthController::class, 'register']);
    
    // مسار للمستخدم المصادق عليه
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    });

    // ==================== مسارات الـ API الرئيسية ====================
    Route::apiResource('users', UserController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('lessons', LessonController::class);
    Route::apiResource('main-topics', MainTopicController::class);
    Route::apiResource('sub-topics', SubTopicController::class);
    Route::apiResource('questions', QuestionController::class);
    Route::apiResource('subjects', SubjectController::class);
    // ==================== مسارات المستخدمين المتقدمة ====================
    
    // البحث والإحصائيات للمستخدمين
    Route::get('/users/search', [UserController::class, 'search']);
    Route::get('/users/{id}/stats', [UserController::class, 'getStats']);
    Route::get('/users/top-contributors/{limit?}', [UserController::class, 'getTopContributors']);
    Route::get('/subjects/{id}/units', [SubjectController::class, 'getUnits']);

    // Question rating routes
    Route::post('/questions/{questionId}/rate', [QuestionRatingController::class, 'rateQuestion']);
    Route::get('/questions/{questionId}/ratings', [QuestionRatingController::class, 'getQuestionRatings']);
    // ==================== مسارات الوحدات المتقدمة ====================
    
    // البحث والإحصائيات للوحدات
    Route::get('/units/search', [UnitController::class, 'search']);
    Route::get('/units/{id}/stats', [UnitController::class, 'getStats']);

    // ==================== مسارات الدروس المتقدمة ====================
    
    // البحث والإحصائيات للدروس
    Route::get('/lessons/search', [LessonController::class, 'search']);
    Route::get('/lessons/{id}/stats', [LessonController::class, 'getStats']);
    Route::get('/lessons/unit/{unitId}', [LessonController::class, 'getByUnit']);

    // ==================== مسارات المواضيع الرئيسية المتقدمة ====================
    
    // البحث والإحصائيات للمواضيع الرئيسية
    Route::get('/main-topics/search', [MainTopicController::class, 'search']);
    Route::get('/main-topics/{id}/stats', [MainTopicController::class, 'getStats']);
    Route::get('/main-topics/lesson/{lessonId}', [MainTopicController::class, 'getByLesson']);

    // ==================== مسارات المواضيع الفرعية المتقدمة ====================
    
    // البحث والإحصائيات للمواضيع الفرعية
    Route::get('/sub-topics/search', [SubTopicController::class, 'search']);
    Route::get('/sub-topics/{id}/stats', [SubTopicController::class, 'getStats']);
    Route::get('/sub-topics/main-topic/{mainTopicId}', [SubTopicController::class, 'getByMainTopic']);

    // ==================== مسارات الأسئلة المتقدمة ====================
    
    // البحث والتصفية
    Route::get('/questions/search', [QuestionController::class, 'search']);
    Route::get('/questions/recent/{limit?}', [QuestionController::class, 'getRecent']);
    
    // التصفية حسب التقييم
    Route::get('/questions/rating/{rating}', [QuestionController::class, 'getByRating']);
    Route::get('/questions/filter/high-rated', [QuestionController::class, 'getHighRated']);
    Route::get('/questions/filter/low-rated', [QuestionController::class, 'getLowRated']);
    
    // التصفية حسب الوحدة والدرس والمستخدم
    Route::get('/questions/unit/{unitId}', [QuestionController::class, 'getByUnit']);
    Route::get('/questions/lesson/{lessonId}', [QuestionController::class, 'getByLesson']);
    Route::get('/questions/user/{userId}', [QuestionController::class, 'getByUser']);
    
    // مسارات الأسئلة والإجابات المحسنة
    Route::get('/questions/with-answers', function() {
        $questions = \App\Models\Question::whereNotNull('answer')
            ->with(['unit', 'lesson', 'mainTopic', 'subTopic', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'count' => $questions->count(),
            'message' => 'الأسئلة التي تحتوي على إجابات'
        ]);
    });

    Route::get('/questions/without-answers', function() {
        $questions = \App\Models\Question::whereNull('answer')
            ->with(['unit', 'lesson', 'mainTopic', 'subTopic', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'count' => $questions->count(),
            'message' => 'الأسئلة بدون إجابات'
        ]);
    });
    
    // إحصائيات الأسئلة
    Route::get('/stats/ratings', [QuestionController::class, 'getRatingStats']);
    Route::get('/stats/units', [QuestionController::class, 'getStatsByUnit']);

    // ==================== مسارات العلاقات المحسنة ====================
    
    // الدروس حسب الوحدة (محسن)
    Route::get('/units/{unit_id}/lessons', function($unit_id) {
        $unit = \App\Models\Unit::find($unit_id);
        
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'الوحدة غير موجودة'
            ], 404);
        }

        $lessons = \App\Models\Lesson::where('unit_id', $unit_id)
            ->withCount(['questions', 'mainTopics'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'unit' => $unit,
            'lessons' => $lessons,
            'count' => $lessons->count()
        ], 200, ['Content-Type' => 'application/json']);
    });

    // المواضيع الرئيسية حسب الدرس (محسن)
    Route::get('/lessons/{lesson_id}/main-topics', function($lesson_id) {
        $lesson = \App\Models\Lesson::with('unit')->find($lesson_id);
        
        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'الدرس غير موجود'
            ], 404);
        }

        $mainTopics = \App\Models\MainTopic::where('lesson_id', $lesson_id)
            ->withCount(['subTopics', 'questions'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'lesson' => $lesson,
            'main_topics' => $mainTopics,
            'count' => $mainTopics->count()
        ], 200, ['Content-Type' => 'application/json']);
    });

    // المواضيع الفرعية حسب الموضوع الرئيسي (محسن)
    Route::get('/main-topics/{main_topic_id}/sub-topics', function($main_topic_id) {
        $mainTopic = \App\Models\MainTopic::with(['lesson.unit'])->find($main_topic_id);
        
        if (!$mainTopic) {
            return response()->json([
                'success' => false,
                'message' => 'الموضوع الرئيسي غير موجود'
            ], 404);
        }

        $subTopics = \App\Models\SubTopic::where('main_topic_id', $main_topic_id)
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'main_topic' => $mainTopic,
            'sub_topics' => $subTopics,
            'count' => $subTopics->count()
        ], 200, ['Content-Type' => 'application/json']);
    });

    // أسئلة المستخدم (محسّن بإحصائيات مفصلة)
    Route::get('/users/{user_id}/questions', function($user_id) {
        $user = \App\Models\User::find($user_id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود'
            ], 404);
        }

        $questions = \App\Models\Question::where('user_id', $user_id)
            ->with(['unit', 'lesson', 'mainTopic', 'subTopic'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_questions' => $questions->count(),
            'questions_with_answers' => $questions->whereNotNull('answer')->count(),
            'questions_without_answers' => $questions->whereNull('answer')->count(),
            'average_rating' => $questions->avg('rating') ? round($questions->avg('rating'), 1) : 0,
            'rating_distribution' => [
                '5_stars' => $questions->where('rating', 5)->count(),
                '4_stars' => $questions->where('rating', 4)->count(),
                '3_stars' => $questions->where('rating', 3)->count(),
                '2_stars' => $questions->where('rating', 2)->count(),
                '1_star' => $questions->where('rating', 1)->count(),
            ],
            'latest_question' => $questions->first(),
            'questions_by_unit' => $questions->groupBy('unit.name')->map->count()
        ];
            
        return response()->json([
            'success' => true,
            'user' => $user->makeHidden(['password']),
            'questions' => $questions,
            'stats' => $stats
        ], 200, ['Content-Type' => 'application/json']);
    });

    // ==================== مسارات إحصائيات شاملة ====================
    
    // إحصائيات عامة محسنة
    Route::get('/stats/general', function() {
        $stats = [
            'counts' => [
                'total_users' => \App\Models\User::count(),
                'admin_users' => \App\Models\User::where('role', 'Admin')->count(),
                'regular_users' => \App\Models\User::where('role', 'User')->count(),
                'total_units' => \App\Models\Unit::count(),
                'total_lessons' => \App\Models\Lesson::count(),
                'total_main_topics' => \App\Models\MainTopic::count(),
                'total_sub_topics' => \App\Models\SubTopic::count(),
                'total_questions' => \App\Models\Question::count(),
                'questions_with_answers' => \App\Models\Question::whereNotNull('answer')->count(),
                'questions_without_answers' => \App\Models\Question::whereNull('answer')->count(),
            ],
            'ratings' => [
                'average_rating' => \App\Models\Question::avg('rating') ? round(\App\Models\Question::avg('rating'), 1) : 0,
                'highest_rated_count' => \App\Models\Question::where('rating', 5)->count(),
                'lowest_rated_count' => \App\Models\Question::where('rating', 1)->count(),
            ],
            'activity' => [
                'questions_today' => \App\Models\Question::whereDate('created_at', today())->count(),
                'questions_this_week' => \App\Models\Question::where('created_at', '>=', now()->startOfWeek())->count(),
                'questions_this_month' => \App\Models\Question::where('created_at', '>=', now()->startOfMonth())->count(),
                'questions_this_year' => \App\Models\Question::where('created_at', '>=', now()->startOfYear())->count(),
            ],
            'top_performers' => [
                'most_active_user' => \App\Models\User::withCount('questions')->orderBy('questions_count', 'desc')->first(),
                'most_popular_unit' => \App\Models\Unit::withCount('questions')->orderBy('questions_count', 'desc')->first(),
                'most_popular_lesson' => \App\Models\Lesson::withCount('questions')->orderBy('questions_count', 'desc')->first(),
            ]
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
            'generated_at' => now()->toISOString()
        ], 200, ['Content-Type' => 'application/json']);
    });

    // إحصائيات الوحدات مع عدد الأسئلة (محسن)
    Route::get('/stats/units-detailed', function() {
        $units = \App\Models\Unit::withCount(['questions', 'lessons'])
            ->with(['lessons' => function($query) {
                $query->withCount(['questions', 'mainTopics']);
            }])
            ->get()
            ->map(function($unit) {
                return [
                    'unit' => $unit,
                    'stats' => [
                        'lessons_count' => $unit->lessons_count,
                        'questions_count' => $unit->questions_count,
                        'questions_with_answers' => $unit->questions()->whereNotNull('answer')->count(),
                        'questions_without_answers' => $unit->questions()->whereNull('answer')->count(),
                        'average_rating' => $unit->questions()->avg('rating') ? round($unit->questions()->avg('rating'), 1) : 0,
                        'completion_percentage' => $unit->questions_count > 0 ? round(($unit->questions()->where('rating', '>=', 4)->count() / $unit->questions_count) * 100, 1) : 0
                    ]
                ];
            });
            
        return response()->json([
            'success' => true,
            'units' => $units,
            'total_units' => $units->count()
        ], 200, ['Content-Type' => 'application/json']);
    });

    // أكثر المستخدمين نشاطاً (محسن)
    Route::get('/stats/top-users/{limit?}', function($limit = 10) {
        $topUsers = \App\Models\User::withCount('questions')
            ->with(['questions' => function($query) {
                $query->select('user_id', 'rating')->latest()->limit(5);
            }])
            ->orderBy('questions_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($user) {
                return [
                    'user' => $user->makeHidden(['password']),
                    'stats' => [
                        'questions_count' => $user->questions_count,
                        'questions_with_answers' => $user->questions()->whereNotNull('answer')->count(),
                        'questions_without_answers' => $user->questions()->whereNull('answer')->count(),
                        'average_rating' => $user->questions()->avg('rating') ? round($user->questions()->avg('rating'), 1) : 0,
                        'latest_questions' => $user->questions
                    ]
                ];
            });
            
        return response()->json([
            'success' => true,
            'top_users' => $topUsers,
            'message' => "أكثر {$limit} مستخدمين نشاطاً"
        ], 200, ['Content-Type' => 'application/json']);
    });

    // ==================== مسارات التقارير والتصدير ====================
    
    // تصدير الأسئلة مع تفاصيل كاملة
    Route::get('/admin/export/questions', function() {
        $questions = \App\Models\Question::with(['unit', 'lesson', 'mainTopic', 'subTopic', 'user'])
            ->get()
            ->map(function($question) {
                return [
                    'id' => $question->id,
                    'text' => $question->text,
                    'has_image' => !empty($question->image),
                    'has_answer' => !empty($question->answer),
                    'rating' => $question->rating,
                    'comment' => $question->comment,
                    'unit_name' => $question->unit->name ?? 'غير محدد',
                    'lesson_name' => $question->lesson->name ?? 'غير محدد',
                    'main_topic_name' => $question->mainTopic->name ?? 'غير محدد',
                    'sub_topic_name' => $question->subTopic->name ?? 'غير محدد',
                    'user_name' => $question->user->name ?? 'غير محدد',
                    'user_role' => $question->user->role ?? 'غير محدد',
                    'created_at' => $question->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $question->updated_at->format('Y-m-d H:i:s')
                ];
            });
            
        return response()->json([
            'success' => true,
            'questions' => $questions,
            'summary' => [
                'total_count' => $questions->count(),
                'questions_with_answers' => $questions->where('has_answer', true)->count(),
                'questions_without_answers' => $questions->where('has_answer', false)->count(),
                'questions_with_images' => $questions->where('has_image', true)->count(),
                'average_rating' => $questions->avg('rating') ? round($questions->avg('rating'), 1) : 0,
                'rating_distribution' => [
                    '5_stars' => $questions->where('rating', 5)->count(),
                    '4_stars' => $questions->where('rating', 4)->count(),
                    '3_stars' => $questions->where('rating', 3)->count(),
                    '2_stars' => $questions->where('rating', 2)->count(),
                    '1_star' => $questions->where('rating', 1)->count(),
                ]
            ],
            'exported_at' => now()->toISOString()
        ], 200, ['Content-Type' => 'application/json']);
    });

    // تصدير تقرير شامل
    Route::get('/admin/export/full-report', function() {
        $report = [
            'users' => \App\Models\User::withCount('questions')->get()->makeHidden(['password']),
            'units' => \App\Models\Unit::withCount(['lessons', 'questions'])->get(),
            'lessons' => \App\Models\Lesson::withCount(['questions', 'mainTopics'])->with('unit')->get(),
            'main_topics' => \App\Models\MainTopic::withCount(['questions', 'subTopics'])->with('lesson.unit')->get(),
            'sub_topics' => \App\Models\SubTopic::withCount('questions')->with('mainTopic.lesson.unit')->get(),
            'questions' => \App\Models\Question::with(['unit', 'lesson', 'user'])->get()
        ];
        
        return response()->json([
            'success' => true,
            'report' => $report,
            'metadata' => [
                'generated_at' => now()->toISOString(),
                'total_records' => array_sum(array_map('count', $report)),
                'report_version' => '2.0'
            ]
        ], 200, ['Content-Type' => 'application/json']);
    });

    // ==================== مسارات الصحة والمراقبة ====================
    
    // فحص صحة النظام محسن
    Route::get('/health', function() {
        try {
            // فحص قاعدة البيانات
            $dbCheck = \DB::connection()->getPdo() ? true : false;
            
            // فحص الجداول الرئيسية
            $tablesCheck = [
                'users' => \Schema::hasTable('users'),
                'units' => \Schema::hasTable('units'),
                'lessons' => \Schema::hasTable('lessons'),
                'main_topics' => \Schema::hasTable('main_topics'),
                'sub_topics' => \Schema::hasTable('sub_topics'),
                'questions' => \Schema::hasTable('questions'),
            ];

            // فحص البيانات الأساسية
            $dataCheck = [
                'users_exist' => \App\Models\User::count() > 0,
                'units_exist' => \App\Models\Unit::count() > 0,
                'questions_exist' => \App\Models\Question::count() > 0,
            ];

            // معلومات الأداء
            $performance = [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'memory_limit' => ini_get('memory_limit'),
                'execution_time' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms'
            ];
            
            return response()->json([
                'success' => true,
                'status' => 'healthy',
                'checks' => [
                    'database' => $dbCheck,
                    'tables' => $tablesCheck,
                    'data' => $dataCheck,
                    'performance' => $performance
                ],
                'environment' => [
                    'app_env' => config('app.env'),
                    'app_debug' => config('app.debug'),
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version()
                ],
                'timestamp' => now()->toISOString()
            ], 200, ['Content-Type' => 'application/json']);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode for detailed trace',
                'timestamp' => now()->toISOString()
            ], 500, ['Content-Type' => 'application/json']);
        }
    });

    // معلومات النظام
    Route::get('/system/info', function() {
        return response()->json([
            'success' => true,
            'system' => [
                'application' => [
                    'name' => config('app.name'),
                    'version' => '2.0',
                    'environment' => config('app.env'),
                    'debug' => config('app.debug'),
                    'url' => config('app.url'),
                    'timezone' => config('app.timezone'),
                    'locale' => config('app.locale')
                ],
                'server' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'upload_max_filesize' => ini_get('upload_max_filesize')
                ],
                'database' => [
                    'connection' => config('database.default'),
                    'driver' => config('database.connections.' . config('database.default') . '.driver')
                ]
            ],
            'generated_at' => now()->toISOString()
        ], 200, ['Content-Type' => 'application/json']);
    });

    // ==================== مسار catch-all للمسارات غير الموجودة ====================
    Route::fallback(function () {
        return response()->json([
            'success' => false,
            'message' => 'المسار المطلوب غير موجود',
            'error' => 'Route not found',
            'available_endpoints' => [
                'GET /api/test' => 'اختبار API',
                'GET /api/health' => 'فحص صحة النظام',
                'GET /api/system/info' => 'معلومات النظام',
                'API Resources' => [
                    'GET /api/users' => 'جميع المستخدمين',
                    'GET /api/units' => 'جميع الوحدات',
                    'GET /api/lessons' => 'جميع الدروس',
                    'GET /api/main-topics' => 'جميع المواضيع الرئيسية',
                    'GET /api/sub-topics' => 'جميع المواضيع الفرعية',
                    'GET /api/questions' => 'جميع الأسئلة',
                ],
                'Search & Filter' => [
                    'GET /api/questions/search' => 'البحث في الأسئلة',
                    'GET /api/questions/filter/high-rated' => 'الأسئلة عالية التقييم',
                    'GET /api/questions/rating/{rating}' => 'الأسئلة حسب التقييم',
                    'GET /api/questions/with-answers' => 'الأسئلة التي تحتوي على إجابات',
                    'GET /api/questions/without-answers' => 'الأسئلة بدون إجابات',
                ],
                'Statistics' => [
                    'GET /api/stats/general' => 'إحصائيات عامة',
                    'GET /api/stats/ratings' => 'إحصائيات التقييمات',
                    'GET /api/stats/units-detailed' => 'إحصائيات الوحدات المفصلة',
                ],
                'Reports' => [
                    'GET /api/admin/export/questions' => 'تصدير الأسئلة',
                    'GET /api/admin/export/full-report' => 'تقرير شامل',
                ]
            ],
            'documentation' => 'راجع التوثيق الكامل للAPI',
            'contact' => 'للدعم التقني تواصل مع فريق التطوير'
        ], 404, ['Content-Type' => 'application/json']);
    });
});