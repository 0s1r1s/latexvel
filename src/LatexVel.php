<?php

namespace Os1r1s\LatexVel;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class LatexVel
{
    public static function pdfFromView(string $viewPath, string $fileName)
    {
        $latex = view($viewPath)->render();

        return self::pdfFromString($latex, $fileName);
    }

    public static function pdfFromString(string $latexString, $fileName): bool
    {
        $relativePath = 'latexvel/'.Str::random().'/';
        $storagePath = storage_path('app/'.$relativePath);
        File::makeDirectory($storagePath);
        File::put($storagePath.$fileName, $latexString);

        $program    = '/Library/TeX/texbin/xelatex';
        $cmd        = [$program, $fileName];

        $process    = new Process($cmd);
        $process->setWorkingDirectory($storagePath);
        $process->run();

        if(!$process->isSuccessful()) {
            throw new Exception('Check latex log in storage.');
        }

        $idProcess = new Process(['/Library/TeX/texbin/makeindex', '-s', config('latexvel.index_style_path'), $fileName.'.idx']);
        $idProcess->setWorkingDirectory($storagePath);
        $idProcess->run();

        $process->run();

        File::move($storagePath.$fileName.'.pdf', storage_path('app/latexvel/').$fileName.'.pdf');
        Storage::deleteDirectory($relativePath);

        return true;
    }
}
