<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    // عرض جميع الدروس
    public function index()
    {
        $lessons = Lesson::with(['unit', 'mainTopics', 'questions'])->get();
        
        return response()->json([
            'success' => true,
            'lessons' => $lessons,
            'count' => $lessons->count()
        ]);
    }

    // إنشاء درس جديد
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit_id' => 'required|exists:units,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $lesson = Lesson::create([
            'name' => $request->name,
            'description' => $request->description,
            'unit_id' => $request->unit_id
        ]);

        // تحميل العلاقات
        $lesson->load(['unit', 'mainTopics']);

        return response()->json([
            'success' => true,
            'lesson' => $lesson,
            'message' => 'تم إنشاء الدرس بنجاح'
        ], 201);
    }

    // عرض درس محدد
    public function show($id)
    {
        $lesson = Lesson::with(['unit', 'mainTopics.subTopics', 'questions.user'])->find($id);

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'الدرس غير موجود'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'lesson' => $lesson
        ]);
    }

    // تحديث درس
    public function update(Request $request, $id)
    {
        $lesson = Lesson::find($id);

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'الدرس غير موجود'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit_id' => 'sometimes|required|exists:units,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $lesson->update($request->only(['name', 'description', 'unit_id']));
        $lesson->load(['unit', 'mainTopics']);

        return response()->json([
            'success' => true,
            'lesson' => $lesson,
            'message' => 'تم تحديث الدرس بنجاح'
        ]);
    }

    // حذف درس
    public function destroy($id)
    {
        $lesson = Lesson::find($id);

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'الدرس غير موجود'
            ], 404);
        }

        // التحقق من وجود أسئلة مرتبطة
        if ($lesson->questions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الدرس لأنه يحتوي على أسئلة'
            ], 400);
        }

        $lesson->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الدرس بنجاح'
        ]);
    }

    // الحصول على دروس وحدة معينة
    public function getByUnit($unitId)
    {
        $lessons = Lesson::where('unit_id', $unitId)
            ->with(['mainTopics', 'questions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'lessons' => $lessons,
            'unit_id' => $unitId,
            'count' => $lessons->count()
        ]);
    }

    // إحصائيات الدرس
    public function getStats($id)
    {
        $lesson = Lesson::find($id);

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'الدرس غير موجود'
            ], 404);
        }

        $stats = [
            'main_topics_count' => $lesson->mainTopics()->count(),
            'questions_count' => $lesson->questions()->count(),
            'average_rating' => $lesson->questions()->avg('rating') ? round($lesson->questions()->avg('rating'), 1) : 0,
            'latest_question' => $lesson->questions()->with('user')->latest()->first(),
            'rating_distribution' => [
                '5_stars' => $lesson->questions()->where('rating', 5)->count(),
                '4_stars' => $lesson->questions()->where('rating', 4)->count(),
                '3_stars' => $lesson->questions()->where('rating', 3)->count(),
                '2_stars' => $lesson->questions()->where('rating', 2)->count(),
                '1_star' => $lesson->questions()->where('rating', 1)->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'lesson' => $lesson,
            'stats' => $stats
        ]);
    }

    // البحث في الدروس
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'unit_id' => 'nullable|exists:units,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات البحث غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $query = Lesson::where('name', 'LIKE', '%' . $request->query . '%')
                      ->orWhere('description', 'LIKE', '%' . $request->query . '%');

        // تصفية حسب الوحدة إذا تم تحديدها
        if ($request->unit_id) {
            $query->where('unit_id', $request->unit_id);
        }

        $lessons = $query->with(['unit', 'mainTopics'])->get();

        return response()->json([
            'success' => true,
            'lessons' => $lessons,
            'count' => $lessons->count(),
            'search_query' => $request->query
        ]);
    }
}