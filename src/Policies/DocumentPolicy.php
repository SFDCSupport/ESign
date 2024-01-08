<?php

namespace NIIT\ESign\Policies;

use App\Models\User;
use NIIT\ESign\Models\ESignDocument;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ESignDocument $document): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ESignDocument $document): bool
    {
        return true;
    }

    public function delete(User $user, ESignDocument $document): bool
    {
        return true;
    }

    public function restore(User $user, ESignDocument $document): bool
    {
        return true;
    }

    public function forceDelete(User $user, ESignDocument $document): bool
    {
        return true;
    }
}
