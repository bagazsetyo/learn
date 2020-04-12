<?php

namespace App\Http\Controllers\Api;

use App\Course;
use App\UserCourse;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function generalList(Request $request) {
        try {
            $courses = Course::paginate(10);

            return response()->json($courses, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong when reading a course',
                'data'    => []
            ], 500);
        }
    }

    public function generalDetail(Request $request, $entity) {
        try {
            if (!$entity) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Entity ID is not defined',
                    'data'    => []
                ], 400);
            }

            $course = Course::find($entity);
            
            if ($course) {
                $course['modules'] = $course->modules;
            }
    
            if ($course['modules']) {
                foreach ($course['modules'] as $module) {
                    $module['lessons'] = $module->lessons;
                    $module['quizzes'] = $module->quizzes;
                }
            }

            if (Auth::check()) {
                $isAlreadyJoined = UserCourse::withTrashed()->where([
                    'user_id' => Auth::user()->id,
                    'course_id' => $entity
                ])->get();

                if ($isAlreadyJoined) {
                    $course['already_joined'] = true;
                } else {
                    $course['already_joined'] = false;
                }
            }

            if ($course) {
                return response()->json([
                    'error'   => false,
                    'message' => 'Successfully reading a course detail',
                    'data'    => $course,
                ]);
            } else {
                return response()->json([
                    'error'   => true,
                    'message' => 'Entity ID doesn\'t exists on database',
                    'data'    => []
                ], 400);
            }
    
        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong when reading a course',
                'data'    => []
            ], 500);
        }
    }

    public function roomDetail(Request $request, $entity) {
        try {
            if (!$entity) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Entity ID is not defined',
                    'data'    => []
                ], 400);
            }

            $isUserCanAccess = UserCourse::where([
                'user_id' => Auth::user()->id,
                'course_id' => $entity
            ])->count();

            if (!$isUserCanAccess) {
                return response()->json([
                    'error'   => true,
                    'message' => 'You can\'t access this course',
                    'data'    => []
                ], 403);
            }

            $course = Course::find($entity);
            
            if ($course) {
                $course['modules'] = $course->modules;
            }
    
            if ($course['modules']) {
                foreach ($course['modules'] as $module) {
                    $module['lessons'] = $module->lessons;
                    $module['quizzes'] = $module->quizzes;
                }
            }

            if ($course) {
                return response()->json([
                    'error'   => false,
                    'message' => 'Successfully reading a course detail',
                    'data'    => $course,
                ]);
            } else {
                return response()->json([
                    'error'   => true,
                    'message' => 'Entity ID doesn\'t exists on database',
                    'data'    => []
                ], 400);
            }
    
        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong when reading a course',
                'data'    => []
            ], 500);
        }
    }

    public function reportTotal(Request $request) {
        try {
            $courseData = Course::count();

            return response()->json([
                'error'   => false,
                'message' => 'Successfully creating a report for total',
                'data'    => $courseData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong when reporting total',
                'data'    => []
            ], 500);
        }
    }

    public function reportFollower(Request $request) {
        try {
            $courseData = Course::count();

            return response()->json([
                'error'   => false,
                'message' => 'Successfully creating a report for follower',
                'data'    => $courseData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong when reporting follower',
                'data'    => []
            ], 500);
        }
    }

    public function reportAccessed(Request $request) {
        try {
            $courseData = Course::count();

            return response()->json([
                'error'   => false,
                'message' => 'Successfully creating a report for accessed',
                'data'    => $courseData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong when reporting accessed',
                'data'    => []
            ], 500);
        }
    }

    public function read(Request $request) {
        $entity = $request->get('entity');
        $includes = $request->get('includes');
        $trashed = $request->get('trashed');

        try {
            if ($entity || $includes) {
                $courses = Course::find($entity ?? explode(',', $includes));
            }
    
            else if ($trashed) {
                $courses = Course::onlyTrashed()->paginate(30);
            }
    
            else {
                $courses = Course::paginate(30);
            }
    
            return response()->json($courses, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong when reading a courses',
                'data'    => []
            ], 500);
        }
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'content' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => true,
                'messages' => $validator->errors(),
                'data' => $request->all(),
            ], 400);
        }

        try {
            $course = Course::create([
                'title' => $request->get('title'),
                'description' => $request->get('description'),
                'content' => $request->get('content'),
            ]);
    
            return response()->json([
                'error'   => false,
                'message' => 'Successfully creating a course',
                'data'    => $course
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong when creating a course',
                'data'    => []
            ], 500);
        }
    }
    
    public function update(Request $request, $entity) {
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'description' => 'string',
            'content' => 'string',
            'status' => 'string',
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => true,
                'messages' => $validator->errors(),
                'data' => $request->all(),
            ], 400);
        }

        try {
            $isExisted = Course::find($entity)->count();

            if (!$isExisted) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Entity data is not found',
                    'data'    => []
                ], 400);
            }
    
            $courseTrashed = Course::onlyTrashed()->where('id', $entity)->count();

            if ($courseTrashed > 0) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Course data already deleted',
                    'data'    => [
                        'entity' => $entity
                    ]
                ], 400);
            }
    
            $courseData = Course::find($entity);

            if ($request->get('title')) {
                $courseData->title = $request->get('title');
            }
            if ($request->get('description')) {
                $courseData->description = $request->get('description');
            }
            if ($request->get('content')) {
                $courseData->content = $request->get('content');
            }
            if ($request->get('status')) {
                $courseData->status = $request->get('status');
            }

            $courseData->save();
    
            return response()->json([
                'error'   => false,
                'message' => 'Successfully updating a course',
                'data'    => $courseData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong when updating a course',
                'data'    => []
            ], 500);
        }
    }

    public function delete(Request $request, $entity) {
        try {
            $courseData = Course::find($entity);

            if (!$courseData) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Entity data is not found',
                    'data'    => []
                ], 400);
            }
            
            $courseData->delete();

            return response()->json([
                'error'   => false,
                'message' => 'Course data already deleted',
                'data'    => [
                    'entity' => $entity
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong when deleting a course',
                'data'    => []
            ], 500);
        }
    }
}
