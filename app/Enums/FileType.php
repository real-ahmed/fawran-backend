<?php

namespace App\Enums;

enum FileType: string
{
    case Image = 'image';
    case Video = 'video';
    case Document = 'document';
}
