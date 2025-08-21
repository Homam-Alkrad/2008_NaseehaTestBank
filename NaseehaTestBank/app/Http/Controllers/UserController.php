<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // عرض جميع المستخدمين
    public function index()
    {
        $users = User::withCount(['questions'])->get();
        
        return response()->json([
            'success' => true,
            'users' => $users,
            'count' => $users->count()
        ]);
    }

    // إنشاء مستخدم جديد
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:Admin,User'
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
            'role' => $request->role
        ]);

        // إخفاء كلمة المرور في الاستجابة
        $user->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'تم إنشاء المستخدم بنجاح'
        ], 201);
    }

    // عرض مستخدم محدد
    public function show($id)
    {
        $user = User::with(['questions.unit', 'questions.lesson'])
                    ->withCount(['questions'])
                    ->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود'
            ], 404);
        }

        $user->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    // تحديث مستخدم
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $id,
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|required|in:Admin,User'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $updateData = $request->only(['name', 'username', 'email', 'role']);
        
        // تشفير كلمة المرور إذا تم تحديثها
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);
        $user->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'تم تحديث المستخدم بنجاح'
        ]);
    }

    // حذف مستخدم
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود'
            ], 404);
        }

        // التحقق من وجود أسئلة مرتبطة
        if ($user->questions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المستخدم لأنه منشئ أسئلة في النظام'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المستخدم بنجاح'
        ]);
    }

    // إحصائيات المستخدم
    public function getStats($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود'
            ], 404);
        }

        $stats = [
            'questions_count' => $user->questions()->count(),
            'average_rating' => $user->questions()->avg('rating') ? round($user->questions()->avg('rating'), 1) : 0,
            'latest_question' => $user->questions()->latest()->first(),
            'questions_by_rating' => [
                '5_stars' => $user->questions()->where('rating', 5)->count(),
                '4_stars' => $user->questions()->where('rating', 4)->count(),
                '3_stars' => $user->questions()->where('rating', 3)->count(),
                '2_stars' => $user->questions()->where('rating', 2)->count(),
                '1_star' => $user->questions()->where('rating', 1)->count(),
            ],
            'questions_by_unit' => $user->questions()
                                       ->selectRaw('unit_id, COUNT(*) as count')
                                       ->with('unit:id,name')
                                       ->groupBy('unit_id')
                                       ->get()
        ];

        $user->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'user' => $user,
            'stats' => $stats
        ]);
    }

    // البحث في المستخدمين
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'role' => 'nullable|in:Admin,User'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات البحث غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $query = User::where('name', 'LIKE', '%' . $request->query . '%')
                    ->orWhere('username', 'LIKE', '%' . $request->query . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->query . '%');

        // تصفية حسب الدور إذا تم تحديده
        if ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->withCount(['questions'])->get();
        
        // إخفاء كلمات المرور
        $users->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'users' => $users,
            'count' => $users->count(),
            'search_query' => $request->query
        ]);
    }

    // الحصول على المستخدمين الأكثر نشاطاً
    public function getTopContributors($limit = 10)
    {
        $users = User::withCount(['questions'])
                    ->orderBy('questions_count', 'desc')
                    ->limit($limit)
                    ->get();

        $users->makeHidden(['password']);

        return response()->json([
            'success' => true,
            'users' => $users,
            'message' => "أكثر {$limit} مستخدمين نشاطاً"
        ]);
    }
}