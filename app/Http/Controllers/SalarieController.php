<?php

namespace App\Http\Controllers;

use App\Models\Salarie;
use App\Services\SalarieService;
use App\Services\SocieteService;
use App\Utils\ExtractSalarieFiltre;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SalarieController extends Controller
{
    private SalarieService $salarieService;
    public function __construct( SalarieService $salarieService)
    {
        $this->salarieService = $salarieService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filtre = ExtractSalarieFiltre::extractFilter($request);
        $data = $this->salarieService->getAll($filtre);
        return Inertia::render("AyantsEndroit/ListedesAyantsDroits_",
        [
            "data"=> $data,
            "filters" => [],
            "flash" => session('flash', [])
        ]);
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
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'sexe' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'date_debut' => 'required|date',
            'poste' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'date_fin' => 'required|date',
            'date_adhesion' => 'required|date',
            'type_adhesion' => 'required|string|max:255',
            'numero' => 'required|string|max:255',
            'beneficiaires' => 'required|array',
            'beneficiaires.*.nom_complet' => 'required|string|max:255',
            'beneficiaires.*.sexe' => 'required|string|max:255',
            'beneficiaires.*.date_naissance' => 'required|date',
            'beneficiaires.*.lien_parente' => 'required|string|max:255',
        ]);
        $result = $this->salarieService->create($validated);
        return back()->with(
            $result['error'] ? 'message.error' : 'message.success',
            $result['message']
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Salarie $salarie)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Salarie $salarie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Salarie $salarie)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Salarie $salarie)
    {

    }
}
