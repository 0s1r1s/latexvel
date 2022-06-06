<?php

namespace Os1r1s\LatexVel;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class LatexVel
{
    public static function pdfFromView(string $viewPath, string $fileName): string
    {
        $latex = view($viewPath)->render();

        return self::pdfFromString($latex, $fileName);
    }

    public static function pdfFromString(string $latexString, $fileName): string
    {
        $relativePath = 'latexvel/'.Str::random().'/';
        $storagePath = storage_path('app/'.$relativePath);
        File::makeDirectory($storagePath, 0777, true);
        File::put($storagePath.$fileName, $latexString);

        $program    = config('latexvel.latex_bin');
        $cmd        = [$program, $fileName];

        $process    = new Process($cmd);
        $process->setWorkingDirectory($storagePath);
        $process->run();

        if(!$process->isSuccessful()) {
            throw new Exception('Check latex log in storage.');
        }

        $idProgram = config('latexvel.makeindex_bin');
        $idProcess = new Process([$idProgram, '-s', config('latexvel.index_style_path'), $fileName.'.idx']);
        $idProcess->setWorkingDirectory($storagePath);
        $idProcess->run();

        $process->run();

        File::move($storagePath.$fileName.'.pdf', storage_path('app/latexvel/').$fileName.'.pdf');
        Storage::deleteDirectory($relativePath);

        return 'latexvel/'.$fileName.'.pdf';
    }
}
