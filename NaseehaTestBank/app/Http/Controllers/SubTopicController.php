<?php

namespace App\Http\Controllers;

use App\Models\SubTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubTopicController extends Controller
{
    // عرض جميع المواضيع الفرعية
    public function index()
    {
        $subTopics = SubTopic::with(['mainTopic.lesson.unit', 'questions'])->get();
        
        return response()->json([
            'success' => true,
            'sub_topics' => $subTopics,
            'count' => $subTopics->count()
        ]);
    }

    // إنشاء موضوع فرعي جديد
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'main_topic_id' => 'required|exists:main_topics,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $subTopic = SubTopic::create([
            'name' => $request->name,
            'description' => $request->description,
            'main_topic_id' => $request->main_topic_id
        ]);

        // تحميل العلاقات
        $subTopic->load(['mainTopic.lesson.unit']);

        return response()->json([
            'success' => true,
            'sub_topic' => $subTopic,
            'message' => 'تم إنشاء الموضوع الفرعي بنجاح'
        ], 201);
    }

    // عرض موضوع فرعي محدد
    public function show($id)
    {
        $subTopic = SubTopic::with(['mainTopic.lesson.unit', 'questions.user'])->find($id);

        if (!$subTopic) {
            return response()->json([
                'success' => false,
                'message' => 'الموضوع الفرعي غير موجود'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'sub_topic' => $subTopic
        ]);
    }

    // تحديث موضوع فرعي
    public function update(Request $request, $id)
    {
        $subTopic = SubTopic::find($id);

        if (!$subTopic) {
            return response()->json([
                'success' => false,
                'message' => 'الموضوع الفرعي غير موجود'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'main_topic_id' => 'sometimes|required|exists:main_topics,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $subTopic->update($request->only(['name', 'description', 'main_topic_id']));
        $subTopic->load(['mainTopic.lesson.unit']);

        return response()->json([
            'success' => true,
            'sub_topic' => $subTopic,
            'message' => 'تم تحديث الموضوع الفرعي بنجاح'
        ]);
    }

    // حذف موضوع فرعي
    public function destroy($id)
    {
        $subTopic = SubTopic::find($id);

        if (!$subTopic) {
            return response()->json([
                'success' => false,
                'message' => 'الموضوع الفرعي غير موجود'
            ], 404);
        }

        // التحقق من وجود أسئلة مرتبطة
        if ($subTopic->questions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الموضوع الفرعي لأنه يحتوي على أسئلة'
            ], 400);
        }

        $subTopic->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الموضوع الفرعي بنجاح'
        ]);
    }

    // الحصول على المواضيع الفرعية لموضوع رئيسي معين
    public function getByMainTopic($mainTopicId)
    {
        $subTopics = SubTopic::where('main_topic_id', $mainTopicId)
            ->with(['questions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'sub_topics' => $subTopics,
            'main_topic_id' => $mainTopicId,
            'count' => $subTopics->count()
        ]);
    }

    // إحصائيات الموضوع الفرعي
    public function getStats($id)
    {
        $subTopic = SubTopic::find($id);

        if (!$subTopic) {
            return response()->json([
                'success' => false,
                'message' => 'الموضوع الفرعي غير موجود'
            ], 404);
        }

        $stats = [
            'questions_count' => $subTopic->questions()->count(),
            'average_rating' => $subTopic->questions()->avg('rating') ? round($subTopic->questions()->avg('rating'), 1) : 0,
            'latest_question' => $subTopic->questions()->with('user')->latest()->first(),
        ];

        return response()->json([
            'success' => true,
            'sub_topic' => $subTopic,
            'stats' => $stats
        ]);
    }

    // البحث في المواضيع الفرعية
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'main_topic_id' => 'nullable|exists:main_topics,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات البحث غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $query = SubTopic::where('name', 'LIKE', '%' . $request->query . '%')
                         ->orWhere('description', 'LIKE', '%' . $request->query . '%');

        // تصفية حسب الموضوع الرئيسي إذا تم تحديده
        if ($request->main_topic_id) {
            $query->where('main_topic_id', $request->main_topic_id);
        }

        $subTopics = $query->with(['mainTopic.lesson.unit'])->get();

        return response()->json([
            'success' => true,
            'sub_topics' => $subTopics,
            'count' => $subTopics->count(),
            'search_query' => $request->query
        ]);
    }
}