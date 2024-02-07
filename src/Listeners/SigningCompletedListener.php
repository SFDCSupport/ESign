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
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\Tcpdf\Fpdi;

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
        $documentPath = $document->document->path;
        $documentFileName = $document->document->file_name;

        try {
            $pdf = new Fpdi();
            $pdf->setSourceFile(
                StreamReader::createByString(
                    $disk->get($documentPath)
                )
            );

            $pdf->setSignature(
                file_get_contents($config['path']),
                file_get_contents($config['private_key_path']),
                $config['password'],
                '',
                2,
                $config['info']
            );

            $outputFileName = (
                pathinfo($documentFileName, PATHINFO_FILENAME).
                '_signed.'.
                pathinfo($documentFileName, PATHINFO_EXTENSION)
            );
            $outputPdf = $pdf->Output('S', $outputFileName);
            $disk->put(
                (
                    esignUploadPath('document', ['id' => $document->id]).
                    $outputFileName
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
        } catch (Exception $e) {
            throw $e;
        }
    }
}
