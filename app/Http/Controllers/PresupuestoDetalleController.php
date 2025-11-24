<?php

namespace App\Http\Controllers;

use App\Models\Presupuesto;
use App\Models\PresupuestoDetalle;
use Illuminate\Http\Request;

class PresupuestoDetalleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Presupuesto $presupuesto)
    {
        $presupuesto->load('detalles');
        return view('presupuestos.detalles.index', compact('presupuesto'));
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
    public function store(Request $r, Presupuesto $presupuesto)
    {
        $data = $r->validate([
            'codigo'=>'required|max:50',
            'codigo_alterno'=>'nullable|max:50',
            'descripcion'=>'required|max:255',
            'cantidad'=>'required|numeric|min:0',
            'und'=>'required|max:10',
            'precio'=>'required|numeric|min:0',
            'nivel'=>'nullable|max:50',
            'tipo'=>'nullable|max:50',
            'id_recurso'=>'nullable|integer',
        ]);
        $data['id_presupuesto'] = $presupuesto->id_presupuesto;
        $data['monto'] = ($data['cantidad'] ?? 0) * ($data['precio'] ?? 0);
        PresupuestoDetalle::create($data);

        return redirect()->route('presupuestos.edit', $presupuesto->id_presupuesto)->with('ok','Ítem agregado');
    }

    /**
     * Display the specified resource.
     */
    public function show(PresupuestoDetalle $presupuestoDetalle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PresupuestoDetalle $presupuestoDetalle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r, PresupuestoDetalle $detalle)
    {
        $data = $r->validate([
            'codigo'=>'required|max:50',
            'codigo_alterno'=>'nullable|max:50',
            'descripcion'=>'required|max:255',
            'cantidad'=>'required|numeric|min:0',
            'und'=>'required|max:10',
            'precio'=>'required|numeric|min:0',
            'nivel'=>'nullable|max:50',
            'tipo'=>'nullable|max:50',
            'id_recurso'=>'nullable|integer',
        ]);
        $data['monto'] = ($data['cantidad'] ?? 0) * ($data['precio'] ?? 0);
        $detalle->update($data);

        return redirect()->route('presupuestos.edit', $detalle->id_presupuesto)->with('ok','Ítem actualizado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PresupuestoDetalle $detalle)
    {
        $pid = $detalle->id_presupuesto;
        $detalle->delete();
        return redirect()->route('presupuestos.edit', $pid)->with('ok','Ítem eliminado');
    }
}

