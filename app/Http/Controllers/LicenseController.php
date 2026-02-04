<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /** @var LicenseService */
    protected $service;

    public function __construct(LicenseService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $status = $this->service->status();
        $hasPub = (bool) $this->service->getPublicKey();
        return view('license.index', compact('status', 'hasPub'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'license_text' => 'nullable|string',
            'license_file' => 'nullable|file|max:512',
            'public_key' => 'nullable|string',
        ]);

        if ($request->filled('public_key')) {
            $ok = $this->service->savePublicKey($request->input('public_key'));
            if (!$ok) return back()->with('error', 'Falha ao salvar a chave pública');
        }

        if ($request->hasFile('license_file')) {
            $json = file_get_contents($request->file('license_file')->getRealPath());
            $ok = $this->service->saveLicense($json);
            if (!$ok) return back()->with('error', 'Falha ao salvar a licença (arquivo)');
        } elseif ($request->filled('license_text')) {
            $ok = $this->service->saveLicense($request->input('license_text'));
            if (!$ok) return back()->with('error', 'Falha ao salvar a licença (texto)');
        }

        return redirect()->route('license.index')->with('success', 'Atualizado');
    }
}


