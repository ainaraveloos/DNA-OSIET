<?php

namespace App\Repositories;

use App\Models\Societe;
use App\Models\User;

class SocieteRepository extends BaseRepository
{
    public function __construct(Societe $societe)
    {
        parent::__construct($societe);
    }

    public function getModel()
    {
        return $this->model;
    }
}
