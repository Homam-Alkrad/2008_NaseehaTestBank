<?php
// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // تسجيل دخول
    public function login(Request $request)
    {
        // إضافة CORS headers
        $headers = [
            'Content-Type' => 'application/json; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept',
        ];

        // التعامل مع طلبات OPTIONS (preflight)
        if ($request->method() === 'OPTIONS') {
            return response()->json(['message' => 'OK'], 200, $headers);
        }

        // إجباري: تعيين Accept header لـ JSON
        $request->headers->set('Accept', 'application/json');

        try {
            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 400, $headers);
            }

            // البحث عن المستخدم
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
                ], 200, $headers);
            }
           
            return response()->json([
                'success' => false,
                'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة'
            ], 401, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في الخادم',
                'error' => $e->getMessage()
            ], 500, $headers);
        }
    }
   
    // إنشاء مستخدم جديد
    public function register(Request $request)
    {
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        ];

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'sometimes|string|in:Admin,User' // تحديد الأدوار إلى Admin و User فقط
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 400, $headers);
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'User' // افتراضياً User
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
            ], 201, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إنشاء المستخدم',
                'error' => $e->getMessage()
            ], 500, $headers);
        }
    }
}