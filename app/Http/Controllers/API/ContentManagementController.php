<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BattleBot;
use App\Models\EventCoverage;
use App\Models\Bot;
use App\Models\BotType;
use App\Models\Category;
use App\Models\ControlBot;
use App\Models\Curriculum;
use App\Models\DriveSystem;
use App\Models\DrumSpinner;
use App\Models\Embed;
use App\Models\FireBot;
use App\Models\FlipperBot;
use App\Models\Gearbox;
use App\Models\GoogleAssignment;
use App\Models\HammerBot;
use App\Models\HandTool;
use App\Models\HorizontalSpinner;
use App\Models\Lesson3dModeling;
use App\Models\Lesson3dPrinting;
use App\Models\LessonBatteries;
use App\Models\LessonBescs;
use App\Models\LessonBrushedBrushless;
use App\Models\LessonElectricalEngineering;
use App\Models\LessonGearRatios;
use App\Models\LessonMaterialScience;
use App\Models\LessonPcbs;
use App\Models\LessonPhysicsGeometry;
use App\Models\LessonReceivers;
use App\Models\LessonSlicing;
use App\Models\LessonSoldering;
use App\Models\LessonThinkercad;
use App\Models\LessonWeaponPhysics;
use App\Models\Partner;
use App\Models\Presentation;
use App\Models\Service;
use App\Models\LeagueRule;
use App\Models\MediaContent;
use App\Models\ModesOfTransferringMotion;
use App\Models\Nhrl;
use App\Models\OverheadSaw;
use App\Models\Plant;
use App\Models\PowerTool;
use App\Models\PrintedCircuitBoard;
use App\Models\Rcl;
use App\Models\Ssp;
use App\Models\TeacherDashboardContent;
use App\Models\ToolsTrade;
use App\Models\Tournament;
use App\Models\TurnaBot;
use App\Models\TypesOfDriveSystem;
use App\Models\VerticalSpinner;
use App\Models\VisitorEmail;
use App\Models\WeaponsSystem;
use App\Models\WeightAntweight;
use App\Models\WeightBeetleweight;
use App\Models\WeightClass;
use App\Models\WeightClassCategory;
use App\Models\WeightFairyweight;
use App\Models\WeightFeatherweight;
use App\Models\WeightHobbyweight;
use App\Models\WeightPlasticAntweight;
use App\Models\WeightSportsman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use View;

class ContentManagementController extends Controller
{
    /**
     * Write code on this method for get about page detail
     *
     * @return response()
     */
    public function getAboutSection()
    {
        $aboutSection = DB::table('cms_about_page')->orderBy('id', 'DESC')->first();

        if ($aboutSection) {
            $aboutSection->about_banner = asset('cms_images/' . $aboutSection->about_banner);
            $response = [
                'success' => true,
                'message' => 'About section retrieved successfully.',
                'data' => $aboutSection,
            ];
            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get visitor content
     *
     * @return response()
     */
    public function getVisitorContent()
    {
        $visitorContent = DB::table('cms_visitor_emails')->orderBy('id', 'DESC')->first();

        if ($visitorContent) {
            $response = [
                'success' => true,
                'message' => 'Visitor content retrieved successfully.',
                'data' => $visitorContent,
            ];
            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }
    /**
     * Write code on this method for get curriculum overview
     *
     * @return response()
     */
    public function getCurriculumOverview()
    {
        $curriculumOverview = DB::table('cms_curriculum_contents')->orderBy('id', 'DESC')->first();

        if ($curriculumOverview) {
            $response = [
                'success' => true,
                'message' => 'Curriculum overview retrieved successfully.',
                'data' => $curriculumOverview,
            ];
            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get home page detail
     *
     * @return response()
     */
    public function getHomeSection()
    {
        $homeSection = DB::table('cms_home_page')->orderBy('id', 'DESC')->first();

        if ($homeSection) {
            $homeSection->banner_image = asset('cms_images/' . $homeSection->banner_image);
            $homeSection->youtube_image = asset('cms_images/' . $homeSection->youtube_image);
            $response = [
                'success' => true,
                'message' => 'Home section retrieved successfully.',
                'data' => $homeSection,
            ];
            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get league page details
     *
     * @return response()
     */
    public function getLeaguePage()
    {
        $leaguePage = DB::table('cms_leagues')->orderBy('id', 'DESC')->first();

        if ($leaguePage) {
            $leaguePage->banner_image = asset('cms_images/leagues/' . $leaguePage->banner_image);
            $leaguePage->league_cover_image = asset('cms_images/leagues/' . $leaguePage->league_cover_image);
            $response = [
                'success' => true,
                'message' => 'League page details retrieved successfully.',
                'data' => $leaguePage,
            ];
            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get terms and conditions
     *
     * @return response()
     */
    public function getGlossaryTerm()
    {
        $glossaryTerm = DB::table('cms_glossary_of_terms')->orderBy('id', 'DESC')->first();

        if ($glossaryTerm) {
            $response = [
                'success' => true,
                'message' => 'Glossary of terms retrieved successfully.',
                'data' => $glossaryTerm,
            ];
            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get privacy policy
     *
     * @return response()
     */
    public function getPrivacyPolicy()
    {
        $privacyPolicy = DB::table('cms_privacy_policy')->orderBy('id', 'DESC')->first();

        if ($privacyPolicy) {
            $response = [
                'success' => true,
                'message' => 'Privacy Policy retrieved successfully.',
                'data' => $privacyPolicy,
            ];
            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get all services
     *
     * @return response()
     */
    public function getAllService()
    {
        $services = Service::orderBy('id', 'DESC')->get();

        if ($services->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No service details found!',
            ], 200);
        }

        // Append full image path to each service
        foreach ($services as $service) {
            $service->service_image = asset('cms_images/services/' . $service->service_image);
        }

        $response = [
            'success' => true,
            'message' => 'Services retrieved successfully.',
            'data' => $services,
        ];

        return response()->json($response, 200);
    }

    /**
     * Write code on this method for get service details
     *
     * @return response()
     */
    public function getServiceDetails(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $serviceData = Service::where('id', $request->query('id'))
            ->orderBy('id', 'DESC')
            ->first();

        if ($serviceData) {
            $serviceData->service_image = asset('cms_images/services/' . $serviceData->service_image);
            return response()->json([
                'success' => true,
                'message' => 'Service details retrieved successfully.',
                'data' => $serviceData,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No service details found!',
            ], 404);
        }
    }

    /**
     * Write code on this method for get all partners
     *
     * @return response()
     */
    public function getAllPartner()
    {
        $partners = Partner::orderBy('id', 'DESC')->get();

        if ($partners->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No company details found!',
            ], 200);
        }

        // Append full image path to each partner
        foreach ($partners as $partner) {
            $partner->company_image = asset('cms_images/partners/' . $partner->company_image);
        }

        $response = [
            'success' => true,
            'message' => 'Partners retrieved successfully.',
            'data' => $partners,
        ];

        return response()->json($response, 200);
    }

    /**
     * Write code on this method for get all bots
     *
     * @return response()
     */
    public function getAllBotTypes()
    {
        $botTypes = BotType::orderBy('id', 'DESC')->get();

        if ($botTypes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No bot types found!',
            ], 200);
        }

        $response = [
            'success' => true,
            'message' => 'Bot Types retrieved successfully.',
            'data' => $botTypes,
        ];

        return response()->json($response, 200);
    }

    /**
     * Write code on this method for get all bots
     *
     * @return response()
     */
    public function getAllWeightClassCategories()
    {
        $weightClassCategory = WeightClassCategory::orderBy('id', 'ASC')->get();

        if ($weightClassCategory->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No weight class categories found!',
            ], 200);
        }

        $response = [
            'success' => true,
            'message' => 'Weight Class Categories retrieved successfully.',
            'data' => $weightClassCategory,
        ];

        return response()->json($response, 200);
    }

    /**
     * Write code on this method for get all bots
     *
     * @return response()
     */
    public function getAllBots()
    {
        $bots = Bot::orderBy('name', 'ASC')->with(['botType', 'weightClass', 'createdBy'])->get();

        if ($bots->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No bot details found!',
            ], 200);
        }

        $response = [
            'success' => true,
            'message' => 'Bots retrieved successfully.',
            'data' => $bots,
        ];

        return response()->json($response, 200);
    }

    /**
     * Write code on this method for create bots
     *
     * @return response()
     */
    public function createBot(Request $request)
    {
        // Validate the request
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'design_type' => 'nullable|in:Custom,Kit',
            'start_date' => 'nullable|date|after_or_equal:today',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error!',
                'errors' => $validate->errors(),
            ], 422);
        }

        // Handle image upload
        $image = '';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('cms_images/bots'), $fileName);
            $image = $fileName;
        }

        // Insert data into the database
        $botData = [
            'name' => $request->name,
            'bot_type_id' => $request->bot_type_id,
            'design_type' => $request->design_type,
            'weight_class_id' => $request->weight_class_id,
            'start_date' => $request->start_date,
            'description' => $request->description,
            'image' => $image,
            'created_by' => $request->created_by,
        ];

        $isInserted = Bot::insert($botData);

        if ($isInserted) {
            return response()->json([
                'success' => true,
                'message' => 'Bot created successfully!',
                'data' => $botData,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while creating the bot!',
            ], 500);
        }
    }

    /**
     * Write code on this method for update bots
     *
     * @return response()
     */
    public function updateBot(Request $request)
    {
        // Find the bot by ID
        $bot = Bot::find($request->bot_id);

        if (!$bot) {
            return response()->json([
                'success' => false,
                'message' => 'Bot not found!',
            ], 404);
        }

        // Validate the request
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'design_type' => 'nullable|in:Custom,Kit',
            'start_date' => 'nullable|date|after_or_equal:today',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error!',
                'errors' => $validate->errors(),
            ], 422);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($bot->image && file_exists(public_path('cms_images/bots/' . $bot->image))) {
                unlink(public_path('cms_images/bots/' . $bot->image));
            }

            $image = $request->file('image');
            $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('cms_images/bots'), $fileName);
            $bot->image = $fileName;
        }

        // Update bot data
        $bot->name = $request->name;
        $bot->bot_type_id = $request->bot_type_id ?? $bot->bot_type_id;
        $bot->design_type = $request->design_type ?? $bot->design_type;
        $bot->weight_class_id = $request->weight_class_id ?? $bot->weight_class_id;
        $bot->start_date = $request->start_date ?? $bot->start_date;
        $bot->description = $request->description ?? $bot->description;
        $bot->created_by = $request->created_by ?? $bot->created_by;

        $bot->save();

        return response()->json([
            'success' => true,
            'message' => 'Bot updated successfully!',
            'data' => $bot,
        ], 200);
    }

    /**
     * Write code on this method for get tournaments
     *
     * @return response()
     */
    public function getAllTournament()
    {
        $tournamentData = Tournament::orderBy('id', 'DESC')->get();

        if ($tournamentData->isNotEmpty()) {
            $tournamentData->transform(function ($tournament) {
                $tournament->banner_image = asset('cms_images/leagues/tournaments/' . $tournament->banner_image);
                return $tournament;
            });

            $response = [
                'success' => true,
                'message' => 'Tournaments retrieved successfully.',
                'data' => $tournamentData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get presentations
     *
     * @return response()
     */
    public function getAllPresentation()
    {
        $presentationData = Presentation::orderBy('id', 'DESC')->get();

        if ($presentationData->isNotEmpty()) {
            $presentationData->transform(function ($presentation) {
                if ($presentation->presentation_cover_image) {
                    $presentation->presentation_cover_image = asset('cms_images/leagues/presentations/' . $presentation->presentation_cover_image);
                } else {
                    $presentation->presentation_cover_image = asset('admin/img/bg-img/placeholder.jpg');
                }
                return $presentation;
            });

            $response = [
                'success' => true,
                'message' => 'Presentations retrieved successfully.',
                'data' => $presentationData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get Weight Class Restrictions
     *
     * @return response()
     */
    public function getAllWeightClassRestriction()
    {
        $weightClassData = WeightClass::orderBy('id', 'DESC')->get();

        if ($weightClassData->isNotEmpty()) {

            $response = [
                'success' => true,
                'message' => 'Weight Class Restrictions retrieved successfully.',
                'data' => $weightClassData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get League Rules
     *
     * @return response()
     */
    public function getAllLeagueRule()
    {
        $sparcRuleData = LeagueRule::orderBy('id', 'DESC')->get();

        if ($sparcRuleData->isNotEmpty()) {

            $response = [
                'success' => true,
                'message' => 'League Rules retrieved successfully.',
                'data' => $sparcRuleData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get event coverage/results
     *
     * @return response()
     */
    public function getAllEventCoverage()
    {
        $eventCoverageData = EventCoverage::orderBy('id', 'DESC')->get();

        if ($eventCoverageData->isNotEmpty()) {
            $eventCoverageData->transform(function ($eventCoverage) {
                $eventCoverage->event_coverage_image = asset('cms_images/' . $eventCoverage->event_coverage_image);
                return $eventCoverage;
            });

            $response = [
                'success' => true,
                'message' => 'Event Coverage/Results retrieved successfully.',
                'data' => $eventCoverageData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get tools of the trade
     *
     * @return response()
     */
    public function getAllToolsTrade()
    {
        $toolsTradeData = ToolsTrade::orderBy('id', 'DESC')->get();

        if ($toolsTradeData->isNotEmpty()) {
            $toolsTradeData->transform(function ($toolsTrade) {
                $toolsTrade->tools_trade_image = asset('cms_images/' . $toolsTrade->tools_trade_image);
                return $toolsTrade;
            });

            $response = [
                'success' => true,
                'message' => 'Tools of the Trade retrieved successfully.',
                'data' => $toolsTradeData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get 3d modeling
     *
     * @return response()
     */
    public function getAllLesson3dModeling()
    {
        $modelingData = Lesson3dModeling::orderBy('id', 'DESC')->get();

        if ($modelingData->isNotEmpty()) {
            $modelingData->transform(function ($modeling) {
                $modeling->lesson_cover_image = asset('cms_images/lesson/modeling/' . $modeling->lesson_cover_image);
                return $modeling;
            });

            $response = [
                'success' => true,
                'message' => '3d Modiling retrieved successfully.',
                'data' => $modelingData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get 3d printing
     *
     * @return response()
     */
    public function getAllLesson3dPrinting()
    {
        $printingData = Lesson3dPrinting::orderBy('id', 'DESC')->get();

        if ($printingData->isNotEmpty()) {
            $printingData->transform(function ($printing) {
                $printing->lesson_cover_image = asset('cms_images/lesson/printing/' . $printing->lesson_cover_image);
                return $printing;
            });

            $response = [
                'success' => true,
                'message' => '3d Printing retrieved successfully.',
                'data' => $printingData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get batteries
     *
     * @return response()
     */
    public function getAllLessonBatteries()
    {
        $batteryData = LessonBatteries::orderBy('id', 'DESC')->get();

        if ($batteryData->isNotEmpty()) {
            $batteryData->transform(function ($batteries) {
                $batteries->lesson_cover_image = asset('cms_images/lesson/batteries/' . $batteries->lesson_cover_image);
                return $batteries;
            });

            $response = [
                'success' => true,
                'message' => 'Batteries retrieved successfully.',
                'data' => $batteryData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get escs/bescs
     *
     * @return response()
     */
    public function getAllLessonBescs()
    {
        $bescsData = LessonBescs::orderBy('id', 'DESC')->get();

        if ($bescsData->isNotEmpty()) {
            $bescsData->transform(function ($bescs) {
                $bescs->lesson_cover_image = asset('cms_images/lesson/bescs/' . $bescs->lesson_cover_image);
                return $bescs;
            });

            $response = [
                'success' => true,
                'message' => 'ESCs/BESCs retrieved successfully.',
                'data' => $bescsData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get brushed vs brushless
     *
     * @return response()
     */
    public function getAllLessonBrushedBrushless()
    {
        $brushedBrushlessData = LessonBrushedBrushless::orderBy('id', 'DESC')->get();

        if ($brushedBrushlessData->isNotEmpty()) {
            $brushedBrushlessData->transform(function ($brushedBrushless) {
                $brushedBrushless->lesson_cover_image = asset('cms_images/lesson/brushed_brushless/' . $brushedBrushless->lesson_cover_image);
                return $brushedBrushless;
            });

            $response = [
                'success' => true,
                'message' => 'Brushed vs. Brushless retrieved successfully.',
                'data' => $brushedBrushlessData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get electrical engineering
     *
     * @return response()
     */
    public function getAllLessonElectricalEngineering()
    {
        $electricalEngineeringData = LessonElectricalEngineering::orderBy('id', 'DESC')->get();

        if ($electricalEngineeringData->isNotEmpty()) {
            $electricalEngineeringData->transform(function ($electricalEngineering) {
                $electricalEngineering->lesson_cover_image = asset('cms_images/lesson/electrical_engineering/' . $electricalEngineering->lesson_cover_image);
                return $electricalEngineering;
            });

            $response = [
                'success' => true,
                'message' => 'Electrical Engineering retrieved successfully.',
                'data' => $electricalEngineeringData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get fusion
     *
     * @return response()
     */
    public function getAllLessonFusion()
    {
        $fusionData = LessonElectricalEngineering::orderBy('id', 'DESC')->get();

        if ($fusionData->isNotEmpty()) {
            $fusionData->transform(function ($fusion) {
                $fusion->lesson_cover_image = asset('cms_images/lesson/fusion/' . $fusion->lesson_cover_image);
                return $fusion;
            });

            $response = [
                'success' => true,
                'message' => 'Fusion retrieved successfully.',
                'data' => $fusionData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get gear ratios
     *
     * @return response()
     */
    public function getAllLessonGearRatios()
    {
        $gearRatioData = LessonGearRatios::orderBy('id', 'DESC')->get();

        if ($gearRatioData->isNotEmpty()) {
            $gearRatioData->transform(function ($gearRatio) {
                $gearRatio->lesson_cover_image = asset('cms_images/lesson/gear_ratios/' . $gearRatio->lesson_cover_image);
                return $gearRatio;
            });

            $response = [
                'success' => true,
                'message' => 'Gear Ratios retrieved successfully.',
                'data' => $gearRatioData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get material science
     *
     * @return response()
     */
    public function getAllLessonMaterialScience()
    {
        $materialScienceData = LessonMaterialScience::orderBy('id', 'DESC')->get();

        if ($materialScienceData->isNotEmpty()) {
            $materialScienceData->transform(function ($materialScience) {
                $materialScience->lesson_cover_image = asset('cms_images/lesson/material_science/' . $materialScience->lesson_cover_image);
                return $materialScience;
            });

            $response = [
                'success' => true,
                'message' => 'Material Science retrieved successfully.',
                'data' => $materialScienceData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get PCBs
     *
     * @return response()
     */
    public function getAllLessonPcbs()
    {
        $pcbsData = LessonPcbs::orderBy('id', 'DESC')->get();

        if ($pcbsData->isNotEmpty()) {
            $pcbsData->transform(function ($pcbs) {
                $pcbs->lesson_cover_image = asset('cms_images/lesson/pcbs/' . $pcbs->lesson_cover_image);
                return $pcbs;
            });

            $response = [
                'success' => true,
                'message' => 'PCBs retrieved successfully.',
                'data' => $pcbsData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get physics geometry
     *
     * @return response()
     */
    public function getAllLessonPhysicsGeometry()
    {
        $physicsGeometryData = LessonPhysicsGeometry::orderBy('id', 'DESC')->get();

        if ($physicsGeometryData->isNotEmpty()) {
            $physicsGeometryData->transform(function ($physicsGeometry) {
                $physicsGeometry->lesson_cover_image = asset('cms_images/lesson/physics_geometry/' . $physicsGeometry->lesson_cover_image);
                return $physicsGeometry;
            });

            $response = [
                'success' => true,
                'message' => 'Physics Geometry retrieved successfully.',
                'data' => $physicsGeometryData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get receivers
     *
     * @return response()
     */
    public function getAllLessonReceivers()
    {
        $receiversData = LessonReceivers::orderBy('id', 'DESC')->get();

        if ($receiversData->isNotEmpty()) {
            $receiversData->transform(function ($receivers) {
                $receivers->lesson_cover_image = asset('cms_images/lesson/receivers/' . $receivers->lesson_cover_image);
                return $receivers;
            });

            $response = [
                'success' => true,
                'message' => 'Receivers retrieved successfully.',
                'data' => $receiversData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get slicing
     *
     * @return response()
     */
    public function getAllLessonSlicing()
    {
        $slicingData = LessonSlicing::orderBy('id', 'DESC')->get();

        if ($slicingData->isNotEmpty()) {
            $slicingData->transform(function ($slicing) {
                $slicing->lesson_cover_image = asset('cms_images/lesson/slicing/' . $slicing->lesson_cover_image);
                return $slicing;
            });

            $response = [
                'success' => true,
                'message' => 'Slicing retrieved successfully.',
                'data' => $slicingData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get soldering
     *
     * @return response()
     */
    public function getAllLessonSoldering()
    {
        $solderingData = LessonSoldering::orderBy('id', 'DESC')->get();

        if ($solderingData->isNotEmpty()) {
            $solderingData->transform(function ($soldering) {
                $soldering->lesson_cover_image = asset('cms_images/lesson/soldering/' . $soldering->lesson_cover_image);
                return $soldering;
            });

            $response = [
                'success' => true,
                'message' => 'Soldering retrieved successfully.',
                'data' => $solderingData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get thinkercad
     *
     * @return response()
     */
    public function getAllLessonThinkercad()
    {
        $thinkercadData = LessonThinkercad::orderBy('id', 'DESC')->get();

        if ($thinkercadData->isNotEmpty()) {
            $thinkercadData->transform(function ($thinkercad) {
                $thinkercad->lesson_cover_image = asset('cms_images/lesson/thinkercad/' . $thinkercad->lesson_cover_image);
                return $thinkercad;
            });

            $response = [
                'success' => true,
                'message' => 'Thinkercad retrieved successfully.',
                'data' => $thinkercadData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get weapon physics
     *
     * @return response()
     */
    public function getAllLessonWeaponPhysics()
    {
        $weaponPhysicsData = LessonWeaponPhysics::orderBy('id', 'DESC')->get();

        if ($weaponPhysicsData->isNotEmpty()) {
            $weaponPhysicsData->transform(function ($weaponPhysics) {
                $weaponPhysics->lesson_cover_image = asset('cms_images/lesson/weapon_physics/' . $weaponPhysics->lesson_cover_image);
                return $weaponPhysics;
            });

            $response = [
                'success' => true,
                'message' => 'Weapon Physics retrieved successfully.',
                'data' => $weaponPhysicsData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get antweights
     *
     * @return response()
     */
    public function getAllAntweight()
    {
        $antweightData = WeightAntweight::orderBy('id', 'DESC')->get();

        if ($antweightData->isNotEmpty()) {
            $antweightData->transform(function ($antweight) {
                $antweight->weight_class_image = asset('cms_images/weight/antweights/' . $antweight->weight_class_image);
                return $antweight;
            });

            $response = [
                'success' => true,
                'message' => 'Antweight retrieved successfully.',
                'data' => $antweightData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get beetleweights
     *
     * @return response()
     */
    public function getAllBeetleweight()
    {
        $beetleweightData = WeightBeetleweight::orderBy('id', 'DESC')->get();

        if ($beetleweightData->isNotEmpty()) {
            $beetleweightData->transform(function ($beetleweight) {
                $beetleweight->weight_class_image = asset('cms_images/weight/beetleweights/' . $beetleweight->weight_class_image);
                return $beetleweight;
            });

            $response = [
                'success' => true,
                'message' => 'Beetleweight retrieved successfully.',
                'data' => $beetleweightData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get fairyweights
     *
     * @return response()
     */
    public function getAllFairyweight()
    {
        $fairyweightData = WeightFairyweight::orderBy('id', 'DESC')->get();

        if ($fairyweightData->isNotEmpty()) {
            $fairyweightData->transform(function ($fairyweight) {
                $fairyweight->weight_class_image = asset('cms_images/weight/fairyweights/' . $fairyweight->weight_class_image);
                return $fairyweight;
            });

            $response = [
                'success' => true,
                'message' => 'Fairyweight retrieved successfully.',
                'data' => $fairyweightData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get featherweights
     *
     * @return response()
     */
    public function getAllFeatherweight()
    {
        $featherweightData = WeightFeatherweight::orderBy('id', 'DESC')->get();

        if ($featherweightData->isNotEmpty()) {
            $featherweightData->transform(function ($featherweight) {
                $featherweight->weight_class_image = asset('cms_images/weight/featherweights/' . $featherweight->weight_class_image);
                return $featherweight;
            });

            $response = [
                'success' => true,
                'message' => 'Featherweight retrieved successfully.',
                'data' => $featherweightData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get hobbyweights
     *
     * @return response()
     */
    public function getAllHobbyweight()
    {
        $hobbyweightData = WeightHobbyweight::orderBy('id', 'DESC')->get();

        if ($hobbyweightData->isNotEmpty()) {
            $hobbyweightData->transform(function ($hobbyweight) {
                $hobbyweight->weight_class_image = asset('cms_images/weight/hobbyweights/' . $hobbyweight->weight_class_image);
                return $hobbyweight;
            });

            $response = [
                'success' => true,
                'message' => 'Hobbyweight retrieved successfully.',
                'data' => $hobbyweightData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get plastic antweights
     *
     * @return response()
     */
    public function getAllPlasticAntweight()
    {
        $plasticAntweightData = WeightPlasticAntweight::orderBy('id', 'DESC')->get();

        if ($plasticAntweightData->isNotEmpty()) {
            $plasticAntweightData->transform(function ($plasticAntweight) {
                $plasticAntweight->weight_class_image = asset('cms_images/weight/plastic_antweights/' . $plasticAntweight->weight_class_image);
                return $plasticAntweight;
            });

            $response = [
                'success' => true,
                'message' => 'Plastic Antweight retrieved successfully.',
                'data' => $plasticAntweightData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for get sportsmans
     *
     * @return response()
     */
    public function getAllSportsman()
    {
        $sportsmanData = WeightSportsman::orderBy('id', 'DESC')->get();

        if ($sportsmanData->isNotEmpty()) {
            $sportsmanData->transform(function ($sportsman) {
                $sportsman->weight_class_image = asset('cms_images/weight/sportsmans/' . $sportsman->weight_class_image);
                return $sportsman;
            });

            $response = [
                'success' => true,
                'message' => 'Sportsman retrieved successfully.',
                'data' => $sportsmanData,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    /**
     * Write code on this method for save contact details
     *
     * @return response()
     */
    public function contactUs(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:150',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        // Prepare data for insertion
        $contactData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
        ];

        $inserted = DB::table('contact_us')->insert($contactData);

        // Check if the record was successfully inserted
        if ($inserted) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for contacting us!',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save your message. Please try again later.',
            ], 500);
        }
    }

    /**
     * Write code on this method for save visitor email
     *
     * @return response()
     */
    public function saveVisitorEmail(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email:rfc,dns|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));

        $ipAddress = $request->ip();
        $userAgent = $request->header('User-Agent');

        $visitor = VisitorEmail::where('email', $email)->first();

        if ($visitor) {

            VisitorEmail::where('id', $visitor->id)
                ->update([
                    'visit_count'     => $visitor->visit_count + 1,
                    'last_visited_at' => now(),
                    'ip_address'      => $ipAddress,
                    'user_agent'      => $userAgent,
                    'updated_at'      => now(),
                ]);

            return response()->json([
                'success' => true,
                'already_exists' => true,
                'message' => 'This email is already registered. Thank you for your continued interest!',
            ], 200);
        }

        VisitorEmail::create([
            'email'            => $email,
            'visit_count'      => 1,
            'first_visited_at' => now(),
            'last_visited_at'  => now(),
            'ip_address'       => $ipAddress,
            'user_agent'       => $userAgent,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return response()->json([
            'success' => true,
            'already_exists' => false,
            'message' => 'Email saved successfully.',
        ], 200);
    }

    /**
     * Write code on this method for get all data
     *
     * @return response()
     */
    public function getAllBattleBots()
    {
        $data = BattleBot::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Battle Bots retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllControlBots()
    {
        $data = ControlBot::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Control Bots retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllDrumSpinners()
    {
        $data = DrumSpinner::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Drum Spinners retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllFireBots()
    {
        $data = FireBot::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Fire Bots retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllFlipperBots()
    {
        $data = FlipperBot::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Flipper Bots retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllGearboxes()
    {
        $data = Gearbox::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Gearboxes retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllHammerBots()
    {
        $data = HammerBot::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Hammer Bots retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllHandTools()
    {
        $data = HandTool::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Hand Tools retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllHorizontalSpinners()
    {
        $data = HorizontalSpinner::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Horizontal Spinners retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllModesOfTransferringMotion()
    {
        $data = ModesOfTransferringMotion::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Modes of Transferring Motion retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllNhrl()
    {
        $data = Nhrl::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'NHRL retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllOverheadSaws()
    {
        $data = OverheadSaw::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Overhead Saws retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllPlants()
    {
        $data = Plant::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Plants retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllPowerTools()
    {
        $data = PowerTool::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Power Tools retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllPrintedCircuitBoards()
    {
        $data = PrintedCircuitBoard::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Printed Circuit Boards retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllRcl()
    {
        $data = Rcl::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'RCL retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllSsp()
    {
        $data = Ssp::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'SSP retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllTurnabots()
    {
        $data = TurnaBot::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Turnabots retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllTypesOfDriveSystems()
    {
        $data = TypesOfDriveSystem::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Types of Drive Systems retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllVerticalSpinners()
    {
        $data = VerticalSpinner::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Vertical Spinners retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllDriveSystems()
    {
        $data = DriveSystem::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Drive Systems retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllWeaponsSystems()
    {
        $data = WeaponsSystem::orderBy('id', 'DESC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Drive Systems retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllEmbeds()
    {
        $data = Embed::orderBy('title', 'ASC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Embeded data retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getAllmedia()
    {
        $data = MediaContent::orderBy('title', 'ASC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Media Content retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    public function getUnitCategories()
    {
        $data = Category::orderBy('category_name', 'ASC')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Unit Category data retrieved successfully.',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found!',
            ], 200);
        }
    }

    // public function getAllCurriculums()
    // {
    //     $curriculums = Curriculum::with('category')
    //         ->orderBy('category_id', 'ASC')
    //         ->orderBy('id', 'DESC')
    //         ->get();

    //     if ($curriculums->isNotEmpty()) {

    //         $curriculums->transform(function ($row) {

    //             if ($row->type === 'pdf') {
    //                 $row->download_url = route('curriculum.pdf.download', $row->id);
    //             } else {
    //                 $row->embed_url = $row->embed_link;
    //             }

    //             unset($row->embed_link);

    //             return $row;
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Curriculum data retrieved successfully.',
    //             'data'    => $curriculums,
    //         ], 200);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => 'No data found!',
    //     ], 200);
    // }

    public function getAllCurriculums()
    {
        $curriculums = Curriculum::query()
            ->join('categories', 'categories.id', '=', 'cms_curriculums.category_id')
            ->orderBy('categories.category_name', 'ASC')
            ->orderBy('cms_curriculums.sequence', 'ASC')
            ->select('cms_curriculums.*')
            ->with('category')
            ->get();

        if ($curriculums->isNotEmpty()) {

            $curriculums->transform(function ($row) {

                if ($row->type === 'pdf') {
                    $row->download_url = route('curriculum.pdf.download', $row->id);
                } else {
                    $row->embed_url = $row->embed_link;
                }

                unset($row->embed_link);

                return $row;
            });

            return response()->json([
                'success' => true,
                'message' => 'Curriculum data retrieved successfully.',
                'data'    => $curriculums,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'No data found!',
        ], 200);
    }

    public function downloadPdf($id)
    {
        $curriculum = Curriculum::findOrFail($id);

        // Block non-PDF downloads
        if ($curriculum->type !== 'pdf') {
            abort(403, 'Download not allowed');
        }

        $filePath = public_path('uploads/curriculum_pdfs/' . $curriculum->embed_link);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download(
            $filePath,
            $curriculum->embed_link,
            ['Content-Type' => 'application/pdf']
        );
    }


    // public function getAllCurriculumAssignments()
    // {
    //     // Get all assignments once
    //     $assignments = GoogleAssignment::orderBy('id', 'DESC')->get();

    //     // Get all categories
    //     $categories = Category::orderBy('id', 'ASC')->get();

    //     if ($categories->isEmpty()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'No data found!',
    //         ], 200);
    //     }

    //     $formatted = $categories->map(function ($category) use ($assignments) {

    //         // Get curriculums for this category
    //         $curriculums = Curriculum::where('category_id', $category->id)
    //             ->orderBy('id', 'DESC')
    //             ->get();

    //         $curriculumList = $curriculums->map(function ($item) use ($assignments) {

    //             // Find assignment mapped with this curriculum
    //             $assignment = $assignments->first(function ($a) use ($item) {
    //                 if (!$a->curriculum_ids) return false;
    //                 $currIds = json_decode($a->curriculum_ids, true);
    //                 return is_array($currIds) && in_array($item->id, $currIds);
    //             });

    //             return [
    //                 'id' => $item->id,
    //                 'title' => $item->title,
    //                 'embed_link' => $item->embed_link,
    //                 'file_type' => $item->file_type,
    //                 'assignment' => $assignment ? [
    //                     'id' => $assignment->id,
    //                     'assignment_id' => $assignment->assignment_id,
    //                     'title' => $assignment->title,
    //                     'max_points' => $assignment->max_points,
    //                     'curriculum_ids' => json_decode($assignment->curriculum_ids, true) ?? [],
    //                     'attachment_link' => json_decode($assignment->attachment_link, true) ?? [],
    //                     'course_id' => $assignment->course_id,
    //                     'due_date' => $assignment->due_date,
    //                     'due_time' => $assignment->due_time,
    //                 ] : null,
    //                 'created_at' => $item->created_at,
    //                 'updated_at' => $item->updated_at,
    //             ];
    //         });

    //         return [
    //             'category_id' => $category->id,
    //             'category_name' => $category->category_name,
    //             'curriculums' => $curriculumList,
    //         ];
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Curriculum list grouped by category fetched successfully.',
    //         'data' => $formatted,
    //     ], 200);
    // }

    public function updateCurriculumSequence(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:cms_curriculums,id',
            'items.*.sequence' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            Curriculum::where('id', $item['id'])
                ->update(['sequence' => $item['sequence']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Curriculum order updated successfully.',
        ], 200);
    }

    public function teacherDashboardData()
    {
        $data = TeacherDashboardContent::all();
        return response()->json([
            'success' => true,
            'message' => 'Teacher dashboard data fetched successfully.',
            'data' => $data,
        ], 200);
    }
}
