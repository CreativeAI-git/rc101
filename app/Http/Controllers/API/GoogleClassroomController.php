<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassroomStudent;
use App\Models\Curriculum;
use App\Models\GoogleCourse;
use App\Models\GoogleAssignment;
use App\Models\GoogleCourseParticipant;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Classroom;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;
use Google\Service\Classroom\CourseWork;
use Google\Service\Classroom\Material;
use Google\Service\Classroom\Link;

class GoogleClassroomController extends Controller
{
    private $client;
    private $classroomService;

    public function __construct()
    {
        // Initialize Google Client
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path(env('GOOGLE_CREDENTIALS_PATH')));
        $this->client->addScope(Classroom::CLASSROOM_COURSES);
        $this->client->addScope(Classroom::CLASSROOM_ROSTERS);
        $this->client->addScope(Classroom::CLASSROOM_COURSEWORK_STUDENTS);
        $this->client->addScope(Classroom::CLASSROOM_PROFILE_EMAILS);
        // $this->client->addScope(Classroom::CLASSROOM_INVITATIONS);
        // $this->client->addScope(Classroom::CLASSROOM_COURSEWORK_ME);
        // $this->client->addScope(Classroom::CLASSROOM_ANNOUNCEMENTS);
        // $this->client->setAccessType('offline');
        // $this->client->setPrompt('consent');
    }

    // get the list of courses
    public function listCourses(Request $request)
    {
        try {
            $accessToken = $request->bearerToken();

            if (!$accessToken) {
                return response()->json(['message' => 'Google token not found'], 400);
            }

            $teacher = User::where('google_token', $accessToken)->first();

            // Fetch courses from the database for this teacher
            $courses = GoogleCourse::where('owner_id', $teacher->id)->get();

            $courseData = [];

            foreach ($courses as $course) {
                // Fetch students linked to this course
                $students = ClassroomStudent::where('course_id', $course->course_id)
                    ->get()
                    ->map(function ($student) {
                        return [
                            'name' => ucwords($student->name),
                            'email' => $student->email,
                        ];
                    })->toArray();

                $courseData[] = [
                    'id' => $course->course_id,
                    'name' => $course->name,
                    'section' => $course->section,
                    'description' => $course->description,
                    'room' => $course->room,
                    'ownerId' => $course->owner_id,
                    'students' => !empty($students) ? $students : null,
                ];
            }

            return response()->json(['success' => true, 'courses' => $courseData], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }

    // Create a course
    public function createCourse(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'section' => 'required|string|max:255',
            'room' => 'required|string|max:255',
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
        ]);

        $teacher = User::find($validated['owner_id']);

        if ($teacher->role_id !== 3) { // Ensure the user is a Teacher
            return response()->json(['success' => false, 'message' => 'Invalid teacher role'], 403);
        }

        $this->client->setAccessToken($teacher->google_token);

        $this->classroomService = new Classroom($this->client);

        $course = new \Google\Service\Classroom\Course([
            'name' => $validated['name'],
            'section' => $validated['section'],
            'room' => $validated['room'],
            'descriptionHeading' => $validated['name'],
            'description' => $validated['description'],
            'ownerId' => $teacher->email,
        ]);

        $createdCourse = $this->classroomService->courses->create($course);

        GoogleCourse::create([
            'course_id' => $createdCourse->id,
            'name' => $validated['name'],
            'section' => $validated['section'],
            'room' => $validated['room'],
            'description' => $validated['description'],
            'owner_id' => $teacher->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Course added successfully', 'course' => $createdCourse], 201);
    }

    // get the list of students
    public function listStudents(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['message' => 'Google token not found'], 400);
        }

        try {
            // Authenticate and find teacher
            $this->client = new \Google\Client();
            $this->client->setAccessToken($accessToken);

            $teacher = User::where('google_token', $accessToken)->first();

            if (!$teacher) {
                return response()->json(['success' => false, 'message' => 'Teacher not found'], 404);
            }

            // Fetch unique students and their latest created_at
            $studentsGrouped = ClassroomStudent::where('teacher_id', $teacher->id)
                ->selectRaw('student_id, name, email, MAX(created_at) as created_at')
                ->groupBy('student_id', 'name', 'email')
                ->get();

            if ($studentsGrouped->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No students found'], 404);
            }

            $students = [];

            foreach ($studentsGrouped as $student) {
                // Get course IDs for this student
                $courseIds = ClassroomStudent::where('teacher_id', $teacher->id)
                    ->where('student_id', $student->student_id)
                    ->pluck('course_id')
                    ->unique()
                    ->toArray();

                // Fetch course names
                $courseNames = GoogleCourse::whereIn('course_id', $courseIds)
                    ->pluck('name')
                    ->toArray();

                $students[] = [
                    'student_id' => $student->student_id,
                    'name' => ucwords($student->name),
                    'email' => $student->email,
                    'course_names' => implode(', ', $courseNames),
                    'created_at' => $student->created_at, // Include here
                ];
            }

            return response()->json([
                'success' => true,
                'students' => $students
            ], 200);
        } catch (\Google\Service\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function listNewStudents(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['message' => 'Google token not found'], 400);
        }

        try {
            $this->client = new \Google\Client();
            $this->client->setAccessToken($accessToken);

            $teacher = User::where('google_token', $accessToken)->first();

            if (!$teacher) {
                return response()->json(['success' => false, 'message' => 'Teacher not found'], 404);
            }

            // Get all registered students
            $registeredStudents = User::where('google_classroom_role', 'student')
                ->whereNotNull('google_token')
                ->get();

            $students = [];

            foreach ($registeredStudents as $student) {
                // Get course IDs where this student is enrolled under the current teacher
                $courseIds = ClassroomStudent::where('teacher_id', $teacher->id)
                    ->where('student_id', $student->google_id)
                    ->where('status', 'enrolled') // exclude invited
                    ->pluck('course_id')
                    ->unique()
                    ->toArray();

                // Fetch course names
                $courseNames = GoogleCourse::whereIn('course_id', $courseIds)
                    ->pluck('name')
                    ->toArray();

                $students[] = [
                    'student_id' => $student->google_id,
                    'name' => ucwords($student->name),
                    'email' => $student->email,
                    'course_names' => implode(', ', $courseNames),
                    'created_at' => $student->created_at,
                ];
            }

            if (empty($students)) {
                return response()->json(['success' => false, 'message' => 'No students found'], 404);
            }

            return response()->json([
                'success' => true,
                'students' => $students
            ], 200);
        } catch (\Google\Service\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    // Add a student to a course
    public function addStudent(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        // Get the authenticated teacher
        $teacher = User::where('google_token', $accessToken)->first();

        if (!$teacher || $teacher->google_classroom_role !== "teacher") {
            return response()->json(['success' => false, 'message' => 'Access denied. Only teachers can add students.'], 403);
        }

        // Validate request
        $validated = $request->validate([
            'course_id' => 'required|string',
            'user_id' => 'required|string', // Expecting a single user ID
        ]);

        // Check the teacher's subscription
        $subscription = UserSubscription::where('user_id', $teacher->id)
            ->where('status', 1)
            ->with('subscription')
            ->orderByDesc('subscription_id')
            ->first();

        if (!$subscription) {
            return response()->json(['success' => false, 'message' => 'No active subscription found. Please subscribe to a plan.'], 403);
        }

        // Define student limits based on subscription type
        $maxStudents = $subscription->type === 'free' ? $subscription->subscription->user_access_count : $subscription->subscription->user_access_count;
        $currentStudents = ClassroomStudent::where('teacher_id', $teacher->id)->count();

        if ($currentStudents >= $maxStudents) {
            return response()->json([
                'success' => false,
                'message' => "You have reached the limit of $maxStudents students for your subscription."
            ], 403);
        }
        // Google Classroom API setup
        $this->client->setAccessToken($accessToken);

        // Initialize the Google Classroom Service
        $this->classroomService = new \Google\Service\Classroom($this->client);

        $responses = [];
        $errors = [];

        try {
            // Prepare the invitation
            $invitation = new \Google\Service\Classroom\Invitation([
                'courseId' => $validated['course_id'],
                'role' => 'STUDENT',
                'userId' => $validated['user_id'],
            ]);

            // Send the invitation
            $result = $this->classroomService->invitations->create($invitation);

            $userDetails = User::where('google_id', $validated['user_id'])->first();

            // Log the added student
            ClassroomStudent::create([
                'teacher_id' => $teacher->id,
                'course_id' => $validated['course_id'],
                'student_id' => $validated['user_id'],
                'name' => $userDetails->name,
                'email' => $userDetails->email,
                'status' => 'invited'
            ]);

            $responses[] = [
                'user_google_id' => $validated['user_id'],
                'message' => 'Invitation sent successfully',
                'invitation' => $result,
            ];
        } catch (\Google\Service\Exception $e) {
            $errors[] = [
                'user_google_id' => $validated['user_id'],
                'message' => 'Requested entity already exists',
            ];
        } catch (\Exception $e) {
            $errors[] = [
                'user_google_id' => $validated['user_id'],
                'message' => 'The invited user already has the course role of a student',
            ];
        }

        return response()->json([
            'success' => empty($errors),
            'message' => !empty($responses) ? $responses[0]['message'] : $errors[0]['message'],
            'responses' => $responses,
            'errors' => $errors,
        ], 200);
    }

    // get the list of assignments
    public function listAssignments(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        $teacher = User::where('google_token', $accessToken)->first();

        $this->client->setAccessToken($teacher->google_token);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $assignments = GoogleAssignment::where('owner_id', $teacher->id)
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No assignments found.'
            ], 404);
        }

        $formatted = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'assignment_id' => $assignment->assignment_id,
                'course_id' => $assignment->course_id,
                'course_name' => optional($assignment->course)->name,
                'title' => $assignment->title,
                'max_points' => $assignment->max_points,
                'description' => $assignment->description,
                'due_date' => $assignment->due_date ? date('Y-m-d', strtotime($assignment->due_date)) : null,
                'due_time' => $assignment->due_time,
                'submitted_at' => $assignment->submitted_at,
                'attachment_link' => $assignment->attachment_link
                    ? json_decode($assignment->attachment_link, true)
                    : [],
                'status' => $assignment->status,
                'created_at' => $assignment->created_at,
                'updated_at' => $assignment->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'assignments' => $formatted
        ], 200);
    }

    // create an assignment
    public function createAssignment(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Google token not found'
            ], 400);
        }

        // --------------------
        // VALIDATION
        // --------------------
        $validated = $request->validate([
            'course_id' => 'required|exists:google_courses,course_id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'attachment_link' => 'nullable|array',
            'attachment_link.*' => 'nullable|url',
            'curriculum_ids' => 'nullable|array',
            'curriculum_ids.*' => 'integer|exists:cms_curriculums,id',
            'is_pushed_on_google' => 'required|boolean',
            'max_points' => 'nullable|numeric|min:0'
        ]);

        // max_points must be integer
        $validated['max_points'] = isset($validated['max_points'])
            ? (int) $validated['max_points']
            : null;

        // --------------------
        // GET TEACHER
        // --------------------
        $teacher = User::where('google_token', $accessToken)->first();
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found'
            ], 404);
        }

        // --------------------
        // PREPARE LOCAL DATA (UTC)
        // --------------------
        $assignmentData = [
            'course_id' => $validated['course_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'due_date' => $validated['due_date'] ?? null,   // UTC
            'due_time' => $validated['due_time'] ?? null,   // UTC
            'owner_id' => $teacher->id,
            'attachment_link' => !empty($validated['attachment_link'])
                ? json_encode($validated['attachment_link'])
                : null,
            'curriculum_ids' => !empty($validated['curriculum_ids'])
                ? json_encode($validated['curriculum_ids'])
                : null,
            'max_points' => $validated['max_points'],
            'is_pushed_on_google' => $validated['is_pushed_on_google'],
        ];

        try {

            // --------------------
            // PUSH TO GOOGLE CLASSROOM
            // --------------------
            if ($validated['is_pushed_on_google']) {

                $this->client->setAccessToken($accessToken);
                $this->classroomService = new \Google\Service\Classroom($this->client);

                // --------------------
                // GET COURSE TIMEZONE
                // --------------------
                $course = $this->classroomService
                    ->courses
                    ->get($validated['course_id']);

                $courseTimeZone = $course->timeZone ?? 'UTC';

                $courseworkData = [
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? '',
                    'workType' => 'ASSIGNMENT',
                    'state' => 'PUBLISHED',
                    'maxPoints' => $validated['max_points'],
                ];

                // --------------------
                // FORCE IST → UTC (-05:30) BEFORE GOOGLE
                // --------------------
                if (!empty($validated['due_date']) && !empty($validated['due_time'])) {

                    // Combine date + time as IST
                    $istDateTime = Carbon::createFromFormat(
                        'Y-m-d H:i',
                        $validated['due_date'] . ' ' . $validated['due_time'],
                        'Asia/Kolkata'
                    );

                    // Subtract 5 hours 30 minutes (IST → UTC)
                    $adjustedDateTime = $istDateTime->subHours(5)->subMinutes(30);

                    // Send adjusted values to Google
                    $courseworkData['dueDate'] = [
                        'year'  => (int) $adjustedDateTime->year,
                        'month' => (int) $adjustedDateTime->month,
                        'day'   => (int) $adjustedDateTime->day,
                    ];

                    $courseworkData['dueTime'] = [
                        'hours'   => (int) $adjustedDateTime->hour,
                        'minutes' => (int) $adjustedDateTime->minute,
                    ];
                }

                // --------------------
                // MATERIAL LINKS (SAFE)
                // --------------------
                if (!empty($validated['attachment_link'])) {

                    $validLinks = array_filter(
                        $validated['attachment_link'],
                        fn($link) => !empty($link)
                    );

                    if (!empty($validLinks)) {
                        $materials = [];

                        foreach ($validLinks as $link) {
                            $materials[] = new Material([
                                'link' => new Link(['url' => $link])
                            ]);
                        }

                        $courseworkData['materials'] = $materials;
                    }
                }

                // --------------------
                // CREATE ASSIGNMENT
                // --------------------
                $coursework = new CourseWork($courseworkData);
                $createdAssignment = $this->classroomService
                    ->courses_courseWork
                    ->create($validated['course_id'], $coursework);

                $assignmentData['assignment_id'] = $createdAssignment->id;
            }

            // --------------------
            // SAVE LOCALLY
            // --------------------
            $assignment = GoogleAssignment::create($assignmentData);

            return response()->json([
                'success' => true,
                'message' => $validated['is_pushed_on_google']
                    ? 'Assignment created and pushed to Google Classroom successfully'
                    : 'Assignment saved locally (not pushed to Google)',
                'assignment' => $assignment
            ], 201);
        } catch (\Google\Service\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    // this method is used for teacher dashboard
    public function teacherDashboard(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        $teacher = User::where('google_token', $accessToken)->first();

        if ($teacher->google_classroom_role !== "teacher") {
            return response()->json(['success' => false, 'message' => 'Access denied. Please log in with a teacher account.'], 400);
        }

        try {
            $this->client->setAccessToken($teacher->google_token);

            // Fetch all courses owned by the teacher
            $courses = GoogleCourse::where('owner_id', $teacher->id)->get();
            $totalCourses = $courses->count();
            // dd(count($courses));

            $totalAssignments = $courses->map(function ($course) {
                return $course->assignments->count();
            })->sum();

            // $studentCount = User::where('google_classroom_role', 'student')
            //     ->whereNotNull('google_id')
            //     ->count();

            $studentCount = ClassroomStudent::where('teacher_id', $teacher->id)
                ->distinct('student_id')
                ->count('student_id');

            $activeAssignments = $courses->map(function ($course) {
                return $course->assignments->where('due_date', '>', now())->count();
            })->sum();

            // $service = new Classroom($this->client);
            // $totalCourses = count($service->courses->listCourses());

            $subscription = UserSubscription::where('user_id', $teacher->id)
            ->with('subscription')
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_assignments' => $totalAssignments,
                    'students' => $studentCount,
                    'active_assignments' => $activeAssignments,
                    'total_courses' => $totalCourses,
                ],
                'subscriptions' => $subscription,
                'message' => 'Dashboard data fetched successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get invitation list sent by teacher
    public function getStudentInvitations(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        // Validate if the Google token is valid for a student
        $student = User::where('google_token', $accessToken)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found or invalid token'], 404);
        }

        $this->client->setAccessToken($accessToken);
        $this->classroomService = new \Google\Service\Classroom($this->client);

        // Validate request parameters
        $validated = $request->validate([
            'user_google_id' => 'nullable|string',
            'course_id' => 'nullable|string',
        ]);

        if (empty($validated['user_google_id']) && empty($validated['course_id'])) {
            return response()->json(['success' => false, 'message' => 'Either user_google_id or course_id must be provided'], 400);
        }

        try {
            // Prepare query parameters
            $queryParams = [];

            if (!empty($validated['user_google_id'])) {
                $queryParams['userId'] = $validated['user_google_id'];
            }

            if (!empty($validated['course_id'])) {
                $queryParams['courseId'] = $validated['course_id'];
            }

            // Fetch invitations based on the parameters
            $invitationsList = $this->classroomService->invitations->listInvitations($queryParams);
            $invitations = $invitationsList->getInvitations();

            $responseData = [];

            if ($invitations) {
                foreach ($invitations as $invitation) {
                    try {
                        // Fetch course details
                        $course = $this->classroomService->courses->get($invitation->getCourseId());

                        // Fetch inviter details
                        $inviterName = null;
                        if (!empty($course->getOwnerId())) {
                            $inviterProfile = $this->classroomService->userProfiles->get($course->getOwnerId());
                            $inviterName = $inviterProfile->getName()->getFullName();
                        }

                        $responseData[] = [
                            'invitation_id' => $invitation->getId(),
                            'course_id' => $invitation->getCourseId(),
                            'course_name' => $course->getName(),
                            'role' => $invitation->getRole(),
                            'invited_by_name' => $inviterName,
                        ];
                    } catch (\Google\Service\Exception $e) {
                        // If course details or inviter details are not found, skip
                        continue;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'invitations' => $responseData,
            ], 200);
        } catch (\Google\Service\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching invitations',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }

    // accept the invitations send by teacher
    public function acceptInvitation(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        // Validate if the Google token is valid for a student
        $student = User::where('google_token', $accessToken)->first();

        if (!$student || $student->google_classroom_role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Student not found or invalid token'], 404);
        }

        $this->client->setAccessToken($accessToken);

        // Initialize Google Classroom Service
        $this->classroomService = new \Google\Service\Classroom($this->client);

        // Validate request parameters
        $validated = $request->validate([
            'invitation_id' => 'required|string', // Invitation ID to accept
        ]);

        try {
            // Accept the invitation in Google Classroom
            $this->classroomService->invitations->accept($validated['invitation_id']);

            // Fetch invitation details (to know courseId + teacherId)
            $invitation = $this->classroomService->invitations->get($validated['invitation_id']);

            // Update student status in your DB
            ClassroomStudent::where('student_id', $student->google_id)
                ->where('course_id', $invitation->courseId)
                ->update(['status' => 'enrolled']);

            return response()->json([
                'success' => true,
                'message' => 'Invitation accepted successfully',
            ], 200);
        } catch (\Google\Service\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error accepting invitation: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 401);
        }
    }

    // reject the invitations send by teacher
    public function deleteInvitation(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        // Validate if the Google token is valid for a student
        $student = User::where('google_token', $accessToken)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found or invalid token'], 404);
        }

        $this->client->setAccessToken($accessToken);

        // Check if the token has expired
        // if ($this->client->isAccessTokenExpired()) {
        //     $refreshToken = $student->google_refresh_token;

        //     if ($refreshToken) {
        //         $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
        //     } else {
        //         return response()->json(['success' => false, 'message' => 'Refresh token not found'], 401);
        //     }
        // }

        // Initialize Google Classroom Service
        $this->classroomService = new \Google\Service\Classroom($this->client);

        // Validate request parameters
        $validated = $request->validate([
            'invitation_id' => 'required|string', // Invitation ID to delete
        ]);

        try {
            // decline the invitation
            $this->classroomService->invitations->delete($validated['invitation_id']);

            return response()->json([
                'success' => true,
                'message' => 'Invitation rejected successfully',
            ], 200);
        } catch (\Google\Service\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting invitation',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 401);
        }
    }

    // this method is used to get the courses of student
    public function getStudentCourses(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        // Validate if the Google token is valid for a student
        $student = User::where('google_token', $accessToken)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found or invalid token'], 404);
        }

        $this->client->setAccessToken($accessToken);

        // $student = User::where('email', 'testing100cti@gmail.com')->first();

        try {
            // Fetch the courses the student is enrolled in
            $courses = ClassroomStudent::where('student_id', $student->google_id)->get();

            if (empty($courses)) {
                return response()->json(['success' => false, 'message' => 'No courses available.'], 404);
            }

            $courseData = [];
            foreach ($courses as $course) {
                // fetch inviter's name
                $courseDetails = GoogleCourse::where('course_id', $course->course_id)->first();
                $teachers = User::where('id', $courseDetails->owner_id)->first();
                if ($courseDetails) {
                    $courseData[] = [
                        'course_name' => $courseDetails->name,
                        'section' => $courseDetails->section,
                        'description' => $courseDetails->description,
                        'course_id' => $course->course_id,
                        'created_by' => $teachers->name,
                    ];
                }
            }

            return response()->json(['success' => true, 'courses' => $courseData], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching courses: ' . $e->getMessage()], 500);
        }
    }

    // this method is used to get the assignments of student
    public function getStudentAssignments($course_id, Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Google token not found'
            ], 400);
        }

        // Validate student using Google token
        $student = User::where('google_token', $accessToken)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or invalid token'
            ], 404);
        }

        $this->client->setAccessToken($accessToken);

        // Fetch course using route parameter
        $course = GoogleCourse::where('course_id', $course_id)->first();

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found.'
            ], 404);
        }

        // Get assignments for the course
        $assignments = GoogleAssignment::where('course_id', $course_id)
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'assignments' => $assignments
        ], 200);
    }

    // this method is used to get the courses of student
    public function getStudentCourses_old(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        // Validate if the Google token is valid for a student
        $student = User::where('google_token', $accessToken)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found or invalid token'], 404);
        }

        $this->client->setAccessToken($accessToken);

        // Check if the token has expired
        // if ($this->client->isAccessTokenExpired()) {
        //     $refreshToken = $student->google_refresh_token;

        //     if ($refreshToken) {
        //         $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
        //     } else {
        //         return response()->json(['success' => false, 'message' => 'Refresh token not found'], 401);
        //     }
        // }

        // Initialize the Google Classroom service
        $classroomService = new \Google\Service\Classroom($this->client);

        try {
            // Fetch the courses the student is enrolled in
            $courses = $classroomService->courses->listCourses()->getCourses();

            if (empty($courses)) {
                return response()->json(['success' => false, 'message' => 'No courses available.'], 404);
            }

            // Extract course names and links
            $courseLinks = [];
            foreach ($courses as $course) {
                $inviterName = null;

                try {
                    // fetch inviter's name
                    if ($course->getOwnerId()) {
                        $inviterProfile = $classroomService->userProfiles->get($course->getOwnerId());
                        $inviterName = $inviterProfile->getName()->getFullName();
                    }
                } catch (\Exception $e) {
                    $inviterName = 'Unknown';
                }

                $courseLinks[] = [
                    'name' => $course->getName(),
                    'link' => $course->getAlternateLink(),
                    'created_by' => $inviterName,
                ];
            }

            return response()->json(['success' => true, 'courses' => $courseLinks], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching courses: ' . $e->getMessage()], 500);
        }
    }

    // Sync all Google Classroom data
    private function getCourseAssignments($courseId, $token)
    {
        $response = Http::withToken($token)
            ->get("https://classroom.googleapis.com/v1/courses/{$courseId}/courseWork");

        return $response->successful() ? $response->json('courseWork') ?? [] : [];
    }

    private function getCourseStudents($courseId, $token)
    {
        $response = Http::withToken($token)
            ->get("https://classroom.googleapis.com/v1/courses/{$courseId}/students");

        return $response->successful() ? $response->json('students') ?? [] : [];
    }

    private function getDueDate($assignment)
    {
        if (!isset($assignment['dueDate'])) return null;

        $date = $assignment['dueDate'];
        return sprintf('%04d-%02d-%02d', $date['year'], $date['month'], $date['day']);
    }

    private function getDueTime($assignment)
    {
        if (!isset($assignment['dueTime'])) return null;

        $time = $assignment['dueTime'];
        return sprintf('%02d:%02d', $time['hours'] ?? 0, $time['minutes'] ?? 0);
    }

    // get all courses
    public function getAllCourses(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 401);
        }

        // Set the access token for the Google client
        $this->client->setAccessToken($accessToken);

        $response = Http::withToken($accessToken)
            ->get('https://classroom.googleapis.com/v1/courses');

        if (!$response->successful()) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch courses'], 401);
        }

        $courses = $response->json('courses') ?? [];

        //Filter only courses with courseState == 'ACTIVE'
        $activeCourses = array_filter($courses, function ($course) {
            return isset($course['courseState']) && $course['courseState'] === 'ACTIVE';
        });

        // Re-index the array
        $activeCourses = array_values($activeCourses);

        return response()->json(['success' => true, 'courses' => $activeCourses], 200);
    }

    // get course with filters
    public function importSelectedCourses(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        $validated = $request->validate([
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'string',
            'owner_id' => 'required|exists:users,id',
        ]);

        $teacher = User::find($validated['owner_id']);
        $token = $teacher->google_token;
        $courseIds = $request->course_ids;

        foreach ($courseIds as $courseId) {
            $course = $this->getSingleCourse($courseId, $token);
            if (!$course) continue;

            $courseModel = GoogleCourse::updateOrCreate(
                ['course_id' => $course['id']],
                [
                    'name' => $course['name'] ?? '',
                    'section' => $course['section'] ?? null,
                    'description' => $course['description'] ?? null,
                    'owner_id' => $teacher->id,
                ]
            );

            $assignments = $this->getCourseAssignments($courseId, $token);
            foreach ($assignments as $assignment) {
                // Extract attachment link if available
                $attachmentLink = null;

                if (!empty($assignment['materials']) && is_array($assignment['materials'])) {
                    foreach ($assignment['materials'] as $material) {
                        if (isset($material['driveFile']['driveFile']['alternateLink'])) {
                            $attachmentLink = $material['driveFile']['driveFile']['alternateLink'];
                            break; // Take the first available attachment link
                        }
                    }
                }

                GoogleAssignment::updateOrCreate(
                    ['assignment_id' => $assignment['id']],
                    [
                        'course_id' => $courseId,
                        'title' => $assignment['title'] ?? '',
                        'description' => $assignment['description'] ?? null,
                        'due_date' => $this->getDueDate($assignment),
                        'due_time' => $this->getDueTime($assignment),
                        'owner_id' => $teacher->id,
                        'attachment_link' => $attachmentLink, // Save the attachment link
                    ]
                );
            }

            $students = $this->getCourseStudents($courseId, $token);
            foreach ($students as $student) {
                ClassroomStudent::updateOrCreate(
                    ['student_id' => $student['userId'], 'course_id' => $courseId],
                    [
                        'name' => $student['profile']['name']['fullName'] ?? '',
                        'email' => $student['profile']['emailAddress'] ?? '',
                        'teacher_id' => $teacher->id,
                    ]
                );
            }
        }

        return response()->json(['success' => true, 'message' => 'Selected courses synced successfully'], 200);
    }

    // get course details
    private function getSingleCourse($courseId, $token)
    {
        $response = Http::withToken($token)
            ->get("https://classroom.googleapis.com/v1/courses/{$courseId}");
        return $response->successful() ? $response->json() : null;
    }

    // get all assignments
    public function getAllAssignments(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['error' => 'Google token not found'], 401);
        }

        // Step 1: Get all courses
        $coursesResponse = Http::withToken($accessToken)
            ->get("https://classroom.googleapis.com/v1/courses");

        if ($coursesResponse->failed()) {
            return response()->json(['error' => 'Failed to fetch courses'], $coursesResponse->status());
        }

        $courses = $coursesResponse->json('courses') ?? [];
        $allAssignments = [];

        // Step 2: Loop over each course and get assignments
        foreach ($courses as $course) {
            $courseId = $course['id'];

            $assignmentsResponse = Http::withToken($accessToken)
                ->get("https://classroom.googleapis.com/v1/courses/{$courseId}/courseWork");

            if ($assignmentsResponse->successful()) {
                $courseAssignments = $assignmentsResponse->json('courseWork') ?? [];
                foreach ($courseAssignments as $assignment) {
                    $assignment['courseId'] = $courseId;
                    $assignment['courseName'] = $course['name'] ?? null;
                    $allAssignments[] = $assignment;
                }
            }
        }

        return response()->json([
            'assignments' => $allAssignments,
            'count' => count($allAssignments),
        ]);
    }

    // get students by the course
    public function listStudentsByCourse(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['message' => 'Google token not found'], 400);
        }

        $request->validate([
            'course_id' => 'required|string'
        ]);

        try {
            // Authenticate and find teacher
            $this->client = new \Google\Client();
            $this->client->setAccessToken($accessToken);

            $teacher = User::where('google_token', $accessToken)->first();

            if (!$teacher) {
                return response()->json(['success' => false, 'message' => 'Teacher not found'], 404);
            }

            $courseId = $request->input('course_id');

            // Fetch students only for this specific course
            $studentsGrouped = ClassroomStudent::where('teacher_id', $teacher->id)
                ->where('course_id', $courseId)
                ->selectRaw('student_id, name, email, MAX(created_at) as created_at')
                ->groupBy('student_id', 'name', 'email')
                ->get();

            if ($studentsGrouped->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No students found for this course'], 404);
            }

            $students = [];

            // Get course name once
            $course = GoogleCourse::where('course_id', $courseId)->first();
            $courseName = $course ? $course->name : 'Unknown Course';

            foreach ($studentsGrouped as $student) {
                $students[] = [
                    'student_id' => $student->student_id,
                    'name' => ucwords($student->name),
                    'email' => $student->email,
                    'course_name' => $courseName,
                    'created_at' => $student->created_at,
                ];
            }

            return response()->json([
                'success' => true,
                'course_id' => $courseId,
                'course_name' => $courseName,
                'students' => $students
            ], 200);
        } catch (\Google\Service\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAssignmentListByCourse(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Google token not found'], 400);
        }

        // Validate request input
        $validated = $request->validate([
            'course_id' => 'required|string',
        ]);

        try {
            // Find teacher using access token
            $teacher = User::where('google_token', $accessToken)->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher not found or unauthorized.'
                ], 401);
            }

            $this->client = new \Google\Client();
            $this->client->setAccessToken($teacher->google_token);

            // Fetch course
            $course = GoogleCourse::where('course_id', $validated['course_id'])->first();

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found.'
                ], 404);
            }

            // Get all assignments for this teacher and course
            $assignments = GoogleAssignment::where('owner_id', $teacher->id)
                ->where('course_id', $validated['course_id'])
                ->with('course')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($assignments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No assignments found for this course.',
                    'course_id' => $course->course_id,
                    'course_name' => $course->name,
                    'assignments' => []
                ], 404);
            }

            // Format assignments
            $formatted = $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'assignment_id' => $assignment->assignment_id,
                    'course_id' => $assignment->course_id,
                    'course_name' => optional($assignment->course)->name,
                    'title' => $assignment->title,
                    'max_points' => $assignment->max_points,
                    'description' => $assignment->description,
                    'due_date' => $assignment->due_date
                        ? date('Y-m-d', strtotime($assignment->due_date))
                        : null,
                    'due_time' => $assignment->due_time,
                    'submitted_at' => $assignment->submitted_at,
                    'attachment_link' => $assignment->attachment_link
                        ? json_decode($assignment->attachment_link, true)
                        : [],
                    'status' => $assignment->status,
                    'created_at' => $assignment->created_at,
                    'updated_at' => $assignment->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'course_id' => $course->course_id,
                'course_name' => $course->name,
                'assignments' => $formatted
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }


    // delete course by teacher
    public function deleteCourse($course_id)
    {
        $course = GoogleCourse::where('course_id', $course_id)->first();

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found.'
            ], 404);
        }

        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully.'
        ], 200);
    }

    // delete assignment by teacher
    public function deleteAssignment($assignment_id)
    {
        // Try to find by assignment_id OR id
        $assignment = GoogleAssignment::where('assignment_id', $assignment_id)
            ->orWhere('id', $assignment_id)
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found.'
            ], 404);
        }

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment deleted successfully.'
        ], 200);
    }
}
