<?php

namespace App\Http\Controllers;

use App\Models\MainTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MainTopicController extends Controller
{
    // عرض جميع المواضيع الرئيسية
    public function index()
    {
        $mainTopics = MainTopic::with(['lesson.unit', 'subTopics', 'questions'])->get();
        
        return response()->json([
            'success' => true,
            'main_topics' => $mainTopics,
            'count' => $mainTopics->count()
        ]);
    }

    // إنشاء موضوع رئيسي جديد
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'lesson_id' => 'required|exists:lessons,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $mainTopic = MainTopic::create([
            'name' => $request->name,
            'description' => $request->description,
            'lesson_id' => $request->lesson_id
        ]);

        // تحميل العلاقات
        $mainTopic->load(['lesson.unit', 'subTopics']);

        return response()->json([
            'success' => true,
            'main_topic' => $mainTopic,
            'message' => 'تم إنشاء الموضوع الرئيسي بنجاح'
        ], 201);
    }

    // عرض موضوع رئيسي محدد
    public function show($id)
    {
        $mainTopic = MainTopic::with(['lesson.unit', 'subTopics', 'questions.user'])->find($id);

        if (!$mainTopic) {
            return response()->json([
                'success' => false,
                'message' => 'الموضوع الرئيسي غير موجود'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'main_topic' => $mainTopic
        ]);
    }

    // تحديث موضوع رئيسي
    public function update(Request $request, $id)
    {
        $mainTopic = MainTopic::find($id);

        if (!$mainTopic) {
            return response()->json([
                'success' => false,
                'message' => 'الموضوع الرئيسي غير موجود'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'lesson_id' => 'sometimes|required|exists:lessons,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $mainTopic->update($request->only(['name', 'description', 'lesson_id']));
        $mainTopic->load(['lesson.unit', 'subTopics']);

        return response()->json([
            'success' => true,
            'main_topic' => $mainTopic,
            'message' => 'تم تحديث الموضوع الرئيسي بنجاح'
        ]);
    }

    // حذف موضوع رئيسي
    public function destroy($id)
    {
        $mainTopic = MainTopic::find($id);

        if (!$mainTopic) {
            return response()->json([
                'success' => false,
                'message' => 'الموضوع الرئيسي غير موجود'
            ], 404);
        }

        // التحقق من وجود مواضيع فرعية أو أسئلة مرتبطة
        if ($mainTopic->subTopics()->count() > 0 || $mainTopic->questions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الموضوع الرئيسي لأنه يحتوي على مواضيع فرعية أو أسئلة'
            ], 400);
        }

        $mainTopic->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الموضوع الرئيسي بنجاح'
        ]);
    }

    // الحصول على المواضيع الرئيسية لدرس معين
    public function getByLesson($lessonId)
    {
        $mainTopics = MainTopic::where('lesson_id', $lessonId)
            ->with(['subTopics', 'questions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'main_topics' => $mainTopics,
            'lesson_id' => $lessonId,
            'count' => $mainTopics->count()
        ]);
    }

    // إحصائيات الموضوع الرئيسي
    public function getStats($id)
    {
        $mainTopic = MainTopic::find($id);

        if (!$mainTopic) {
            return response()->json([
                'success' => false,
                'message' => 'الموضوع الرئيسي غير موجود'
            ], 404);
        }

        $stats = [
            'sub_topics_count' => $mainTopic->subTopics()->count(),
            'questions_count' => $mainTopic->questions()->count(),
            'average_rating' => $mainTopic->questions()->avg('rating') ? round($mainTopic->questions()->avg('rating'), 1) : 0,
            'latest_question' => $mainTopic->questions()->with('user')->latest()->first(),
        ];

        return response()->json([
            'success' => true,
            'main_topic' => $mainTopic,
            'stats' => $stats
        ]);
    }

    // البحث في المواضيع الرئيسية
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'lesson_id' => 'nullable|exists:lessons,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات البحث غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $query = MainTopic::where('name', 'LIKE', '%' . $request->query . '%')
                         ->orWhere('description', 'LIKE', '%' . $request->query . '%');

        // تصفية حسب الدرس إذا تم تحديده
        if ($request->lesson_id) {
            $query->where('lesson_id', $request->lesson_id);
        }

        $mainTopics = $query->with(['lesson.unit', 'subTopics'])->get();

        return response()->json([
            'success' => true,
            'main_topics' => $mainTopics,
            'count' => $mainTopics->count(),
            'search_query' => $request->query
        ]);
    }
}