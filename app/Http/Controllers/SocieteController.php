<?php

namespace App\Http\Controllers;

use App\Models\Societe;
use App\Services\SocieteService;
use App\Utils\ExtractSocieteFiltre;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SocieteController extends Controller
{
    private SocieteService $societeService;
    public function __construct( SocieteService $societeService)
    {
        $this->societeService = $societeService;
    }
    /**
     * Display a listing of the resource.
     */
     public function index(Request $request)
    {
        $filtre = ExtractSocieteFiltre::extractFilter($request);
        $data = $this->societeService->getAll($filtre);
        return Inertia::render("Societe/ListedesSociété_",
        [
            "data"=> $data,
            "filters" => [],
            "flash" => session('flash', [])
        ]);
    }

    public function suivi()
    {
        return Inertia::render("Societe/SuiviCotisation");
    }

    public function histori()
    {
        return Inertia::render("Societe/Historique");
    }

    public function Paiement()
    {
        return Inertia::render("Societe/Paiements");
    }

     public function alt()
    {
        return Inertia::render("Societe/alt");
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255',Rule::unique('societes')->whereNull('deleted_at')],
            'nom_contact' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:255',
            'stat' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|string|in:actif,inactif',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);
        $result = $this->societeService->createSociete($validated);
        return back()->with(
            $result['error'] ? 'message.error' : 'message.success',
            $result['message']
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Societe $societe)
    {
        return back()->with([
            'flash' => [
                'data' => $societe
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Societe $societe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Societe $societe)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255',Rule::unique('societes')
            ->ignore($societe->id)
            ->whereNull('deleted_at'),],
            'nom_contact' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:255',
            'stat' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|string|in:actif,inactif',
            'img' => 'nullable'
        ]);

        $result = $this->societeService->updateSociete($societe, $validated);

        return back()->with(
            $result['error'] ? 'message.error' : 'message.success',
            $result['message']
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Societe $societe)
    {
        $result = $this->societeService->deleteSociete($societe);
        return back()->with(
            $result['error'] ? 'message.error' : 'message.success',
            $result['message']
        );
    }
}
