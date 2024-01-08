<?php

namespace NIIT\ESign\Policies;

use App\Models\User;
use NIIT\ESign\Models\Document;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Document $document): bool
    {
        return true;
    }

    public function delete(User $user, Document $document): bool
    {
        return true;
    }

    public function restore(User $user, Document $document): bool
    {
        return true;
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return true;
    }
}
