<?php

namespace App\Http\Controllers;

use App\Http\Helpers\CodeHelper;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function CreateUser(Request $request) {
        $validate = Validator::make($request->all(),[
            'Email'     => 'email|required',
            'Nome'      => 'String|required',
            'Telefone'  => 'String|required',
            'Senha'     => 'String|required',
            'CpfCnpj'   => 'String|required'
        ],['required' => 'O campo :attribute é obrigatório']);

        if ($validate->fails()){
            return response()->json([
                'Sucesso'   => false,
                'Mensagem'  => 'Ocorreram Erros na validação dos campos enviados',
                'Campos'    => $validate->errors()
            ],400);
        }
        Log::info(1);
        $cpfCnpj = CodeHelper::limpaCNPJandCPF($request->CpfCnpj);
        if (strlen($cpfCnpj) == 11 ? !CodeHelper::CPF($cpfCnpj) : !CodeHelper::CNPJ($cpfCnpj)) {
            return response()->json([
                'Sucesso' => false,
                'Mensagem' => config('errors.CpfCnpjInvalido')
            ],400);
        }
        Log::info(1);
        if (User::where('Email', $request->Email)->count() > 0){
            Log::info('EMAIL CADASTRADOs');
            return response()->json([
                'Sucesso' => false,
                'Mensagem' => config('errors.EmailJaCadastrado')
            ],400);
        }
        Log::info(1);
        if (User::where('CpfCnpj', $request->CpfCnpj)->count() > 0){
            return response()->json([
                'Sucesso' => false,
                'Mensagem' => config('errors.CpfCnpjJaCadastrado')
            ],400);
        }
        Log::info(1);
        try {
            $user = User::create([
                'Nome' => $request->Nome,
                'Email' => $request->Email,
                'Telefone' => $request->Telefone,
                'CpfCnpj' => $cpfCnpj,
                'Senha' => Hash::make($request->Senha)
            ]);
            Log::info(1);
            return response()->json([
                'Sucesso' => true,
                'Mensagem' => 'Conta criada com sucesso'
            ],200);
        } catch (Exception $e) {
            Log::alert($e);
            return response()->json([
                'Sucesso' => false,
                'Mensagem' => config('errors.ErroGenerico')
            ],500);
        }
    }


}
