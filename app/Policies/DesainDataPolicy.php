<?php

namespace App\Policies;

use App\Models\DataDesain;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DesainDataPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, DataDesain $dataDesain): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $dataDesain->accepted_by_id == $user->id;
    }

    public function terima(User $user): bool
    {
        if ($user->hasRole('Super Admin') | $user->hasRole('Desain Data')) {
            return true;
        }

        return false;
    }

    public function batal(User $user, DataDesain $dataDesain): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $dataDesain->accepted_by_id == $user->id;
    }

    public function update(User $user, DataDesain $dataDesain): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $dataDesain->accepted_by_id == $user->id;
    }

    public function selesai(User $user, DataDesain $dataDesain): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $dataDesain->accepted_by_id == $user->id;
    }
}
