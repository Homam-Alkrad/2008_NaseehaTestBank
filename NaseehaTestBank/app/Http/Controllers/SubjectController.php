<?php
namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    // GET /subjects
    public function index()
    {
        $subjects = Subject::with('units')->get();
        return response()->json([
            'success' => true,
            'subjects' => $subjects
        ]);
    }

    // POST /subjects
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $subject = Subject::create($request->all());
        
        return response()->json([
            'success' => true,
            'subject' => $subject
        ], 201);
    }

    // GET /subjects/{id}
    public function show($id)
    {
        $subject = Subject::with('units.lessons')->find($id);
        
        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'subject' => $subject
        ]);
    }

    // GET /subjects/{id}/units
    public function getUnits($id)
    {
        $subject = Subject::find($id);
        
        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        }

        $units = $subject->units()->with('lessons')->get();
        
        return response()->json([
            'success' => true,
            'subject' => $subject,
            'units' => $units
        ]);
    }

    // PUT /subjects/{id}
    public function update(Request $request, $id)
    {
        $subject = Subject::find($id);
        
        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $subject->update($request->all());
        
        return response()->json([
            'success' => true,
            'subject' => $subject
        ]);
    }

    // DELETE /subjects/{id}
    public function destroy($id)
    {
        $subject = Subject::find($id);
        
        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        }

        // تحقق من وجود وحدات مرتبطة
        if ($subject->units()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject with existing units'
            ], 400);
        }

        $subject->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully'
        ]);
    }
}