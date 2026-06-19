<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CmsHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentManagementController extends Controller
{
    // this method is used to view for home content banner
    public function editHome()
    {
        $data['homeBanner'] = DB::table('cms_home_page')->first();
        return view('admin.content_management.home', $data);
    }

    // this method is used to insert or update home content banner
    public function updateHome(Request $request)
    {
        $request->validate([
            'banner_title' => 'nullable|string|max:100',
            'banner_sub_title' => 'nullable|string|max:255',
            'banner_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'youtube_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $cmsHomeData = [
            'banner_title' => $request->input('banner_title', ''),
            'banner_sub_title' => $request->input('banner_sub_title', ''),
            'banner_text' => $request->input('banner_text', ''),
            'media_content' => $request->input('media_content', ''),
            'link' => $request->input('link', ''),
        ];

        $id = $request->id;

        if ($id) {
            $cmsHomeBanner = DB::table('cms_home_page')->find($id);

            if (!$cmsHomeBanner) {
                return redirect('cms/home')->with('error_msg', 'No record found with the provided ID.');
            }
        }

        // Handle banner_image
        if ($request->hasFile('banner_image')) {
            $banner_image = $request->file('banner_image');
            $bannerFileName = uniqid() . '.' . $banner_image->getClientOriginalExtension();
            $banner_image->move(public_path('cms_images'), $bannerFileName);

            // Delete previous banner_image if it exists
            if (isset($cmsHomeBanner->banner_image) && file_exists(public_path('cms_images/' . $cmsHomeBanner->banner_image))) {
                unlink(public_path('cms_images/' . $cmsHomeBanner->banner_image));
            }

            $cmsHomeData['banner_image'] = $bannerFileName;
        }

        // Handle youtube_image
        if ($request->hasFile('youtube_image')) {
            $youtube_image = $request->file('youtube_image');
            $youtubeFileName = uniqid() . '.' . $youtube_image->getClientOriginalExtension();
            $youtube_image->move(public_path('cms_images'), $youtubeFileName);

            // Delete previous youtube_image if it exists
            if (isset($cmsHomeBanner->youtube_image) && file_exists(public_path('cms_images/' . $cmsHomeBanner->youtube_image))) {
                unlink(public_path('cms_images/' . $cmsHomeBanner->youtube_image));
            }

            $cmsHomeData['youtube_image'] = $youtubeFileName;
        }

        if ($id) {
            DB::table('cms_home_page')->where('id', $id)->update($cmsHomeData);
            $message = 'Home banner section updated successfully!';
        } else {
            $id = DB::table('cms_home_page')->insertGetId($cmsHomeData);
            $message = 'New home banner section added successfully!';
        }
        
        $combinedContent =
            $cmsHomeData['banner_text'] . ' ' .
            $cmsHomeData['media_content'];

        CmsHelper::cleanupCkeditorImages($combinedContent);

        return redirect('cms/home')->with($id ? 'success_msg' : 'error_msg', $message);
    }

    public function uploadCkEditorImageForHome(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/ckeditor'), $filename);

            $url = asset('uploads/ckeditor/' . $filename);

            return response()->json([
                'uploaded' => true,
                'url' => $url
            ]);
        }

        return response()->json(['uploaded' => false, 'error' => ['message' => 'No file uploaded.']]);
    }

    // this method is used to view for about section
    public function editCurriculumContent()
    {
        $data['currriculumContent'] = DB::table('cms_curriculum_contents')->first();
        return view('admin.content_management.curriculum_content', $data);
    }

    // this method is used to insert or update curriculum content
    public function updateCurriculumContent(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required',
        ]);

        $cmsCurriculumContentData = [
            'title' => $request->input('title', ''),
            'description' => $request->input('description', ''),
        ];

        $id = $request->id;

        $cmsCurriculumContent = null;
        if ($id) {
            $cmsCurriculumContent = DB::table('cms_curriculum_contents')->find($id);

            if (!$cmsCurriculumContent) {
                return redirect('curriculums/overview')->with('error_msg', 'No record found with the provided ID.');
            }
        }

        // Insert or Update
        if ($id) {
            DB::table('cms_curriculum_contents')->where('id', $id)->update($cmsCurriculumContentData);
            $message = 'Curriculum overview updated successfully!';
        } else {
            $id = DB::table('cms_curriculum_contents')->insertGetId($cmsCurriculumContentData);
            $message = 'New curriculum overview added successfully!';
        }

        // Cleanup orphaned CKEditor images (after saving)
        CmsHelper::cleanupCkeditorImages($cmsCurriculumContentData['description']);

        return redirect('curriculums/overview')->with('success_msg', $message);
    }

    public function uploadCkEditorImageForCurriculum(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/ckeditor'), $filename);

            $url = asset('uploads/ckeditor/' . $filename);

            return response()->json([
                'uploaded' => true,
                'url' => $url
            ]);
        }

        return response()->json(['uploaded' => false, 'error' => ['message' => 'No file uploaded.']]);
    }

    // this method is used to view for about section
    public function editAbout()
    {
        $data['aboutSection'] = DB::table('cms_about_page')->first();
        return view('admin.content_management.about', $data);
    }

    // this method is used to insert or update about page
    public function updateAbout(Request $request)
    {
        $request->validate([
            'about_title' => 'required|string|max:100',
            'about_content' => 'required',
            'about_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $cmsAboutData = [
            'about_title' => $request->input('about_title', ''),
            'about_content' => $request->input('about_content', ''),
        ];

        $id = $request->id;

        $cmsAboutSection = null;
        if ($id) {
            $cmsAboutSection = DB::table('cms_about_page')->find($id);

            if (!$cmsAboutSection) {
                return redirect('cms/about')->with('error_msg', 'No record found with the provided ID.');
            }
        }

        // Handle banner image
        if ($request->hasFile('about_banner')) {
            $about_banner = $request->file('about_banner');
            $fileName = uniqid() . '.' . $about_banner->getClientOriginalExtension();
            $about_banner->move(public_path('cms_images/'), $fileName);

            // Delete previous image if it exists
            if (isset($cmsAboutSection->about_banner) && file_exists(public_path('cms_images/' . $cmsAboutSection->about_banner))) {
                unlink(public_path('cms_images/' . $cmsAboutSection->about_banner));
            }

            $cmsAboutData['about_banner'] = $fileName;
        }

        // Insert or Update
        if ($id) {
            DB::table('cms_about_page')->where('id', $id)->update($cmsAboutData);
            $message = 'About section updated successfully!';
        } else {
            $id = DB::table('cms_about_page')->insertGetId($cmsAboutData);
            $message = 'New about section added successfully!';
        }

        // Cleanup orphaned CKEditor images (after saving)
        CmsHelper::cleanupCkeditorImages($cmsAboutData['about_content']);

        return redirect('cms/about')->with('success_msg', $message);
    }

    public function uploadCkEditorImageForAboutUs(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/ckeditor'), $filename);

            $url = asset('uploads/ckeditor/' . $filename);

            return response()->json([
                'uploaded' => true,
                'url' => $url
            ]);
        }

        return response()->json(['uploaded' => false, 'error' => ['message' => 'No file uploaded.']]);
    }

    // this method is used to view for visitor content
    public function editVisitorContent()
    {
        $data['visitorContent'] = DB::table('cms_visitor_emails')->first();
        return view('admin.content_management.visitor_content', $data);
    }

    // this method is used to insert or update visitor content
    public function updateVisitorContent(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:255',
        ]);

        $cmsVisitorData = [
            'title' => $request->input('title', ''),
            'description' => $request->input('description', ''),
        ];

        $id = $request->id;

        $cmsVisitorSection = null;
        if ($id) {
            $cmsVisitorSection = DB::table('cms_visitor_emails')->find($id);

            if (!$cmsVisitorSection) {
                return redirect('cms/visitor-content')->with('error_msg', 'No record found with the provided ID.');
            }
        }

        // Insert or Update
        if ($id) {
            DB::table('cms_visitor_emails')->where('id', $id)->update($cmsVisitorData);
            $message = 'Visitor content updated successfully!';
        } else {
            $id = DB::table('cms_visitor_emails')->insertGetId($cmsVisitorData);
            $message = 'New visitor content added successfully!';
        }

        return redirect('cms/visitor-content')->with('success_msg', $message);
    }

    // this method is used to view for league
    public function editLeague()
    {
        $data['leaguePage'] = DB::table('cms_leagues')->first();
        return view('admin.content_management.leagues.league', $data);
    }

    // this method is used to insert or update league page
    public function updateLeague(Request $request)
    {
        $request->validate([
            'banner_title' => 'required|string|max:150',
            'banner_text' => 'nullable|string|max:255',
        ]);

        $cmsLeagueData = [
            'banner_title' => $request->input('banner_title', ''),
            'banner_text' => $request->input('banner_text', ''),
            'about_league_title' => $request->input('about_league_title', ''),
            'about_league_desc' => $request->input('about_league_desc', ''),
            'league_page_title' => $request->input('league_page_title', ''),
            'league_page_desc' => $request->input('league_page_desc', ''),
            'league_youtube_link' => $request->input('league_youtube_link', ''),
            // 'league_media_content' => $request->input('league_media_content', ''),
        ];

        $id = $request->id;

        // Check if updating an existing record
        if ($id) {
            $cmsLeague = DB::table('cms_leagues')->find($id);

            if (!$cmsLeague) {
                return redirect('cms/league')->with('error_msg', 'No record found with the provided ID.');
            }

            // Handle Banner Image
            if ($request->hasFile('banner_image')) {
                $banner_image = $request->file('banner_image');
                $fileName = uniqid() . '.' . $banner_image->getClientOriginalExtension();
                $banner_image->move(public_path('cms_images/leagues'), $fileName);

                // Remove old image if exists
                if ($cmsLeague->banner_image && file_exists(public_path('cms_images/leagues/' . $cmsLeague->banner_image))) {
                    unlink(public_path('cms_images/leagues/' . $cmsLeague->banner_image));
                }

                $cmsLeagueData['banner_image'] = $fileName;
            }

            // Handle League Cover Image
            if ($request->hasFile('league_cover_image')) {
                $league_cover_image = $request->file('league_cover_image');
                $fileName = uniqid() . '.' . $league_cover_image->getClientOriginalExtension();
                $league_cover_image->move(public_path('cms_images/leagues'), $fileName);

                // Remove old image if exists
                if ($cmsLeague->league_cover_image && file_exists(public_path('cms_images/leagues/' . $cmsLeague->league_cover_image))) {
                    unlink(public_path('cms_images/leagues/' . $cmsLeague->league_cover_image));
                }

                $cmsLeagueData['league_cover_image'] = $fileName;
            }

            DB::table('cms_leagues')->where('id', $id)->update($cmsLeagueData);
            $message = 'League page details updated successfully!';
        } else {
            // Insert new record
            if ($request->hasFile('banner_image')) {
                $banner_image = $request->file('banner_image');
                $fileName = uniqid() . '.' . $banner_image->getClientOriginalExtension();
                $banner_image->move(public_path('cms_images/leagues'), $fileName);

                $cmsLeagueData['banner_image'] = $fileName;
            }

            if ($request->hasFile('league_cover_image')) {
                $league_cover_image = $request->file('league_cover_image');
                $fileName = uniqid() . '.' . $league_cover_image->getClientOriginalExtension();
                $league_cover_image->move(public_path('cms_images/leagues'), $fileName);

                $cmsLeagueData['league_cover_image'] = $fileName;
            }

            $id = DB::table('cms_leagues')->insertGetId($cmsLeagueData);
            $message = 'League page details added successfully!';
        }

        return redirect('cms/league')->with($id ? 'success_msg' : 'error_msg', $message);
    }

    // this method is used to view for terms and conditions
    public function editGlossaryTerm()
    {
        $data['glossaryTerm'] = DB::table('cms_glossary_of_terms')->first();
        return view('admin.content_management.glossary_term', $data);
    }

    // this method is used to insert or update terms and conditions
    public function updateGlossaryTerm(Request $request)
    {
        $request->validate([
            'glossary_term_title' => 'required|string|max:100',
            'glossary_term_description' => 'required',
        ]);

        $cmsGlossaryTermData = [
            'glossary_term_title' => $request->input('glossary_term_title', ''),
            'glossary_term_description' => $request->input('glossary_term_description', ''),
        ];

        $id = $request->id;

        if ($id) {
            $cmsGlossaryTerm = DB::table('cms_glossary_of_terms')->find($id);

            if ($cmsGlossaryTerm) {
                DB::table('cms_glossary_of_terms')->where('id', $id)->update($cmsGlossaryTermData);
                $message = 'Glossary of terms updated successfully!';
            } else {
                return redirect('cms/terms-and-conditions')->with('error_msg', 'No record found with the provided ID.');
            }
        } else {
            $id = DB::table('cms_glossary_of_terms')->insertGetId($cmsGlossaryTermData);
            $message = 'New Glossary of terms added successfully!';
        }

        return redirect('cms/terms-and-conditions')->with($id ? 'success_msg' : 'error_msg', $message);
    }

    // this method is used to view for privacy policy
    public function editPrivacyPolicy()
    {
        $data['privacyPolicy'] = DB::table('cms_privacy_policy')->first();
        return view('admin.content_management.privacy_policy', $data);
    }

    // this method is used to insert or update privacy policy
    public function updatePrivacyPolicy(Request $request)
    {
        $request->validate([
            'privacy_policy_title' => 'required|string|max:100',
            'privacy_policy_description' => 'required',
        ]);

        $cmsPrivacyPolicyData = [
            'privacy_policy_title' => $request->input('privacy_policy_title', ''),
            'privacy_policy_description' => $request->input('privacy_policy_description', ''),
        ];

        $id = $request->id;

        if ($id) {
            $cmsPrivacyPolicy = DB::table('cms_privacy_policy')->find($id);

            if ($cmsPrivacyPolicy) {
                DB::table('cms_privacy_policy')->where('id', $id)->update($cmsPrivacyPolicyData);
                $message = 'Privacy Policy updated successfully!';
            } else {
                return redirect('cms/privacy-policy')->with('error_msg', 'No record found with the provided ID.');
            }
        } else {
            $id = DB::table('cms_privacy_policy')->insertGetId($cmsPrivacyPolicyData);
            $message = 'New Privacy Policy added successfully!';
        }

        return redirect('cms/privacy-policy')->with($id ? 'success_msg' : 'error_msg', $message);
    }
}
