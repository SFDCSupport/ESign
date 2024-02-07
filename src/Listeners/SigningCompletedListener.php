<?php

namespace NIIT\ESign\Listeners;

use App\Actions\FilepondAction;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\ESignFacade;
use NIIT\ESign\Events\SigningProcessCompleted;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;
use SetaPDF_Core_Document;
use SetaPDF_Core_Exception;
use SetaPDF_Core_SecHandler_Exception;
use SetaPDF_Core_Writer_Var;
use SetaPDF_Signer;
use SetaPDF_Signer_Asn1_Exception;
use SetaPDF_Signer_Exception;
use SetaPDF_Signer_Signature_Module_Cms;

class SigningCompletedListener
{
    /**
     * @throws Exception|SetaPDF_Signer_Exception|SetaPDF_Core_SecHandler_Exception|SetaPDF_Core_Exception|SetaPDF_Signer_Asn1_Exception
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
        $documentPath = $document->document->path;
        $documentFileName = $document->document->file_name;
        $documentContent = $disk->get($documentPath);
        $outputPdf = null;

        try {
            $writer = new SetaPDF_Core_Writer_Var($outputPdf);
            $sDocument = SetaPDF_Core_Document::loadByString($documentContent, $writer);
            $signer = new SetaPDF_Signer($sDocument);

            $signer->setReason('Document signed by ESign.');
            $signer->setLocation('Imphal');
            $signer->setContactInfo('+91-700-58-12-123');

            $module = new SetaPDF_Signer_Signature_Module_Cms();
            $module->setCertificate(file_get_contents($config['path']));
            $module->setPrivateKey([file_get_contents($config['private_key_path']), $config['password']]);

            $signer->setCertificationLevel($config['level'] ?? SetaPDF_Signer::CERTIFICATION_LEVEL_NO_CHANGES_ALLOWED);
            $signer->sign($module);

            $disk->put(
                (
                    esignUploadPath('document', ['id' => $document->id]).
                    pathinfo($documentFileName, PATHINFO_FILENAME).
                    '_signed.'.
                    pathinfo($documentFileName, PATHINFO_EXTENSION)
                ),
                $outputPdf
            );

            $document->update([
                'is_signed' => true,
            ]);

            $document->logAuditTrait(
                document: $document,
                event: 'document-signed',
            );
        } catch (Exception|SetaPDF_Signer_Exception|SetaPDF_Core_SecHandler_Exception|SetaPDF_Core_Exception|SetaPDF_Signer_Asn1_Exception $e) {
            throw $e;
        }
    }
}
