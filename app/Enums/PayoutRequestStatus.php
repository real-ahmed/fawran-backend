<?php

namespace App\Enums;

enum PayoutRequestStatus: string
{
    case Pending = 'pending';
    case Transferred = 'transferred';
    case Rejected = 'rejected';
}
