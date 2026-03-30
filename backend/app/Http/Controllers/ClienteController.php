<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ClienteController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = Cliente::orderBy('nome', 'asc');

    //     // pesquisa por nome
    //     if ($request->has('search') && $request->search) {
    //         $query->where('nome', 'ilike', '%' . $request->search . '%');
    //     }

    //     return $query->paginate(8);
    // }

    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $page = $request->query('page', 1);

        // Chave única para evitar colisões
        $cacheKey = "clientes_index_p{$page}_s_" . md5((string)$search);

        return Cache::tags(['clientes'])->remember($cacheKey, 3600, function () use ($search) {
            $query = Cliente::orderBy('nome', 'asc');

            if (!empty($search)) {
                $query->where('nome', 'ilike', '%' . $search . '%');
            }

            // O SEGREDO: toArray() remove as "Closures" pesadas do Eloquent
            // e salva apenas os dados que o Vue precisa.
            return $query->paginate(8)->toArray();
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'cpf' => 'required|string|unique:clientes,cpf',
            'email' => 'required|string|unique:clientes,email',
            'telefone' => 'required|string',
            'endereco' => 'required|string',
            'numero' => 'required|max:10',
            'cep' => 'required|string',
            'data_nascimento' => 'required|date|before_or_equal:today',
            'genero' => 'required|string',
        ]);

        $cliente = Cliente::create([
            'nome' => $request->nome,
            'cpf' => $request->cpf,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'endereco' => $request->endereco,
            'numero' => $request->numero,
            'cep' => $request->cep,
            'data_nascimento' => $request->data_nascimento,
            'genero' => $request->genero,
        ]);

        Cache::tags(['clientes'])->flush();
        return response()->json($cliente, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }
        return response()->json($cliente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }

        $request->validate([
            'nome' => 'sometimes|string',
            'cpf' => 'sometimes|string|unique:clientes,cpf,' . $id,
            'email' => 'sometimes|string|unique:clientes,email,' . $id,
            'telefone' => 'sometimes|string',
            'endereco' => 'sometimes|string',
            'numero' => 'sometimes|max:10',
            'cep' => 'sometimes|string',
            'data_nascimento' => 'sometimes|date|before_or_equal:today',
            'genero' => 'sometimes|string',
        ]);
        $cliente->update($request->only(['nome', 'cpf', 'email', 'telefone', 'endereco', 'numero', 'cep', 'data_nascimento', 'genero']));
        Cache::tags(['clientes'])->flush();
        return response()->json($cliente);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }
        $cliente->delete();
        Cache::tags(['clientes'])->flush();
        return response()->json(['message' => 'Cliente deletado com sucesso']);
    }

    public function verificarCpf(Request $request)
    {
        $existe = Cliente::where('cpf', $request->cpf)
            ->when($request->id, function ($q) use ($request) {
                return $q->where('id', '!=', $request->id); // Ignora o próprio usuário
            })
            ->exists();

        return response()->json(['existe' => $existe]);
    }

    public function verificarEmail(Request $request)
    {
        $existe = Cliente::where('email', $request->email)
            ->when($request->id, function ($q) use ($request) {
                return $q->where('id', '!=', $request->id); // Adicione esta linha
            })
            ->exists();

        return response()->json(['existe' => $existe]);
    }
}
