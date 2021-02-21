<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\User;
use App\Block;
use App\Course;
use App\Department;
use App\Assignment;
use App\AssignmentSubmission;
use App\Material;
use App\ScheduleRequest;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/course/{id}', function ($id) {
    return Course::find($id);
});

Route::get('/course/block/{id}', function ($id) {
    return Course::find($id)->blocks;
});

Route::get('/all-courses', function () {
    return Course::with(['department'])->orderBy('title')->get();
});

Route::get('/all-users', function () {
    return User::all();
});

Route::get('/all-departments', function () {
    return Department::all();
});

Route::post('/course-block', function (Request $request) {
    $block = new Block;
    $block->title = $request->title;
    $block->course_id = $request->course_id;
    $block->save();
    return $block;
});

Route::post('/edit-course-block', function (Request $request) {
    $block = Block::find($request->block_id);
    $block->title = $request->title;
    $block->save();
    return $block;
});

Route::post('/delete-course-block', function (Request $request) {
    $block = Block::find($request->block_id);
    $block->delete();
});

Route::post('/register-course', function (Request $request) {
    if (count(ScheduleRequest::where('student_id', $request->user_id)->get())) {
        $schedule_request = ScheduleRequest::where('student_id', $request->user_id)->first();
    } else {
        $schedule_request = new ScheduleRequest();
        $schedule_request->student_id = $request->user_id;
        $schedule_request->save();
    }
    $schedule_request->courses()->attach($request->course_id);
    return User::find($request->user_id);
});

Route::post('/approve-courses', function (Request $request) {
    $student = User::find($request->student_id);
    $student->registration_status = 'approved';
    $courses = $request->courses;
    foreach ($courses as $course){
        $student->courses()->attach($course);
    }
    $request = ScheduleRequest::find($request->request_id);
    $request->delete();
    $student->save();
    return User::find($request->student_id);
});

Route::post('/add-user', function (Request $request) {
    $user = new User();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->role = $request->role;
    $user->password = bcrypt($request->password);
    $user->department_id = $request->department;
    $user->save();
    $data = [
        'name' => $request->name,
        'email' => $request->email,
        'password' => $request->password
    ];
    \Illuminate\Support\Facades\Mail::to($request->email)->queue(new \App\Mail\RegisterMail($data));
    return User::find($user->id);
});

Route::post('/add-course', function (Request $request) {
    $course = new Course();
    $course->title = $request->title;
    $course->abbreviation = $request->abbreviation;
    $course->department_id = $request->department;
    $course->capacity = $request->capacity;
    $course->save();
    return Course::find($course->id);
});

Route::get('/assignment/{id}', function ($id) {
    return Assignment::find($id);
});

Route::post('/assignment-submit', function (Request $request) {
    $submission = new AssignmentSubmission();
    $file = $request->file('file');
    $fileName = uniqid() . '.' . $file->extension();
    $file->storePubliclyAs('public', $fileName);
    $submission->title = $request->title;
    $submission->text = $request->text;
    $submission->file_submission = $fileName;
    $submission->submitted_at = now();
    $submission->assignment_id = $request->assignment_id;
    $submission->user_id = $request->user_id;
    $submission->save();
    return $submission;
});

Route::get('/assignment-submissions/{id}', function ($id) {
    return AssignmentSubmission::where('assignment_id', $id)->get();
});

Route::post('/edit-course', function (Request $request) {
    $course = Course::find($request->course_id);
    $course->title = $request->title;
    $course->abbreviation = $request->abbreviation;
    $course->department_id = $request->department;
    $course->capacity = $request->capacity;
    $course->save();
    return $course;
});

Route::post('/delete-course', function (Request $request) {
    $course = Course::find($request->course_id);
    $course->delete();
});

Route::post('/edit-user', function (Request $request) {
    $user = User::find($request->user_id);
    $user->name = $request->name;
    $user->email = $request->email;
    $user->role = $request->role;
    $user->department_id = $request->department;
    $user->save();
    return $user;
});

Route::post('/delete-user', function (Request $request) {
    $user = User::find($request->user_id);
    $user->delete();
});

Route::post('/add-assignment', function (Request $request) {
    $assignment = new Assignment();
    $file = $request->file('file');
    $fileName = uniqid() . '.' . $file->extension();
    $file->storePubliclyAs('public', $fileName);
    $assignment->title = $request->title;
    $assignment->description = $request->description;
    $assignment->file = $fileName;
    $assignment->block_id = $request->block_id;
    $assignment->save();
    return $assignment;
});

Route::post('/add-material', function (Request $request) {
    $material = new Material();
    $file = $request->file('file');
    $fileName = uniqid() . '.' . $file->extension();
    $file->storePubliclyAs('public', $fileName);
    $material->title = $request->title;
    $material->file = $fileName;
    $material->block_id = $request->block_id;
    $material->save();
    return $material;
});

Route::post('/update-course-thumbnail', function (Request $request) {
    $course = Course::find($request->course_id);
    $file = $request->file('file');
    $fileName = uniqid() . '.' . $file->extension();
    $file->storePubliclyAs('public', $fileName);
    $course->thumbnail = $fileName;
    $course->save();
    return $course;
});

Route::get('/adviser-students/{id}', function ($id) {
    return User::where('adviser_id', $id)->get();
});

Route::post('/send-request-adviser', function (Request $request) {
    $user = User::find($request->student_id);
    $user->registration_status = 'pending';
    $user->save();
    return User::find($request->student_id);
});

Route::post('/reject-courses', function (Request $request) {
    $student = User::find($request->student_id);
    $student->registration_status = 'rejected';
    $request = ScheduleRequest::find($request->request_id);
    $request->delete();
    $student->save();
    return User::find($request->student_id);
});
