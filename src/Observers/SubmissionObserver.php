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
        $this->logAuditTrait($submission->document, 'submission-added', $submission->signer, $submission->element);
    }

    public function updated(Submission $submission): void
    {
        $this->logAuditTrait($submission->document, 'submission-updated', $submission->signer, $submission->element, $submission->getDirty());
    }

    public function deleted(Submission $submission): void
    {
        $this->logAuditTrait($submission->document, 'submission-deleted', $submission->signer, $submission->element);
    }

    public function restored(Submission $submission): void
    {
        $this->logAuditTrait($submission->document, 'submission-restored', $submission->signer, $submission->element);
    }

    public function forceDeleted(Submission $submission): void
    {
        $this->logAuditTrait($submission->document, 'submission-force-deleted', $submission->signer, $submission->element);
    }
}
