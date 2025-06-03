<?php

namespace App\Enum;

enum RoleType: string
{
    case ADMIN = 'admin';
    case ENSEIGNANT = 'enseignant';
    case ETUDIANT = 'etudiant';
}