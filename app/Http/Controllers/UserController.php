<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
 

class UserController extends Controller
{
    
    public function index()
    {
        try {
            $users = User::all();
            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'erro ao listar usuarios'], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            Log::info('Usuario {name} criado', ['name' => $user->name,'email' => $user->email]);

            return response()->json($user, 201);
        } catch (\Exception $e) {
            Log::info('Erro ao criar usuario {name}', ['name' => $request->name,'email' => $request->email]);

            return response()->json(['error' => 'erro ao criar usuario'], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json($user, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'usuario nao encontrado'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::findOrFail($id);
            $user->update($request->only(['name', 'email', 'password']));

            Log::info('Usuario {name} atualizado', ['name' => $user->name,'email' => $user->email]);

            return response()->json($user, 200);
        } catch (\Exception $e) {
            Log::info('Erro ao atualizar usuario {id}', ['id' => $id]);

            return response()->json(['error' => 'erro ao atualizar usuario'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            Log::info('Usuario {name} excluido', ['id' => $id,'name'=>$user->name]);

            return response()->json(['message' => 'usuario deletado com sucesso'], 200);
        } catch (\Exception $e) {
            Log::info('Erro ao excluir usuario {id}', ['id' => $id]);

            return response()->json(['error' => 'erro ao deletar usuario'], 500);
        }
    }
}

