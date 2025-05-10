<?php

namespace SoureCode\Bundle\DoctrineExtension\Attributes;

enum Mode: string
{
    case ALWAYS = 'always';
    case NULL = 'null';
}
