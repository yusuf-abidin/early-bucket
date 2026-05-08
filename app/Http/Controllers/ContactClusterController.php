<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditContactRequest;
use App\Models\Area;
use App\Models\Category;
use App\Models\ContactCluster;
use App\Models\Regional;
use App\Models\StcTlContact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Storage;

class ContactClusterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $regionals = Regional::with([
            'contactCluster',
            'areas' => function ($query) {
                $query->orderBy('name');
            },
            'areas.contactCluster',
            'areas.branches' => function ($query) {
                $query->orderBy('name');
            },
            'areas.branches.contactCluster',
            'areas.branches.stcTlContacts',
            'areas.branches.stcTlContacts.categories' => function ($query) {
                $query->orderBy('name');
            },
            'branches' => function ($query) {
                $query->whereNull('area_id')->orderBy('name');
            },
            'branches.contactCluster',
            'branches.stcTlContacts',
            'branches.stcTlContacts.categories' => function ($query) {
                $query->orderBy('name');
            },
        ])
            ->orderBy('name')->get();

        $categories = Category::where('type', StcTlContact::TYPE_BUCKET)
            ->orderBy('name')
            ->get();

        return Inertia::render('contact_cluster/Index2', [
            'regionals' => $regionals,
            'categories' => $categories,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(EditContactRequest $request)
    {
        $validated = $request->validated();

        $uniqueKey = match(true) {
            !empty($validated['regional_id']) => ['regional_id' => $validated['regional_id']],
            !empty($validated['area_id'])     => ['area_id'     => $validated['area_id']],
            !empty($validated['branch_id'])   => ['branch_id'   => $validated['branch_id']],
        };

        $fillable = array_diff_key($validated, $uniqueKey);
        $existing =  ContactCluster::where($uniqueKey)->first();
        if($request->hasFile('avatar')) {
            if ($existing && $existing->avatar) {
                Storage::disk('public')->delete($existing->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $fillable['avatar'] = $path;
        } elseif ($request->input('remove_avatar') == 1) {
            if ($existing && $existing->avatar) {
                Storage::disk('public')->delete($existing->avatar);
            }
            $fillable['avatar'] = null;
        }
        ContactCluster::updateOrCreate($uniqueKey, $fillable);

        return back()->with('success', 'Kontak berhasil diperbarui.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EditContactRequest $request, ContactCluster $contactCluster)
    {
        $validated = $request->validated();
        if ($request->hasFile('avatar')) {
            if ($contactCluster->avatar && Storage::disk('public')->exists($contactCluster->avatar)){
                Storage::disk('public')->delete($contactCluster->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        } elseif ($request->input('remove_avatar') == 1) {
            if ($contactCluster->avatar) {
                Storage::disk('public')->delete($contactCluster->avatar);
            }
            $validated['avatar'] = null;
        }
        $contactCluster->update($validated);

        return back()->with('success', 'Kontak berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactCluster $contactCluster)
    {
        try {
            if ($contactCluster->avatar && Storage::disk('public')->exists($contactCluster->avatar)){
                Storage::disk('public')->delete($contactCluster->avatar);
            }
            $contactCluster->delete();
            return back()->with('success', 'Kontak berhasil dihapus.');
        }catch (\Exception $exception){
            return back()->withErrors(['message' => 'gagal menghapus kontak: ' . $exception->getMessage()]);
        }
    }
}
