<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    // عرض جميع الوحدات مع الدروس
    public function index()
    {
        $units = Unit::with(['lessons'])->get();
        
        return response()->json([
            'success' => true,
            'units' => $units,
            'count' => $units->count()
        ]);
    }

    // إنشاء وحدة جديدة
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:units,name',
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $unit = Unit::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        // تحميل العلاقات
        $unit->load('lessons');

        return response()->json([
            'success' => true,
            'unit' => $unit,
            'message' => 'تم إنشاء الوحدة بنجاح'
        ], 201);
    }

    // عرض وحدة محددة مع جميع التفاصيل
    public function show($id)
    {
        $unit = Unit::with(['lessons.mainTopics', 'lessons.questions'])->find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'الوحدة غير موجودة'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'unit' => $unit
        ]);
    }

    // تحديث وحدة
    public function update(Request $request, $id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'الوحدة غير موجودة'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:units,name,' . $id,
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $unit->update($request->only(['name', 'description']));
        $unit->load('lessons');

        return response()->json([
            'success' => true,
            'unit' => $unit,
            'message' => 'تم تحديث الوحدة بنجاح'
        ]);
    }

    // حذف وحدة
    public function destroy($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'الوحدة غير موجودة'
            ], 404);
        }

        // التحقق من وجود دروس مرتبطة
        if ($unit->lessons()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الوحدة لأنها تحتوي على دروس'
            ], 400);
        }

        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الوحدة بنجاح'
        ]);
    }

    // إحصائيات الوحدة
    public function getStats($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'الوحدة غير موجودة'
            ], 404);
        }

        $stats = [
            'lessons_count' => $unit->lessons()->count(),
            'questions_count' => $unit->questions()->count(),
            'average_rating' => $unit->questions()->avg('rating') ? round($unit->questions()->avg('rating'), 1) : 0,
            'latest_question' => $unit->questions()->latest()->first(),
            'top_rated_questions' => $unit->questions()->where('rating', '>=', 4)->count()
        ];

        return response()->json([
            'success' => true,
            'unit' => $unit,
            'stats' => $stats
        ]);
    }

    // البحث في الوحدات
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات البحث غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        $units = Unit::where('name', 'LIKE', '%' . $request->query . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->query . '%')
                    ->with('lessons')
                    ->get();

        return response()->json([
            'success' => true,
            'units' => $units,
            'count' => $units->count(),
            'search_query' => $request->query
        ]);
    }
}