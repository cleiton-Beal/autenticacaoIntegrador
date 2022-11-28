<?php

namespace App\Http\Controllers;

use App\Http\Helpers\CodeHelper;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function GerarToken(Request $request) {
        $validate = Validator::make($request->all(),[
            'CpfCnpj'   => 'String|required',
            'Senha'     => 'String|required',
        ],['required' => 'O campo :attribute é obrigatório']);

        if ($validate->fails()){
            return response()->json([
                'Sucesso'   => false,
                'Mensagem'  => 'Ocorreram Erros na validação dos campos enviados',
                'Campos'    => $validate->errors()
            ],400);
        }

        $cpfCnpj = CodeHelper::limpaCNPJandCPF($request->CpfCnpj);
        if (strlen($cpfCnpj) == 11 ? !CodeHelper::CPF($cpfCnpj) : !CodeHelper::CNPJ($cpfCnpj)) {
            return response()->json([
                'Sucesso' => false,
                'Mensagem' => config('errors.CpfCnpjInvalido')
            ],400);
        }

        try {
            $user = User::where('CpfCnpj',$request->CpfCnpj)->first();
            if ($user && Hash::check($request->Senha, $user->Senha)) {
                $token = $user->createToken('token')->plainTextToken;
                if($token){
                    return response()->json([
                        'Sucesso' => true,
                        'mensagem' => 'Token gerado com sucesso!',
                        'Token' => $token
                    ]);
                }
            }
            else {
                return response()->json([
                    'Sucesso' => false,
                    'Mensagem' => 'Credenciais inválidas'
                ]);
            }
        }
        catch (Exception $e) {
            Log::alert($e);
            return response()->json([
                'Sucesso' => false,
                'Mensagem' => config('errors.ErroGenerico')
            ],500);
        }
    }
}
