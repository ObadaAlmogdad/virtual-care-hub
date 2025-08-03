<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedicalBanner;
use Illuminate\Support\Facades\Storage;

class MedicalBannerController extends Controller
{
    public function index()
    {
        $banners = MedicalBanner::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();

        return response()->json($banners);
    }

    public function show($id)
    {
        $banner = MedicalBanner::find($id);

        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        return response()->json($banner);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048', // صورة بحجم أقصى 2 ميجا
            'link' => 'nullable',
            'is_active' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        // حفظ الصورة في التخزين (public disk)
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('medical_banners', 'public');
            $validated['image_url'] = Storage::url($path);  // رابط الصورة قابل للوصول
        }

        $banner = MedicalBanner::create($validated);

        return response()->json($banner, 201);
    }

    public function update(Request $request, $id)
    {
        $banner = MedicalBanner::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|image|max:2048',
            'link' => 'nullable',
            'expires_at' => 'nullable|date',
        ]);


        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إن وجدت
            if ($banner->image_url) {
                $oldPath = str_replace('/storage/', '', $banner->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('medical_banners', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $banner->update($validated);
        
        return response()->json($banner);
    }

    // حذف بانر
    public function destroy($id)
    {
        $banner = MedicalBanner::findOrFail($id);

        if ($banner->image_url) {
            $oldPath = str_replace('/storage/', '', $banner->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully']);
    }

    // تفعيل أو تعطيل بانر (مثلاً تحديث حقل is_active فقط)
    public function toggleActive($id)
    {
        $banner = MedicalBanner::findOrFail($id);
        $banner->is_active = !$banner->is_active;
        $banner->save();

        return response()->json($banner);
    }
}
