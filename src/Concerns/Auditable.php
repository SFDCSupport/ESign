<?php

namespace NIIT\ESign\Concerns;

use App\Models\User;
use Illuminate\Support\Str;
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

        if ($document->parent_id) {
            $metadata = array_merge($metadata ?? [], [
                'parent_id' => $document->parent_id,
            ]);
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

    public function getAuditsByDocument(?Document $document = null)
    {
        return $this->getAuditBy([
            'document_id' => ($document ?? $this)->id,
        ]);
    }

    public function getAuditsBySigner(Signer|string $signer, ?Document $document = null)
    {
        return $this->getAuditBy([
            'document_id' => ($document ?? $this)->id,
            'signer_id' => $signer->id ?? $signer,
        ]);
    }

    public function getAuditsByCreator(User $user)
    {
        return $this->getAuditBy([
            'created_by' => $user->id,
        ]);
    }

    private function getAuditBy(array $conditions = [])
    {
        return Audit::when(! blank($conditions),
            static fn ($q) => $q->where($conditions)
        )->latest()->get()
            ->map(fn ($a) => [
                'executor' => Str::random(rand(3, 12)),
                'action' => $a->event,
                'time' => $a->created_at->format('D, d M Y H:i:s e'),
            ]);
    }
}
