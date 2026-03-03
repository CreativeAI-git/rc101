<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Embed;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EmbedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Embed::orderBy('title', 'ASC');

            // Apply menu filter
            if (!empty($request->menu_type)) {
                $query->where('menu_type', $request->menu_type);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('serial_number', function ($row) {
                    static $index = 0;
                    return ++$index;
                })
                ->addColumn('image', function ($row) {
                    if (!empty($row->image) && file_exists(public_path('cms_images/' . $row->image))) {
                        return '<img src="' . asset('cms_images/' . $row->image) . '" width="50" height="50" class="img-thumbnail" />';
                    }
                    return '<img src="' . asset('admin/img/shop-img/no_image.png') . '" width="50" height="50" class="img-thumbnail" />';
                })
                ->addColumn('embed_link', function ($row) {
                    return $row->linked_name
                        ? '<a href="' . $row->embed_link . '" target="_blank"><span style="max-width:300px;display:block">' . $row->linked_name . '</span></a>'
                        : '<a href="' . $row->embed_link . '" target="_blank"><span style="max-width:300px;display:block">' . $row->embed_link . '</span></a>';
                })
                ->addColumn('menu_type', function ($row) {
                    $types = [
                        'lexicon' => 'Lexicon',
                        'weight_classes' => 'Weight Classes',
                        'vendors' => 'Vendors',
                        'youtube_channel' => 'Youtube Channel',
                        'tools' => 'Tools',
                        'robot_combat_starter_kits' => 'Robot Combat Starter Kits',
                    ];
                    return $types[$row->menu_type] ?? ucfirst(str_replace('_', ' ', $row->menu_type));
                })
                ->addColumn('action', function ($row) {
                    return '
                    <div class="d-flex align-items-center gap-3">
                        <a href="' . url('cms/embeds-edit/' . $row->id) . '">
                            <lord-icon src="https://cdn.lordicon.com/wuvorxbv.json"
                                trigger="hover"
                                colors="primary:#333333,secondary:#333333"
                                style="width:20px;height:20px">
                            </lord-icon>
                        </a>
                        <a href="javascript:;" onclick="deleteConfirm(' . $row->id . ')">
                            <lord-icon src="https://cdn.lordicon.com/drxwpfop.json"
                                trigger="hover"
                                colors="primary:#ff0000,secondary:#ff0000"
                                style="width:20px;height:20px">
                            </lord-icon>
                        </a>
                    </div>';
                })
                ->rawColumns(['image', 'embed_link', 'action'])
                ->make(true);
        }

        return view('admin.content_management.embeds.list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.content_management.embeds.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->has('embed_link') && !preg_match('/^https?:\/\//', $request->embed_link)) {
            $request->merge([
                'embed_link' => 'https://' . $request->embed_link
            ]);
        }

        $request->validate([
            'title' => 'required|string|max:150',
            'type' => 'required|in:doc,slide,link',
            'menu_type' => 'required|in:lexicon,weight_classes,vendors,youtube_channel,tools,robot_combat_starter_kits',
            'embed_link' => 'required|url',
            'linked_name' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $image = '';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('cms_images/'), $fileName);

            $image = $fileName;
        }

        $isInserted = Embed::insert([
            'title' => $request->title,
            'type' => $request->type,
            'menu_type' => $request->menu_type,
            'embed_link' => $request->embed_link,
            'linked_name' => $request->linked_name,
            'image' => $image
        ]);

        if ($isInserted) {
            return redirect('cms/embeds-list')->with('success_msg', 'Data added successfully!');
        } else {
            return redirect('cms/embeds-list')->with('error_msg', 'Something went wrong!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data['embedsData'] = Embed::where('id', $id)->first();
        return view('admin.content_management.embeds.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        if ($request->has('embed_link') && !preg_match('/^https?:\/\//', $request->embed_link)) {
            $request->merge([
                'embed_link' => 'https://' . $request->embed_link
            ]);
        }

        $request->validate([
            'id' => 'required', // Ensure an id is provided for updating
            'title' => 'required|string|max:150',
            'type' => 'required|in:doc,slide,link',
            'menu_type' => 'required|in:lexicon,weight_classes,vendors,youtube_channel,tools,robot_combat_starter_kits',
            'embed_link' => 'required',
            'linked_name' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $id = $request->id;

        // Check if the provided id exists
        $existingData = Embed::where('id', $id)->first();
        if (!$existingData) {
            return redirect('cms/embeds-list')->with('error_msg', 'Data not found.');
        }

        // Update fields
        $updateData = [
            'title' => $request->title,
            'type' => $request->type,
            'menu_type' => $request->menu_type,
            'embed_link' => $request->embed_link,
            'linked_name' => $request->linked_name
        ];

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('cms_images/'), $fileName);

            // Delete the old image if it exists
            $oldImage = $existingData->image;
            $oldImagePath = public_path('cms_images/' . $oldImage);
            if (file_exists($oldImagePath) && !empty($oldImage)) {
                unlink($oldImagePath);
            }

            $updateData['image'] = $fileName;
        }

        // Perform the update
        $isUpdated = Embed::where('id', $id)->update($updateData);

        if ($isUpdated) {
            return redirect('cms/embeds-list')->with('success_msg', 'Data updated successfully!');
        } else {
            return redirect('cms/embeds-list')->with('error_msg', 'Failed to update data.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $result = Embed::where('id', $request->id)->first();

        if ($result) {
            if (!empty($result->image)) {
                $imagePath = public_path('cms_images/' . $result->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            Embed::where('id', $request->id)->delete();
            return response()->json(['success' => true, 'message' => 'Data deleted successfully.'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Data not found.'], 404);
        }
    }
}
