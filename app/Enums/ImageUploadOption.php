<?php

namespace App\Enums;

enum ImageUploadOption: string
{
    case WHITE_EDGE = 'white-edge';
    case FILL = 'fill';
    case STRETCH = 'stretch';
    case ORIGINAL = 'original';
}
