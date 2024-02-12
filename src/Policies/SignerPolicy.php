<?php

namespace NIIT\ESign\Policies;

use App\Models\User;
use NIIT\ESign\Models\Signer;

class SignerPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Signer $signer): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, Signer $signer): bool
    {
        return true;
    }

    public function delete(?User $user, Signer $signer): bool
    {
        return true;
    }

    public function restore(?User $user, Signer $signer): bool
    {
        return true;
    }

    public function forceDelete(?User $user, Signer $signer): bool
    {
        return true;
    }
}
