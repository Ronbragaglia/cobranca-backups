<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use Illuminate\Http\Request;

class CobrancaController extends Controller
{
    public function index()
    {
        return Cobranca::query()->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'data_vencimento' => ['required', 'date', 'after:today'],
            'telefone' => ['required', 'string', 'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/'],
        ]);

        return Cobranca::create($data);
    }

    public function show(Cobranca $cobranca)
    {
        return $cobranca;
    }

    public function update(Request $request, Cobranca $cobranca)
    {
        $data = $request->validate([
            'descricao' => ['sometimes', 'required', 'string', 'max:255'],
            'valor' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'string'],
            'data_vencimento' => ['sometimes', 'required', 'date', 'after:today'],
            'telefone' => ['sometimes', 'required', 'string', 'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/'],
        ]);

        $cobranca->update($data);

        return $cobranca;
    }

    public function destroy(Cobranca $cobranca)
    {
        $cobranca->delete();

        return response()->json(null, 204);
    }
}
