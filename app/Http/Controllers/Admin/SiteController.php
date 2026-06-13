<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\UpdateSiteRequest;
use App\Models\Site;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $sites = Site::query()
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('id', $user->site_id))
            ->orderBy('code')
            ->get();
        return view('admin.sites.index', compact('sites'));
    }

    public function edit(Site $site): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && $user->site_id !== $site->id) {
            return redirect()->route('admin.sites.index')
                ->with('flash', Flash::err('Anda hanya bisa edit klinik sendiri.'));
        }
        return view('admin.sites.edit', compact('site'));
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isSuperAdmin() && $user->site_id !== $site->id) {
            return back()->with('flash', Flash::err('Anda hanya bisa edit klinik sendiri.'));
        }

        try {
            $data = $request->validated();

            // Upload logo
            if ($request->hasFile('logo')) {
                if ($site->logo_url && Storage::disk('public')->exists($site->logo_url)) {
                    Storage::disk('public')->delete($site->logo_url);
                }
                $data['logo_url'] = $request->file('logo')->store("sites/{$site->id}", 'public');
            }
            unset($data['logo']);

            // Upload kop surat
            if ($request->hasFile('kop_image')) {
                if ($site->kop_image_url && Storage::disk('public')->exists($site->kop_image_url)) {
                    Storage::disk('public')->delete($site->kop_image_url);
                }
                $data['kop_image_url'] = $request->file('kop_image')->store("sites/{$site->id}/kop", 'public');
            }
            unset($data['kop_image']);

            $site->update($data);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal update: ' . $e->getMessage()));
        }

        return redirect()->route('admin.sites.edit', $site)
            ->with('flash', Flash::ok("Pengaturan klinik <b>{$site->name}</b> diperbarui."));
    }

    public function destroyLogo(Site $site): RedirectResponse
    {
        if ($site->logo_url && Storage::disk('public')->exists($site->logo_url)) {
            Storage::disk('public')->delete($site->logo_url);
        }
        $site->update(['logo_url' => null]);
        return back()->with('flash', Flash::ok('Logo dihapus.'));
    }

    public function destroyKop(Site $site): RedirectResponse
    {
        if ($site->kop_image_url && Storage::disk('public')->exists($site->kop_image_url)) {
            Storage::disk('public')->delete($site->kop_image_url);
        }
        $site->update(['kop_image_url' => null]);
        return back()->with('flash', Flash::ok('Kop surat dihapus.'));
    }
}
