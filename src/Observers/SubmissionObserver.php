<?php
/*
 * @author : Anand Pilania
 * @mailto : Anand.Pilania@niit.com
 * @updated : 1/25/24, 11:35 AM
 */

namespace NIIT\ESign\Observers;

use NIIT\ESign\Models\Submission;

class SubmissionObserver extends Observer
{
    public function created(Submission $submission): void
    {
        $this->logAuditTrait(
            $submission->document,
            'submission-added',
            $submission->signer,
            $submission->element
        );
    }

    public function updated(Submission $submission): void
    {
        $this->logAuditTrait(
            document: $submission->document,
            event: 'submission-updated',
            signer: $submission->signer,
            element: $submission->element,
            metadata: $submission->getDirty()
        );
    }

    public function deleted(Submission $submission): void
    {
        $this->logAuditTrait(
            document: $submission->document,
            event: 'submission-deleted',
            signer: $submission->signer,
            element: $submission->element
        );
    }

    public function restored(Submission $submission): void
    {
        $this->logAuditTrait(
            document: $submission->document,
            event: 'submission-restored',
            signer: $submission->signer,
            element: $submission->element
        );
    }

    public function forceDeleted(Submission $submission): void
    {
        $this->logAuditTrait(
            document: $submission->document,
            event: 'submission-force-deleted',
            signer: $submission->signer,
            element: $submission->element
        );
    }
}
