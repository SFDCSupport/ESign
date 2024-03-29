<?php

namespace NIIT\ESign\Observers;

use NIIT\ESign\Enum\AuditEvent;
use NIIT\ESign\Models\Signer;
use NIIT\ESign\Models\SignerElement as Element;

class SignerElementObserver extends Observer
{
    public function creating(Element $element): void
    {
        if (
            blank($element->position) &&
            $this->missingRequiredFields($element, ['signer_id', 'document_id'])
        ) {
            $maxPriority = Element::where([
                'signer_id' => $signerId,
                'document_id' => $documentId,
            ])->max('position') ?? 0;

            $element->position = $maxPriority + 1;
        }
    }

    public function created(Element $element): void
    {
        $signer = $this->getRelations($element);

        $this->logAuditTrait(
            document: $signer->document,
            event: AuditEvent::ELEMENT_ADDED,
            signer: $signer,
            element: $element
        );
    }

    public function updated(Element $element): void
    {
        $signer = $this->getRelations($element);
        $dirty = array_diff_key(
            $element->getdirty(),
            array_flip([
                'updated_at',
                'updated_by',
            ])
        );

        $this->logAuditTrait(
            document: $signer->document,
            event: AuditEvent::ELEMENT_UPDATED,
            signer: $signer,
            element: $element,
            metadata: $dirty
        );
    }

    public function deleted(Element $element): void
    {
        $signer = $this->getRelations($element);

        $this->logAuditTrait(
            document: $signer->document,
            event: AuditEvent::ELEMENT_DELETED,
            signer: $signer,
            element: $element
        );
    }

    public function restored(Element $element): void
    {
        $signer = $this->getRelations($element);

        $this->logAuditTrait(
            document: $signer->document,
            event: AuditEvent::ELEMENT_RESTORED,
            signer: $signer,
            element: $element
        );
    }

    public function forceDeleted(Element $element): void
    {
        $signer = $this->getRelations($element);

        $this->logAuditTrait(
            document: $signer->document,
            event: AuditEvent::ELEMENT_DELETED_FORCE,
            signer: $signer,
            element: $element
        );
    }

    protected function getRelations(Element $element): Signer
    {
        return $element->loadMissing('signer.document')->signer;
    }

    private function missingRequiredFields(Element $element, array $fields): bool
    {
        foreach ($fields as $field) {
            if (blank($element->$field)) {
                return true;
            }
        }

        return false;
    }
}
