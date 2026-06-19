<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CMS\BattleBotController;
use App\Http\Controllers\Admin\CMS\EventCoverageController;
use App\Http\Controllers\Admin\CMS\BotController;
use App\Http\Controllers\Admin\CMS\ControlBotController;
use App\Http\Controllers\Admin\CMS\DriveSystemController;
use App\Http\Controllers\Admin\CMS\DrumSpinnerController;
use App\Http\Controllers\Admin\CMS\EmbedController;
use App\Http\Controllers\Admin\CMS\TeacherDashboardContentController;
use App\Http\Controllers\Admin\CMS\FireBotController;
use App\Http\Controllers\Admin\CMS\FlipperBotController;
use App\Http\Controllers\Admin\CMS\GearboxController;
use App\Http\Controllers\Admin\CMS\HammerBotController;
use App\Http\Controllers\Admin\CMS\HandToolController;
use App\Http\Controllers\Admin\CMS\HorizontalSpinnerController;
use App\Http\Controllers\Admin\CMS\Lesson3dModelingController;
use App\Http\Controllers\Admin\CMS\Lesson3dPrintingController;
use App\Http\Controllers\Admin\CMS\LessonBatteriesController;
use App\Http\Controllers\Admin\CMS\LessonBescsController;
use App\Http\Controllers\Admin\CMS\LessonBrushedBrushlessController;
use App\Http\Controllers\Admin\CMS\LessonElectricalEngineeringController;
use App\Http\Controllers\Admin\CMS\LessonFusionController;
use App\Http\Controllers\Admin\CMS\LessonGearRatiosController;
use App\Http\Controllers\Admin\CMS\LessonMaterialScienceController;
use App\Http\Controllers\Admin\CMS\LessonPcbsController;
use App\Http\Controllers\Admin\CMS\LessonPhysicsGeometryController;
use App\Http\Controllers\Admin\CMS\LessonReceiversController;
use App\Http\Controllers\Admin\CMS\LessonSlicingController;
use App\Http\Controllers\Admin\CMS\LessonSolderingController;
use App\Http\Controllers\Admin\CMS\LessonThinkercadController;
use App\Http\Controllers\Admin\CMS\LessonWeaponPhysicsController;
use App\Http\Controllers\Admin\CMS\PartnerController;
use App\Http\Controllers\Admin\CMS\PresentationController;
use App\Http\Controllers\Admin\CMS\ServiceController;
use App\Http\Controllers\Admin\CMS\LeagueRuleController;
use App\Http\Controllers\Admin\CMS\TransferringMotionController;
use App\Http\Controllers\Admin\CMS\NhrlController;
use App\Http\Controllers\Admin\CMS\OverheadSawController;
use App\Http\Controllers\Admin\CMS\PlantController;
use App\Http\Controllers\Admin\CMS\PowerToolController;
use App\Http\Controllers\Admin\CMS\PrintedCircuitBoardController;
use App\Http\Controllers\Admin\CMS\RclController;
use App\Http\Controllers\Admin\CMS\SspController;
use App\Http\Controllers\Admin\CMS\SubscriptionController;
use App\Http\Controllers\Admin\CMS\ToolsTradeController;
use App\Http\Controllers\Admin\CMS\TournamentController;
use App\Http\Controllers\Admin\CMS\TurnaBotController;
use App\Http\Controllers\Admin\CMS\TypesOfDriveSystemController;
use App\Http\Controllers\Admin\CMS\VerticalSpinnerController;
use App\Http\Controllers\Admin\CMS\WeaponsSystemController;
use App\Http\Controllers\Admin\CMS\WeightAntweightController;
use App\Http\Controllers\Admin\CMS\WeightBeetleweightController;
use App\Http\Controllers\Admin\CMS\WeightClassController;
use App\Http\Controllers\Admin\CMS\WeightFairyweightController;
use App\Http\Controllers\Admin\CMS\WeightFeatherweightController;
use App\Http\Controllers\Admin\CMS\WeightHobbyweightController;
use App\Http\Controllers\Admin\CMS\WeightPlasticAntweightController;
use App\Http\Controllers\Admin\CMS\WeightSportsmanController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitorController;
use App\Http\Controllers\Admin\ContentManagementController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\MediaContentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\GoogleAnalyticsAuthController;
use Google\Api\Visibility;
use Google\Service\Classroom\Teacher;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});

// Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::controller(StripePaymentController::class)->group(function () {
    Route::get('stripe', 'stripe');
    Route::post('stripe', 'stripePost')->name('stripe.post');
});

// ============== Admin Routes =========== ----code by krishn starts----
Route::post('/admin-login', [AdminController::class, 'login'])->name('admin-login');
Route::post('/admin-login-submit',  [AdminController::class, 'adminLoginSubmit']);
Route::get('/admin-logout', [AdminController::class, 'logout']);

Route::group(['middleware' => ['admin']], function () {
    Route::controller(AdminController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::post('/logout', 'logout')->name('logout');
        Route::get('/get-profile', 'getProfile')->name('get-profile');
        Route::get('/edit-profile', 'editProfile')->name('edit-profile');
        Route::post('/update-profile', 'updateProfile')->name('update-profile');
        Route::get('/change-password', 'changePassword')->name('change-password');
        Route::post('/update-password', 'updatePassword')->name('update-password');
    });

    Route::controller(GoogleAnalyticsAuthController::class)->group(function () {
        Route::get('/google/analytics/connect', 'connect')->name('admin.google.analytics.connect');
        Route::get('/google/analytics/callback', 'callback')->name('admin.google.analytics.callback');
    });

    // GA4 Analytics dashboard
    Route::controller(AnalyticsController::class)->group(function () {
        Route::get('/analytics', 'index')->name('admin.analytics');
        Route::get('/analytics/data', 'data')->name('admin.analytics.data');
    });

    // // route for products
    // Route::controller(ProductController::class)->group(function () {
    //     Route::get('/list-product', 'index');
    //     Route::get('/add-product', 'create');
    //     Route::get('/edit-product/{id}', 'edit');
    //     Route::post('/save-product', 'store');
    //     Route::post('/update-product', 'update');
    //     Route::post('/delete-product', 'destroy');
    //     Route::get('/subcategories', 'getSubCategory');
    // });

    // // route for product category
    // Route::controller(CategoryController::class)->group(function () {
    //     Route::get('/category-list', 'index');
    //     Route::get('/category-add', 'create');
    //     Route::get('/category-edit/{id}', 'edit');
    //     Route::post('/category-save', 'store');
    //     Route::post('/category-update', 'update');
    //     Route::post('/category-delete', 'destroy');
    // });

    // // route for product sub category
    // Route::controller(SubCategoryController::class)->group(function () {
    //     Route::get('/list-sub-category', 'index');
    //     Route::get('/add-sub-category', 'create');
    //     Route::get('/edit-sub-category/{id}', 'edit');
    //     Route::post('/save-sub-category', 'store');
    //     Route::post('/update-sub-category', 'update');
    //     Route::post('/delete-sub-category', 'destroy');
    // });

    // route for All users
    Route::prefix('users')->group(function () {

        // route for users
        Route::controller(UserController::class)->group(function () {
            Route::get('/list-student', 'index');
            Route::get('/add-student', 'create');
            Route::get('/edit-student/{id}', 'edit');
            Route::post('/save-student', 'store');
            Route::post('/update-student', 'update');
            Route::post('/delete-student', 'destroy');
        });

        // route for teachers
        Route::controller(TeacherController::class)->group(function () {
            Route::get('/list-teacher', 'index');
            Route::get('/add-teacher', 'create');
            Route::get('/edit-teacher/{id}', 'edit');
            Route::post('/save-teacher', 'store');
            Route::post('/update-teacher', 'update');
            Route::post('/delete-teacher', 'destroy');
        });

        // route for team members
        Route::controller(TeamMemberController::class)->group(function () {
            Route::get('/list-member', 'index');
            Route::get('/add-member', 'create');
            Route::get('/edit-member/{id}', 'edit');
            Route::post('/save-member', 'store');
            Route::post('/update-member', 'update');
            Route::post('/delete-member', 'destroy');
        });

        Route::controller(VisitorController::class)->group(function () {
            Route::get('/list-visitor', 'index');
        });
    });

    // route for All curriculums
    Route::prefix('curriculums')->group(function () {

        // route for unit categories
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/category-list', 'index');
            Route::get('/category-add', 'create');
            Route::get('/category-edit/{id}', 'edit');
            Route::post('/category-save', 'store');
            Route::post('/category-update', 'update');
            Route::post('/category-delete', 'destroy');
        });

        Route::controller(ContentManagementController::class)->group(function () {
            // route for curriculum content page
            Route::get('/overview', 'editCurriculumContent');
            Route::post('/update-overview', 'updateCurriculumContent');
            Route::post('/curriculum-content-image', 'uploadCkEditorImageForCurriculum')->name('curriculum.content.image');
        });

        // route for curriculum units
        Route::controller(CurriculumController::class)->group(function () {
            Route::get('/unit-list', 'index');
            Route::get('/unit-add', 'create');
            Route::post('/unit-save', 'store');
            Route::get('/unit-edit/{id}', 'edit');
            Route::post('/unit-update', 'update');
            Route::post('/unit-delete', 'destroy');
            Route::post('/update-sequence', 'updateCurriculumSequence');
        });
    });

    // route for bots
    Route::controller(BotController::class)->group(function () {
        Route::get('/bot-list', 'index');
        Route::get('/bot-add', 'create');
        Route::post('/bot-save', 'store');
        Route::get('/bot-edit/{id}', 'edit');
        Route::post('/bot-update', 'update');
        Route::post('/bot-delete', 'destroy');
    });

    // route for partners(companies)
    Route::controller(PartnerController::class)->group(function () {
        Route::get('/partner-list', 'index');
        Route::get('/partner-add', 'create');
        Route::post('/partner-save', 'store');
        Route::get('/partner-edit/{id}', 'edit');
        Route::post('/partner-update', 'update');
        Route::post('/partner-delete', 'destroy');
    });

    // route for subscriptions
    Route::controller(SubscriptionController::class)->group(function () {
        Route::get('/subscription-list', 'index');
        Route::get('/subscription-add', 'create');
        Route::post('/subscription-save', 'store');
        Route::get('/subscription-edit/{id}', 'edit');
        Route::post('/subscription-update', 'update');
        Route::post('/subscription-delete', 'destroy');
    });

    // route for Content Management
    Route::prefix('cms')->group(function () {

        // route for embeds
        Route::controller(EmbedController::class)->group(function () {
            Route::get('/embeds-list', 'index');
            Route::get('/embeds-add', 'create');
            Route::post('/embeds-save', 'store');
            Route::get('/embeds-edit/{id}', 'edit');
            Route::post('/embeds-update', 'update');
            Route::post('/embeds-delete', 'destroy');
        });

        // route for teacher's dashboard information
        Route::controller(TeacherDashboardContentController::class)->group(function () {
            Route::get('/dashboard-content-list', 'index');
            Route::get('/dashboard-content-add', 'create');
            Route::post('/dashboard-content-save', 'store');
            Route::get('/dashboard-content-edit/{id}', 'edit');
            Route::post('/dashboard-content-update', 'update');
            Route::post('/dashboard-content-delete', 'destroy');
        });

        // route for tournaments
        Route::controller(TournamentController::class)->group(function () {
            Route::get('/tournament-list', 'index');
            Route::get('/tournament-add', 'create');
            Route::post('/tournament-save', 'store');
            Route::get('/tournament-edit/{id}', 'edit');
            Route::post('/tournament-update', 'update');
            Route::post('/tournament-delete', 'destroy');
        });

        // route for presentations
        Route::controller(PresentationController::class)->group(function () {
            Route::get('/presentation-list', 'index');
            Route::get('/presentation-add', 'create');
            Route::post('/presentation-save', 'store');
            Route::get('/presentation-edit/{id}', 'edit');
            Route::post('/presentation-update', 'update');
            Route::post('/presentation-delete', 'destroy');
        });

        // route for weight classes/restrictions
        Route::controller(WeightClassController::class)->group(function () {
            Route::get('/weight-class-list', 'index');
            Route::get('/weight-class-add', 'create');
            Route::post('/weight-class-save', 'store');
            Route::get('/weight-class-edit/{id}', 'edit');
            Route::post('/weight-class-update', 'update');
            Route::post('/weight-class-delete', 'destroy');
        });

        // route for services
        Route::controller(ServiceController::class)->group(function () {
            Route::get('/service-list', 'index');
            Route::get('/service-add', 'create');
            Route::post('/service-save', 'store');
            Route::get('/service-edit/{id}', 'edit');
            Route::post('/service-update', 'update');
            Route::post('/service-delete', 'destroy');
        });

        // route for League Rules
        Route::controller(LeagueRuleController::class)->group(function () {
            Route::get('/league-rule-list', 'index');
            Route::get('/league-rule-add', 'create');
            Route::post('/league-rule-save', 'store');
            Route::get('/league-rule-edit/{id}', 'edit');
            Route::post('/league-rule-update', 'update');
            Route::post('/league-rule-delete', 'destroy');
        });

        // route for Event Coverage/Results
        Route::controller(EventCoverageController::class)->group(function () {
            Route::get('/event-coverage-list', 'index');
            Route::get('/event-coverage-add', 'create');
            Route::post('/event-coverage-save', 'store');
            Route::get('/event-coverage-edit/{id}', 'edit');
            Route::post('/event-coverage-update', 'update');
            Route::post('/event-coverage-delete', 'destroy');
        });

        // route for Media Contents
        Route::controller(MediaContentController::class)->group(function () {
            Route::get('/media-content-list', 'index');
            Route::get('/media-content-add', 'create');
            Route::post('/media-content-save', 'store');
            Route::get('/media-content-edit/{id}', 'edit');
            Route::post('/media-content-update', 'update');
            Route::post('/media-content-delete', 'destroy');
        });

        // route for Tools of the Trade
        Route::controller(ToolsTradeController::class)->group(function () {
            Route::get('/tools-trade-list', 'index');
            Route::get('/tools-trade-add', 'create');
            Route::post('/tools-trade-save', 'store');
            Route::get('/tools-trade-edit/{id}', 'edit');
            Route::post('/tools-trade-update', 'update');
            Route::post('/tools-trade-delete', 'destroy');
        });

        // Routes for all knowledgebases
        Route::prefix('knowledgebases')->group(function () {

            // Routes for CMS battlebots
            Route::controller(BattleBotController::class)->group(function () {
                Route::get('/battlebot-list', 'index');
                Route::get('/battlebot-add', 'create');
                Route::post('/battlebot-save', 'store');
                Route::get('/battlebot-edit/{id}', 'edit');
                Route::post('/battlebot-update', 'update');
                Route::post('/battlebot-delete', 'destroy');
            });

            // Routes for CMS controlbots
            Route::controller(ControlBotController::class)->group(function () {
                Route::get('/controlbot-list', 'index');
                Route::get('/controlbot-add', 'create');
                Route::post('/controlbot-save', 'store');
                Route::get('/controlbot-edit/{id}', 'edit');
                Route::post('/controlbot-update', 'update');
                Route::post('/controlbot-delete', 'destroy');
            });

            // Routes for CMS drum spinners
            Route::controller(DrumSpinnerController::class)->group(function () {
                Route::get('/drum-spinner-list', 'index');
                Route::get('/drum-spinner-add', 'create');
                Route::post('/drum-spinner-save', 'store');
                Route::get('/drum-spinner-edit/{id}', 'edit');
                Route::post('/drum-spinner-update', 'update');
                Route::post('/drum-spinner-delete', 'destroy');
            });

            // Routes for CMS firebots
            Route::controller(FireBotController::class)->group(function () {
                Route::get('/firebot-list', 'index');
                Route::get('/firebot-add', 'create');
                Route::post('/firebot-save', 'store');
                Route::get('/firebot-edit/{id}', 'edit');
                Route::post('/firebot-update', 'update');
                Route::post('/firebot-delete', 'destroy');
            });

            // Routes for CMS flipper bots
            Route::controller(FlipperBotController::class)->group(function () {
                Route::get('/flipperbot-list', 'index');
                Route::get('/flipperbot-add', 'create');
                Route::post('/flipperbot-save', 'store');
                Route::get('/flipperbot-edit/{id}', 'edit');
                Route::post('/flipperbot-update', 'update');
                Route::post('/flipperbot-delete', 'destroy');
            });

            // Routes for CMS gearboxes
            Route::controller(GearboxController::class)->group(function () {
                Route::get('/gearbox-list', 'index');
                Route::get('/gearbox-add', 'create');
                Route::post('/gearbox-save', 'store');
                Route::get('/gearbox-edit/{id}', 'edit');
                Route::post('/gearbox-update', 'update');
                Route::post('/gearbox-delete', 'destroy');
            });

            // Routes for CMS hammerbots
            Route::controller(HammerBotController::class)->group(function () {
                Route::get('/hammerbot-list', 'index');
                Route::get('/hammerbot-add', 'create');
                Route::post('/hammerbot-save', 'store');
                Route::get('/hammerbot-edit/{id}', 'edit');
                Route::post('/hammerbot-update', 'update');
                Route::post('/hammerbot-delete', 'destroy');
            });

            // Routes for CMS hand tools
            Route::controller(HandToolController::class)->group(function () {
                Route::get('/hand-tool-list', 'index');
                Route::get('/hand-tool-add', 'create');
                Route::post('/hand-tool-save', 'store');
                Route::get('/hand-tool-edit/{id}', 'edit');
                Route::post('/hand-tool-update', 'update');
                Route::post('/hand-tool-delete', 'destroy');
            });

            // Routes for CMS horizontal spinners
            Route::controller(HorizontalSpinnerController::class)->group(function () {
                Route::get('/horizontal-spinner-list', 'index');
                Route::get('/horizontal-spinner-add', 'create');
                Route::post('/horizontal-spinner-save', 'store');
                Route::get('/horizontal-spinner-edit/{id}', 'edit');
                Route::post('/horizontal-spinner-update', 'update');
                Route::post('/horizontal-spinner-delete', 'destroy');
            });

            // Routes for CMS modes of transferring motion
            Route::controller(TransferringMotionController::class)->group(function () {
                Route::get('/transferring-motion-list', 'index');
                Route::get('/transferring-motion-add', 'create');
                Route::post('/transferring-motion-save', 'store');
                Route::get('/transferring-motion-edit/{id}', 'edit');
                Route::post('/transferring-motion-update', 'update');
                Route::post('/transferring-motion-delete', 'destroy');
            });

            // Routes for CMS nhrl
            Route::controller(NhrlController::class)->group(function () {
                Route::get('/nhrl-list', 'index');
                Route::get('/nhrl-add', 'create');
                Route::post('/nhrl-save', 'store');
                Route::get('/nhrl-edit/{id}', 'edit');
                Route::post('/nhrl-update', 'update');
                Route::post('/nhrl-delete', 'destroy');
            });

            // Routes for CMS overhead saws
            Route::controller(OverheadSawController::class)->group(function () {
                Route::get('/overhead-saw-list', 'index');
                Route::get('/overhead-saw-add', 'create');
                Route::post('/overhead-saw-save', 'store');
                Route::get('/overhead-saw-edit/{id}', 'edit');
                Route::post('/overhead-saw-update', 'update');
                Route::post('/overhead-saw-delete', 'destroy');
            });

            // Routes for CMS plants
            Route::controller(PlantController::class)->group(function () {
                Route::get('/plant-list', 'index');
                Route::get('/plant-add', 'create');
                Route::post('/plant-save', 'store');
                Route::get('/plant-edit/{id}', 'edit');
                Route::post('/plant-update', 'update');
                Route::post('/plant-delete', 'destroy');
            });

            // Routes for CMS power tools
            Route::controller(PowerToolController::class)->group(function () {
                Route::get('/power-tool-list', 'index');
                Route::get('/power-tool-add', 'create');
                Route::post('/power-tool-save', 'store');
                Route::get('/power-tool-edit/{id}', 'edit');
                Route::post('/power-tool-update', 'update');
                Route::post('/power-tool-delete', 'destroy');
            });

            // Routes for CMS printed circuit boards
            Route::controller(PrintedCircuitBoardController::class)->group(function () {
                Route::get('/printed-circuit-board-list', 'index');
                Route::get('/printed-circuit-board-add', 'create');
                Route::post('/printed-circuit-board-save', 'store');
                Route::get('/printed-circuit-board-edit/{id}', 'edit');
                Route::post('/printed-circuit-board-update', 'update');
                Route::post('/printed-circuit-board-delete', 'destroy');
            });

            // Routes for CMS drum RCL
            Route::controller(RclController::class)->group(function () {
                Route::get('/rcl-list', 'index');
                Route::get('/rcl-add', 'create');
                Route::post('/rcl-save', 'store');
                Route::get('/rcl-edit/{id}', 'edit');
                Route::post('/rcl-update', 'update');
                Route::post('/rcl-delete', 'destroy');
            });

            // Routes for CMS drive system
            Route::controller(DriveSystemController::class)->group(function () {
                Route::get('/drive-system-list', 'index');
                Route::get('/drive-system-add', 'create');
                Route::post('/drive-system-save', 'store');
                Route::get('/drive-system-edit/{id}', 'edit');
                Route::post('/drive-system-update', 'update');
                Route::post('/drive-system-delete', 'destroy');
            });

            // Routes for CMS ssp
            Route::controller(SspController::class)->group(function () {
                Route::get('/ssp-list', 'index');
                Route::get('/ssp-add', 'create');
                Route::post('/ssp-save', 'store');
                Route::get('/ssp-edit/{id}', 'edit');
                Route::post('/ssp-update', 'update');
                Route::post('/ssp-delete', 'destroy');
            });

            // Routes for CMS turnabot
            Route::controller(TurnaBotController::class)->group(function () {
                Route::get('/turnabot-list', 'index');
                Route::get('/turnabot-add', 'create');
                Route::post('/turnabot-save', 'store');
                Route::get('/turnabot-edit/{id}', 'edit');
                Route::post('/turnabot-update', 'update');
                Route::post('/turnabot-delete', 'destroy');
            });

            // Routes for CMS type of drive system
            Route::controller(TypesOfDriveSystemController::class)->group(function () {
                Route::get('/type-of-drive-system-list', 'index');
                Route::get('/type-of-drive-system-add', 'create');
                Route::post('/type-of-drive-system-save', 'store');
                Route::get('/type-of-drive-system-edit/{id}', 'edit');
                Route::post('/type-of-drive-system-update', 'update');
                Route::post('/type-of-drive-system-delete', 'destroy');
            });

            // Routes for CMS vertical spinner
            Route::controller(VerticalSpinnerController::class)->group(function () {
                Route::get('/vertical-spinner-list', 'index');
                Route::get('/vertical-spinner-add', 'create');
                Route::post('/vertical-spinner-save', 'store');
                Route::get('/vertical-spinner-edit/{id}', 'edit');
                Route::post('/vertical-spinner-update', 'update');
                Route::post('/vertical-spinner-delete', 'destroy');
            });

            // Routes for CMS weapon system
            Route::controller(WeaponsSystemController::class)->group(function () {
                Route::get('/weapon-system-list', 'index');
                Route::get('/weapon-system-add', 'create');
                Route::post('/weapon-system-save', 'store');
                Route::get('/weapon-system-edit/{id}', 'edit');
                Route::post('/weapon-system-update', 'update');
                Route::post('/weapon-system-delete', 'destroy');
            });
        });

        // Routes for all lessons
        Route::prefix('lessons')->group(function () {

            // Routes for CMS Lesson 3D Modeling
            Route::controller(Lesson3dModelingController::class)->group(function () {
                Route::get('/3d-modeling-list', 'index');
                Route::get('/3d-modeling-add', 'create');
                Route::post('/3d-modeling-save', 'store');
                Route::get('/3d-modeling-edit/{id}', 'edit');
                Route::post('/3d-modeling-update', 'update');
                Route::post('/3d-modeling-delete', 'destroy');
            });

            // Routes for CMS Lesson 3D Printing
            Route::controller(Lesson3dPrintingController::class)->group(function () {
                Route::get('/3d-printing-list', 'index');
                Route::get('/3d-printing-add', 'create');
                Route::post('/3d-printing-save', 'store');
                Route::get('/3d-printing-edit/{id}', 'edit');
                Route::post('/3d-printing-update', 'update');
                Route::post('/3d-printing-delete', 'destroy');
            });

            // Routes for CMS Lesson Batteries
            Route::controller(LessonBatteriesController::class)->group(function () {
                Route::get('/batteries-list', 'index');
                Route::get('/batteries-add', 'create');
                Route::post('/batteries-save', 'store');
                Route::get('/batteries-edit/{id}', 'edit');
                Route::post('/batteries-update', 'update');
                Route::post('/batteries-delete', 'destroy');
            });

            // Routes for CMS Lesson BESCs
            Route::controller(LessonBescsController::class)->group(function () {
                Route::get('/bescs-list', 'index');
                Route::get('/bescs-add', 'create');
                Route::post('/bescs-save', 'store');
                Route::get('/bescs-edit/{id}', 'edit');
                Route::post('/bescs-update', 'update');
                Route::post('/bescs-delete', 'destroy');
            });

            // Routes for CMS Lesson Brushed and Brushless
            Route::controller(LessonBrushedBrushlessController::class)->group(function () {
                Route::get('/brushed-brushless-list', 'index');
                Route::get('/brushed-brushless-add', 'create');
                Route::post('/brushed-brushless-save', 'store');
                Route::get('/brushed-brushless-edit/{id}', 'edit');
                Route::post('/brushed-brushless-update', 'update');
                Route::post('/brushed-brushless-delete', 'destroy');
            });

            // Routes for CMS Lesson Electrical Engineering
            Route::controller(LessonElectricalEngineeringController::class)->group(function () {
                Route::get('/electrical-engineering-list', 'index');
                Route::get('/electrical-engineering-add', 'create');
                Route::post('/electrical-engineering-save', 'store');
                Route::get('/electrical-engineering-edit/{id}', 'edit');
                Route::post('/electrical-engineering-update', 'update');
                Route::post('/electrical-engineering-delete', 'destroy');
            });

            // Routes for CMS Lesson Fusion
            Route::controller(LessonFusionController::class)->group(function () {
                Route::get('/fusion-list', 'index');
                Route::get('/fusion-add', 'create');
                Route::post('/fusion-save', 'store');
                Route::get('/fusion-edit/{id}', 'edit');
                Route::post('/fusion-update', 'update');
                Route::post('/fusion-delete', 'destroy');
            });

            // Routes for CMS Lesson Gear Ratios
            Route::controller(LessonGearRatiosController::class)->group(function () {
                Route::get('/gear-ratios-list', 'index');
                Route::get('/gear-ratios-add', 'create');
                Route::post('/gear-ratios-save', 'store');
                Route::get('/gear-ratios-edit/{id}', 'edit');
                Route::post('/gear-ratios-update', 'update');
                Route::post('/gear-ratios-delete', 'destroy');
            });

            // Routes for CMS Lesson Material Science
            Route::controller(LessonMaterialScienceController::class)->group(function () {
                Route::get('/material-science-list', 'index');
                Route::get('/material-science-add', 'create');
                Route::post('/material-science-save', 'store');
                Route::get('/material-science-edit/{id}', 'edit');
                Route::post('/material-science-update', 'update');
                Route::post('/material-science-delete', 'destroy');
            });

            // Routes for CMS Lesson PCBs
            Route::controller(LessonPcbsController::class)->group(function () {
                Route::get('/pcbs-list', 'index');
                Route::get('/pcbs-add', 'create');
                Route::post('/pcbs-save', 'store');
                Route::get('/pcbs-edit/{id}', 'edit');
                Route::post('/pcbs-update', 'update');
                Route::post('/pcbs-delete', 'destroy');
            });

            // Routes for CMS Lesson Physics and Geometry
            Route::controller(LessonPhysicsGeometryController::class)->group(function () {
                Route::get('/physics-geometry-list', 'index');
                Route::get('/physics-geometry-add', 'create');
                Route::post('/physics-geometry-save', 'store');
                Route::get('/physics-geometry-edit/{id}', 'edit');
                Route::post('/physics-geometry-update', 'update');
                Route::post('/physics-geometry-delete', 'destroy');
            });

            // Routes for CMS Lesson Receivers
            Route::controller(LessonReceiversController::class)->group(function () {
                Route::get('/receivers-list', 'index');
                Route::get('/receivers-add', 'create');
                Route::post('/receivers-save', 'store');
                Route::get('/receivers-edit/{id}', 'edit');
                Route::post('/receivers-update', 'update');
                Route::post('/receivers-delete', 'destroy');
            });

            // Routes for CMS Lesson Slicing
            Route::controller(LessonSlicingController::class)->group(function () {
                Route::get('/slicing-list', 'index');
                Route::get('/slicing-add', 'create');
                Route::post('/slicing-save', 'store');
                Route::get('/slicing-edit/{id}', 'edit');
                Route::post('/slicing-update', 'update');
                Route::post('/slicing-delete', 'destroy');
            });

            // Routes for CMS Lesson Soldering
            Route::controller(LessonSolderingController::class)->group(function () {
                Route::get('/soldering-list', 'index');
                Route::get('/soldering-add', 'create');
                Route::post('/soldering-save', 'store');
                Route::get('/soldering-edit/{id}', 'edit');
                Route::post('/soldering-update', 'update');
                Route::post('/soldering-delete', 'destroy');
            });

            // Routes for CMS Lesson Thinkercad
            Route::controller(LessonThinkercadController::class)->group(function () {
                Route::get('/thinkercad-list', 'index');
                Route::get('/thinkercad-add', 'create');
                Route::post('/thinkercad-save', 'store');
                Route::get('/thinkercad-edit/{id}', 'edit');
                Route::post('/thinkercad-update', 'update');
                Route::post('/thinkercad-delete', 'destroy');
            });

            // Routes for CMS Lesson Weapon Physics
            Route::controller(LessonWeaponPhysicsController::class)->group(function () {
                Route::get('/weapon-physics-list', 'index');
                Route::get('/weapon-physics-add', 'create');
                Route::post('/weapon-physics-save', 'store');
                Route::get('/weapon-physics-edit/{id}', 'edit');
                Route::post('/weapon-physics-update', 'update');
                Route::post('/weapon-physics-delete', 'destroy');
            });
        });

        // Routes for all weight-classes
        Route::prefix('weight-classes')->group(function () {

            // Routes for CMS Weight Antweight
            Route::controller(WeightAntweightController::class)->group(function () {
                Route::get('/antweight-list', 'index');
                Route::get('/antweight-add', 'create');
                Route::post('/antweight-save', 'store');
                Route::get('/antweight-edit/{id}', 'edit');
                Route::post('/antweight-update', 'update');
                Route::post('/antweight-delete', 'destroy');
            });

            // Routes for CMS Weight Beetleweight
            Route::controller(WeightBeetleweightController::class)->group(function () {
                Route::get('/beetleweight-list', 'index');
                Route::get('/beetleweight-add', 'create');
                Route::post('/beetleweight-save', 'store');
                Route::get('/beetleweight-edit/{id}', 'edit');
                Route::post('/beetleweight-update', 'update');
                Route::post('/beetleweight-delete', 'destroy');
            });

            // Routes for CMS Weight Fairyweight
            Route::controller(WeightFairyweightController::class)->group(function () {
                Route::get('/fairyweight-list', 'index');
                Route::get('/fairyweight-add', 'create');
                Route::post('/fairyweight-save', 'store');
                Route::get('/fairyweight-edit/{id}', 'edit');
                Route::post('/fairyweight-update', 'update');
                Route::post('/fairyweight-delete', 'destroy');
            });

            // Routes for CMS Weight Featherweight
            Route::controller(WeightFeatherweightController::class)->group(function () {
                Route::get('/featherweight-list', 'index');
                Route::get('/featherweight-add', 'create');
                Route::post('/featherweight-save', 'store');
                Route::get('/featherweight-edit/{id}', 'edit');
                Route::post('/featherweight-update', 'update');
                Route::post('/featherweight-delete', 'destroy');
            });

            // Routes for CMS Weight Hobbyweight
            Route::controller(WeightHobbyweightController::class)->group(function () {
                Route::get('/hobbyweight-list', 'index');
                Route::get('/hobbyweight-add', 'create');
                Route::post('/hobbyweight-save', 'store');
                Route::get('/hobbyweight-edit/{id}', 'edit');
                Route::post('/hobbyweight-update', 'update');
                Route::post('/hobbyweight-delete', 'destroy');
            });

            // Routes for CMS Weight Plastic Antweight
            Route::controller(WeightPlasticAntweightController::class)->group(function () {
                Route::get('/plastic-antweight-list', 'index');
                Route::get('/plastic-antweight-add', 'create');
                Route::post('/plastic-antweight-save', 'store');
                Route::get('/plastic-antweight-edit/{id}', 'edit');
                Route::post('/plastic-antweight-update', 'update');
                Route::post('/plastic-antweight-delete', 'destroy');
            });

            // Routes for CMS Weight Sportsman
            Route::controller(WeightSportsmanController::class)->group(function () {
                Route::get('/sportsman-list', 'index');
                Route::get('/sportsman-add', 'create');
                Route::post('/sportsman-save', 'store');
                Route::get('/sportsman-edit/{id}', 'edit');
                Route::post('/sportsman-update', 'update');
                Route::post('/sportsman-delete', 'destroy');
            });
        });

        Route::controller(ContentManagementController::class)->group(function () {
            // route for home page
            Route::get('/home', 'editHome');
            Route::post('/update-home', 'updateHome');
            Route::post('/home-content-image', 'uploadCkEditorImageForHome')->name('home.content.image');

            // route for about page
            Route::get('/visitor-content', 'editVisitorContent');
            Route::post('/update-visitor-content', 'updateVisitorContent');

            // route for about page
            Route::get('/about', 'editAbout');
            Route::post('/update-about', 'updateAbout');
            Route::post('/aboutus-content-image', 'uploadCkEditorImageForAboutUs')->name('aboutus.content.image');

            // route for league page
            Route::get('/league', 'editLeague');
            Route::post('/update-league', 'updateLeague');

            // route for terms and conditions
            Route::get('/terms-and-conditions', 'editGlossaryTerm');
            Route::post('/update-terms-and-conditions', 'updateGlossaryTerm');

            // route for privacy policy
            Route::get('/privacy-policy', 'editPrivacyPolicy');
            Route::post('/update-privacy-policy', 'updatePrivacyPolicy');
        });
    });

    // Route::get('list-order', [OrderController::class, 'listOrder']);

    Route::controller(SubscriptionController::class)->group(function () {
        Route::get('subscriber-list', 'subscriberList');
        Route::post('update-end-date', 'updateEndDate');
        Route::post('update-user-access-count', 'updateUserAccessCount');
    });
});
