<?php

namespace NIIT\ESign\Concerns;

use App\Models\User;
use NIIT\ESign\Models\Audit;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;
use NIIT\ESign\Models\SignerElement;

trait Auditable
{
    public function logAuditTrait(
        Document $document,
        string $event,
        ?Signer $signer = null,
        ?SignerElement $element = null,
        ?array $metadata = null,
        bool $hasUserStamps = true,
    ): Audit {
        $model = new Audit;

        if (! $hasUserStamps) {
            $model = $model->disableStamping();
        }

        $model->event = $event;
        $model->metadata = $metadata;
        $model->signer_id = $signer?->id;
        $model->element_id = $element?->id;
        $model->document_id = $document->id;

        $model->save();

        if (! $hasUserStamps) {
            $model = $model->enableStamping();
        }

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
