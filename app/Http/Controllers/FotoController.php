<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FotoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $codpes = $user->codpes;

        // 📷 APENAS FOTO CUSTOMIZADA (com cache busting)
        $fotoCustom = null;
        $fotoPath = "fotos/{$codpes}.jpg";
        if ($codpes && Storage::disk('public')->exists($fotoPath)) {
            $timestamp = Storage::disk('public')->lastModified($fotoPath);
            $fotoCustom = asset("storage/{$fotoPath}?v={$timestamp}");
        }

        return view('fotos.index', compact('codpes', 'fotoCustom'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $user = Auth::user();
        $codpes = $user->codpes;
        $file = $request->file('foto');

        // 🖼️ Processamento com Intervention Image v3 (Crop 3x4)
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        $width = $image->width();
        $height = $image->height();
        $ratio = 3 / 4;

        if ($width / $height > $ratio) {
            $newWidth = (int)($height * $ratio);
            $newHeight = $height;
        } else {
            $newWidth = $width;
            $newHeight = (int)($width / $ratio);
        }

        $x = (int)(($width - $newWidth) / 2);
        $y = (int)(($height - $newHeight) / 2);

        $image->crop($newWidth, $newHeight, $x, $y);
        $image->scale(width: 300); // Redimensiona mantendo 3:4

        $path = storage_path("app/public/fotos/{$codpes}.jpg");
        $image->toJpeg(90)->save($path);

        return back()->with('success', 'Foto atualizada e ajustada para o padrão 3x4!');
    }
}