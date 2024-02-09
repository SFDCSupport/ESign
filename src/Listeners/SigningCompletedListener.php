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
                $config['level'],
                $config['info']
            );

            [$outputFileName, $outputPath] = $document->getSignedDocumentPath(true);

            $outputPdf = $pdf->Output($outputFileName, 'S');
            $disk->put(
                $outputPath,
                $outputPdf
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
                new SignedByAllMail($document, $outputPath)
            );
        } catch (Exception $e) {
            throw $e;
        }
    }
}
