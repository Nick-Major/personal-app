<?php

namespace App\Enums;

enum PayerSelectionType: string
{
    case STRICT = 'strict';
    case OPTIONAL = 'optional';
    case ADDRESS_BASED = 'address_based';
}
