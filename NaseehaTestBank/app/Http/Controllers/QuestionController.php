<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    // عرض جميع الأسئلة مع التقييمات والتعليقات
    public function index()
    {
        $questions = Question::with(['unit', 'lesson', 'mainTopic', 'subTopic', 'user'])->get();
        
        return response()->json([
            'success' => true,
            'questions' => $questions
        ]);
    }
   
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'text' => 'nullable|string',
        'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:5120',
        'answer' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:5120',
        'unit_id' => 'required|exists:units,id',
        'lesson_id' => 'required|exists:lessons,id',
        'main_topic_id' => 'nullable|exists:main_topics,id',
        'sub_topic_id' => 'nullable|exists:sub_topics,id',
        'user_id' => 'required|exists:users,id',
        'rating' => 'required|integer|min:1|max:5',
        'creator_comment' => 'nullable|string|max:1000'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'بيانات غير صحيحة',
            'errors' => $validator->errors()
        ], 400);
    }

    // Handle image upload
    $imagePath = null;
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $imagePath = $image->storeAs('questions', $imageName, 'public');
    }

    // Handle answer image upload
    $answerPath = null;
    if ($request->hasFile('answer')) {
        $answerImage = $request->file('answer');
        $answerName = time() . '_answer_' . uniqid() . '.' . $answerImage->getClientOriginalExtension();
        $answerPath = $answerImage->storeAs('answers', $answerName, 'public');
    }

    $question = Question::create([
        'text' => $request->text,
        'image' => $imagePath,
        'answer' => $answerPath,
        'unit_id' => $request->unit_id,
        'lesson_id' => $request->lesson_id,
        'main_topic_id' => $request->main_topic_id,
        'sub_topic_id' => $request->sub_topic_id,
        'user_id' => $request->user_id,
        'rating' => $request->rating,
        'creator_comment' => $request->creator_comment
    ]);

    $question->load(['unit', 'lesson', 'mainTopic', 'subTopic', 'user']);

    return response()->json([
        'success' => true,
        'question' => $question,
        'message' => 'تم إنشاء السؤال بنجاح'
    ], 201);
}
    // عرض سؤال محدد مع جميع التفاصيل
    public function show($id)
    {
        $question = Question::with(['unit', 'lesson', 'mainTopic', 'subTopic', 'user'])->find($id);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'السؤال غير موجود'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'question' => $question
        ]);
    }
   
    // تحديث سؤال مع التقييم والتعليق
    public function update(Request $request, $id)
    {
        $question = Question::find($id);
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'السؤال غير موجود'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'text' => 'sometimes|required|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:5120',
            'answer' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:5120',
            'unit_id' => 'sometimes|required|exists:units,id',
            'lesson_id' => 'sometimes|required|exists:lessons,id',
            'main_topic_id' => 'nullable|exists:main_topics,id',
            'sub_topic_id' => 'nullable|exists:sub_topics,id',
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'creator_comment' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        // Prepare update data
        $updateData = $request->only([
            'text', 'unit_id', 'lesson_id', 
            'main_topic_id', 'sub_topic_id', 'rating'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $updateData['image'] = $image->storeAs('questions', $imageName, 'public');
        }

        // Handle answer image upload  
        if ($request->hasFile('answer')) {
            $answerImage = $request->file('answer');
            $answerName = time() . '_answer_' . uniqid() . '.' . $answerImage->getClientOriginalExtension();
            $updateData['answer'] = $answerImage->storeAs('answers', $answerName, 'public');
        }

        // Handle comment field - prefer creator_comment if provided
        if ($request->has('creator_comment')) {
            $updateData['comment'] = $request->creator_comment;
        } elseif ($request->has('comment')) {
            $updateData['comment'] = $request->comment;
        }

        $question->update($updateData);

        $question->load(['unit', 'lesson', 'mainTopic', 'subTopic', 'user']);
       
        return response()->json([
            'success' => true,
            'question' => $question,
            'message' => 'تم تحديث السؤال بنجاح'
        ]);
    }
   
    // حذف سؤال
    public function destroy($id)
    {
        $question = Question::find($id);
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'السؤال غير موجود'
            ], 404);
        }

        $question->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف السؤال بنجاح'
        ]);
    }

    // الحصول على الأسئلة حسب التقييم
    public function getByRating($rating)
    {
        if ($rating < 1 || $rating > 5) {
            return response()->json([
                'success' => false,
                'message' => 'التقييم يجب أن يكون بين 1 و 5'
            ], 400);
        }

        $questions = Question::where('rating', $rating)
            ->with(['unit', 'lesson', 'user'])
            ->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'rating' => $rating,
            'count' => $questions->count()
        ]);
    }

    // الحصول على إحصائيات التقييمات
    public function getRatingStats()
    {
        $stats = [];
        $totalQuestions = Question::count();
        
        for ($i = 1; $i <= 5; $i++) {
            $count = Question::where('rating', $i)->count();
            $percentage = $totalQuestions > 0 ? round(($count / $totalQuestions) * 100, 1) : 0;
            
            $stats[$i] = [
                'count' => $count,
                'percentage' => $percentage
            ];
        }
        
        return response()->json([
            'success' => true,
            'rating_stats' => $stats,
            'total_questions' => $totalQuestions,
            'average_rating' => Question::avg('rating') ? round(Question::avg('rating'), 1) : 0
        ]);
    }

    // الحصول على الأسئلة عالية التقييم
    public function getHighRated()
    {
        $questions = Question::whereIn('rating', [4, 5])
            ->with(['unit', 'lesson', 'user'])
            ->orderBy('rating', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'message' => 'الأسئلة عالية التقييم (4-5 نجوم)'
        ]);
    }

    // الحصول على الأسئلة منخفضة التقييم
    public function getLowRated()
    {
        $questions = Question::whereIn('rating', [1, 2])
            ->with(['unit', 'lesson', 'user'])
            ->orderBy('rating', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'message' => 'الأسئلة منخفضة التقييم (1-2 نجوم)'
        ]);
    }

    // البحث في الأسئلة
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'unit_id' => 'nullable|exists:units,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'rating' => 'nullable|integer|min:1|max:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات البحث غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $query = Question::query();

        // البحث في نص السؤال أو التعليق
        $query->where(function ($q) use ($request) {
            $q->where('text', 'LIKE', '%' . $request->query . '%')
              ->orWhere('comment', 'LIKE', '%' . $request->query . '%');
        });

        // تصفية حسب الوحدة
        if ($request->unit_id) {
            $query->where('unit_id', $request->unit_id);
        }

        // تصفية حسب الدرس
        if ($request->lesson_id) {
            $query->where('lesson_id', $request->lesson_id);
        }

        // تصفية حسب التقييم
        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        $questions = $query->with(['unit', 'lesson', 'user'])->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'count' => $questions->count(),
            'search_query' => $request->query
        ]);
    }

    // الحصول على الأسئلة حسب الوحدة
    public function getByUnit($unitId)
    {
        $questions = Question::where('unit_id', $unitId)
            ->with(['unit', 'lesson', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'unit_id' => $unitId,
            'count' => $questions->count()
        ]);
    }

    // الحصول على الأسئلة حسب الدرس
    public function getByLesson($lessonId)
    {
        $questions = Question::where('lesson_id', $lessonId)
            ->with(['unit', 'lesson', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'lesson_id' => $lessonId,
            'count' => $questions->count()
        ]);
    }

    // الحصول على أحدث الأسئلة
    public function getRecent($limit = 10)
    {
        $questions = Question::with(['unit', 'lesson', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'message' => "أحدث {$limit} أسئلة"
        ]);
    }

    // الحصول على الأسئلة التي أنشأها مستخدم معين
    public function getByUser($userId)
    {
        $questions = Question::where('user_id', $userId)
            ->with(['unit', 'lesson'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'user_id' => $userId,
            'count' => $questions->count()
        ]);
    }

    // تجميع الأسئلة حسب التقييم والوحدة
    public function getStatsByUnit()
    {
        $stats = Question::selectRaw('unit_id, rating, COUNT(*) as count')
            ->with('unit:id,name')
            ->groupBy('unit_id', 'rating')
            ->orderBy('unit_id')
            ->orderBy('rating')
            ->get()
            ->groupBy('unit_id');

        return response()->json([
            'success' => true,
            'stats_by_unit' => $stats
        ]);
    }
}