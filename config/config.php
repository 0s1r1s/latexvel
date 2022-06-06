<?php

return [
    'index_style_path' => resource_path('latex/index_style.ist'),
    'latex_bin' => env('LATEX_BIN', '/usr/bin/xelatex'),
    'makeindex_bin' => env('MAKEINDEX_BIN', '/usr/bin/makeindex'),
];
