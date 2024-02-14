<?php

namespace NIIT\ESign\Listeners;

use App\Actions\FilepondAction;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Mail;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\ESignFacade;
use NIIT\ESign\Events\SigningProcessCompleted;
use NIIT\ESign\Mail\SignedByAllMail;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class SigningCompletedListener
{
    /**
     * @throws Exception
     */
    public function handle(SigningProcessCompleted $event): void
    {
        /** @var Document $document */
        $document = $event->document;

        /** @var Signer $signer */
        $signer = $event->signer;

        $document->update([
            'status' => DocumentStatus::COMPLETED,
        ]);

        $document->logAuditTrait(
            document: $document,
            event: 'signing-completed',
            signer: $signer,
        );

        /** @var Filesystem $disk */
        $disk = FilepondAction::getDisk();

        $config = ESignFacade::config('certificate');
        $documentPath = $document->document->path.'/'.$document->document->file_name;

        try {
            $outputPdfFile = null;
            $writer = new \SetaPDF_Core_Writer_Var($outputPdfFile);
            $sDocument = \SetaPDF_Core_Document::loadByString($disk->get($documentPath), $writer);
            $signer = new \SetaPDF_Signer($sDocument);

            $signer->setReason('This document is fixed');
            $signer->setLocation('Imphal');
            $signer->setContactInfo('+91-600-530-92-42');

            $module = new \SetaPDF_Signer_Signature_Module_Cms();

            $module->setCertificate(file_get_contents($config['path']));
            $module->setPrivateKey([file_get_contents($config['private_key_path']), 'password']);

            $signer->setCertificationLevel($config['level']);

            $signer->sign($module);

            $disk->put(
                $documentPath,
                $outputPdfFile
            );

            $document->logAuditTrait(
                document: $document,
                event: 'document-signed',
            );

            Mail::to(
                $document->signers()
                    ->pluck('email')
                    ->merge([
                        $document->loadMissing('creator')
                            ->creator->email,
                    ])->toArray()
            )->send(
                new SignedByAllMail($document, $documentPath)
            );
        } catch (Exception $e) {
            throw $e;
        }
    }
}
