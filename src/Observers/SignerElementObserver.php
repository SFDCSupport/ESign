<?php

namespace NIIT\ESign\Observers;

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

    private function missingRequiredFields(Element $element, array $fields): bool
    {
        foreach ($fields as $field) {
            if (blank($element->$field)) {
                return true;
            }
        }

        return false;
    }

    public function created(Element $element): void
    {
        $signer = $this->getRelations($element);

        $this->logAuditTrait(
            document: $signer->document,
            event: 'element-added',
            signer: $signer,
            element: $element
        );
    }

    protected function getRelations(Element $element): Signer
    {
        return $element->loadMissing('signer.document')->signer;
    }

    public function updated(Element $element): void
    {
        $signer = $this->getRelations($element);

        $this->logAuditTrait(
            document: $signer->document,
            event: 'element-updated',
            signer: $signer,
            element: $element,
            metadata: $element->getDirty()
        );
    }

    public function deleted(Element $element): void
    {
        $signer = $this->getRelations($element);

        $this->logAuditTrait(
            document: $signer->document,
            event: 'element-deleted',
            signer: $signer,
            element: $element
        );
    }

    public function restored(Element $element): void
    {
        $signer = $this->getRelations($element);

        $this->logAuditTrait(
            document: $signer->document,
            event: 'element-restored',
            signer: $signer,
            element: $element
        );
    }

    public function forceDeleted(Element $element): void
    {
        $signer = $this->getRelations($element);

        $this->logAuditTrait(
            document: $signer->document,
            event: 'element-force-deleted',
            signer: $signer,
            element: $element
        );
    }
}
