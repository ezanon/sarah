<?php
namespace App\Http\Controllers;
use App\Models\OdsUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OdsController extends Controller
{
    
    public const ODS_LIST = [
        1  => ['nome' => 'Erradicação da pobreza', 'img' => 'images/ods/SDG-01.jpg'],
        2  => ['nome' => 'Fome zero e agricultura sustentável', 'img' => 'images/ods/SDG-02.jpg'],
        3  => ['nome' => 'Saúde e bem-estar', 'img' => 'images/ods/SDG-03.jpg'],
        4  => ['nome' => 'Educação de qualidade', 'img' => 'images/ods/SDG-04.jpg'],
        5  => ['nome' => 'Igualdade de gênero', 'img' => 'images/ods/SDG-05.jpg'],
        6  => ['nome' => 'Água potável e saneamento', 'img' => 'images/ods/SDG-06.jpg'],
        7  => ['nome' => 'Energia acessível e limpa', 'img' => 'images/ods/SDG-07.jpg'],
        8  => ['nome' => 'Trabalho decente e crescimento econômico', 'img' => 'images/ods/SDG-08.jpg'],
        9  => ['nome' => 'Indústria, inovação e infraestrutura', 'img' => 'images/ods/SDG-09.jpg'],
        10 => ['nome' => 'Redução das desigualdades', 'img' => 'images/ods/SDG-10.jpg'],
        11 => ['nome' => 'Cidades e comunidades sustentáveis', 'img' => 'images/ods/SDG-11.jpg'],
        12 => ['nome' => 'Consumo e produção responsáveis', 'img' => 'images/ods/SDG-12.jpg'],
        13 => ['nome' => 'Ação contra a mudança global do clima', 'img' => 'images/ods/SDG-13.jpg'],
        14 => ['nome' => 'Vida na água', 'img' => 'images/ods/SDG-14.jpg'],
        15 => ['nome' => 'Vida terrestre', 'img' => 'images/ods/SDG-15.jpg'],
        16 => ['nome' => 'Paz, justiça e instituições fortes', 'img' => 'images/ods/SDG-16.jpg'],
        17 => ['nome' => 'Parcerias e meios de implementação', 'img' => 'images/ods/SDG-17.jpg'],
    ];

    public function index()
    {
        $minhasOds = OdsUsuario::where('user_id', Auth::id())->pluck('ods_id')->toArray();
        return view('ods.index', compact('minhasOds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ods'   => 'nullable|array',
            'ods.*' => 'integer|between:1,17',
        ]);

        // Substitui a seleção anterior (sincronização)
        OdsUsuario::where('user_id', Auth::id())->delete();
        foreach ($request->input('ods', []) as $odsId) {
            OdsUsuario::create(['user_id' => Auth::id(), 'ods_id' => $odsId]);
        }

        return back()->with('success', 'ODS atualizadas com sucesso!');
    }
}