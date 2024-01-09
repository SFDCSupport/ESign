<?php

namespace NIIT\ESign\Concerns;

use App\Models\User;
use NIIT\ESign\Models\Audit;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

trait Auditable
{
    public function logAuditTrait(Document $document, string $event, ?Signer $signer = null, array $metadata = []): Audit
    {
        $model = new Audit;

        $model->event = $event;
        $model->metadata = $metadata;
        $model->signer = $signer;
        $model->document = $document;

        $model->save();

        return $model;
    }

    public function getAuditsByDocument(Document $document)
    {
        return $this->getAuditBy([
            'document_id' => $document->id,
        ])->oldest();
    }

    public function getAuditsBySigner(Document $document, Signer $signer)
    {
        return $this->getAuditBy([
            'document_id' => $document->id,
            'signer_id' => $signer->id,
        ])->oldest();
    }

    public function getAuditsByCreator(User $user)
    {
        return $this->getAuditBy([
            'created_by' => $user->id,
        ])->oldest();
    }

    public function getAllAudits()
    {
        return $this->getAuditBy()->oldest();
    }

    private function getAuditBy(array $conditions = [])
    {
        return Audit::when(! blank($conditions),
            static fn ($q) => $q->where($conditions)
        )->get();
    }
}
