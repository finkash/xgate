<?php

namespace App\Domain\Engagement\Enums;

enum ReactionType: string
{
    case LIKE = 'like';
    case LOVE = 'love';
    case LAUGH = 'laugh';
    case WOW = 'wow';
    case SAD = 'sad';
    case ANGRY = 'angry';
}
