<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function toggleFotoPublica(Request $request)
    {
        $user = auth()->user();
        
        // Inverte o valor atual
        $user->autoriza_foto_publica = !$user->autoriza_foto_publica;
        $user->save();
        
        return redirect()->route('home')->with('success', 
            $user->autoriza_foto_publica 
                ? '✅ Foto autorizada para exibição pública' 
                : '❌ Autorização de exibição pública revogada'
        );
    }
}