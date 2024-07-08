<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

enum OpenWebNetDebuggingLevel: int
{
    case NONE = 0;
    case NORMAL = 1;
    case VERBOSE = 2;
}
