<?php
namespace App\Http\Controllers;

use App\Models\UserQuestionRating;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionRatingController extends Controller
{
    // إضافة تقييم
    public function rateQuestion(Request $request, $questionId)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $question = Question::find($questionId);
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found'
            ], 404);
        }

        $rating = UserQuestionRating::updateOrCreate(
            ['user_id' => $request->user_id, 'question_id' => $questionId],
            ['rating' => $request->rating]
        );

        return response()->json([
            'success' => true,
            'rating' => $rating,
            'average_rating' => $question->average_rating,
            'rating_count' => $question->rating_count
        ]);
    }

    // جلب تقييمات السؤال
    public function getQuestionRatings($questionId)
    {
        $question = Question::with('userRatings.user')->find($questionId);
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'question_id' => $questionId,
            'average_rating' => $question->average_rating,
            'rating_count' => $question->rating_count,
            'ratings' => $question->userRatings
        ]);
    }
}