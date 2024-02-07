<?php

namespace NIIT\ESign\Http\Controllers;

use App\Actions\FilepondAction;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use NIIT\ESign\Enum\ReadStatus;
use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\Events\ReadStatusChanged;
use NIIT\ESign\Events\SigningProcessStarted;
use NIIT\ESign\Events\SigningStatusChanged;
use NIIT\ESign\Http\Requests\SigningRequest;
use NIIT\ESign\Http\Resources\SignerResource;
use NIIT\ESign\Models\Signer;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class SigningController extends Controller
{
    public function index(Signer $signer)
    {
        $document = $signer->loadMissing('document.document', 'elements')->document;

        SigningProcessStarted::dispatch(
            $document,
            $signer
        );

        $formattedData = [
            'signers' => SignerResource::collection([$signer]),
        ];

        return view('esign::signing.index', compact(
            'signer',
            'document',
            'formattedData',
        ));
    }

    public function store(SigningRequest $request, Signer $signer)
    {
        $storage = FilepondAction::getDisk();
        $disk = FilepondAction::getDisk(true);
        $loadedSigner = $signer->loadMissing('document.document');
        $signerUploadPath = signerUploadPath($signer);

        $data = collect($request->validated()['element'])->map(function ($d) use ($loadedSigner, $disk, $signerUploadPath) {
            $isSignaturePad = $d['type'] === 'signature_pad';

            $data = $d['data'];

            if ($isSignaturePad) {
                /** @var UploadedFile $file */
                $file = $d['data'];
                $fileName = $d['id'].'_'.trim($originalFileName = $file->getClientOriginalName());

                $filePath = $file->storeAs(
                    $signerUploadPath,
                    $fileName.'.png',
                    $disk
                );

                $loadedSigner->elements()->where('id', $d['id'])->first()->update([
                    'data' => json_encode([
                        'file_path' => $filePath,
                        'file_name' => $originalFileName,
                        'disk' => $disk,
                        'saved_at' => now(),
                    ]),
                ]);

                $data = FilepondAction::loadFile($filePath, 'view');
            }

            return collect([
                'pageIndex' => $d['page_index'],
                'pageWidth' => $d['page_width'],
                'pageHeight' => $d['page_height'],
                'top' => $d['top'],
                'left' => $d['left'],
                'width' => $d['width'],
                'height' => $d['height'],
            ])->when($isSignaturePad, fn ($c) => $c->merge([
                'data' => $data,
                'type' => 'image',
            ]), fn ($c) => $c->merge([
                'type' => 'text',
                'data' => $d['data'],
                'size' => $d['size'] ?? 16,
                'color' => $d['color'] ?? '0',
            ]));
        });

        $fileName = $loadedSigner->document_id.'.pdf';

        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile(StreamReader::createByString($storage->get($loadedSigner->document->document->path)));

        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $templateId = $pdf->importPage($pageNumber);
            $pdf->addPage();
            $pdf->useTemplate($templateId);

            if ($data->where('pageIndex', $pageNumber)->isNotEmpty()) {
                [$pageWidth, $pageHeight] = $pdf->getTemplateSize($templateId);

                $data->where('pageIndex', $pageNumber)->where('type', 'image')->each(function ($d) use ($pdf, $pageWidth, $pageHeight) {
                    $scaleX = $pageWidth / $d['pageWidth'];
                    $scaleY = $pageHeight / $d['pageHeight'];
                    $pdfLeft = $d['left'] * $scaleX;
                    $pdfTop = $d['top'] * $scaleY;
                    $width = $d['width'] * $scaleX;
                    $height = $d['height'] * $scaleY;

                    $pdf->Image(
                        $d['data'],
                        $pdfTop,
                        $pdfLeft,
                        $width,
                        $height
                    );
                });

                $data->where('pageIndex', $pageNumber)->where('type', '!=', 'signature_pad')->each(function ($d) use ($pdf, $pageWidth, $pageHeight) {
                    $scaleX = $pageWidth / $d['pageWidth'];
                    $scaleY = $pageHeight / $d['pageHeight'];
                    $width = $d['width'] * $scaleX;
                    $height = $d['height'] * $scaleY;
                    $pdfLeft = $d['left'] * $scaleX;
                    $pdfTop = $d['top'] * $scaleY;
                    $fontSize = min($width / $d['pageWidth'] * $pageWidth, $height / $d['pageHeight'] * $pageHeight);

                    $pdf->SetFont('Arial', '', $fontSize);
                    $pdf->SetTextColor($d['color'] ?? '000', '000', '000');
                    $pdf->SetXY($pdfTop, $pdfLeft);

                    if ($d['type'] === 'textarea') {
                        $pdf->MultiCell($width, $height, $d['data'], align: 'L');
                    } else {
                        $pdf->Cell($width, $height, $d['data'], ln: 1, align: 'L');
                    }
                });
            }
        }

        $documentContent = $pdf->Output('S', $fileName);
        $storage->put($signerUploadPath.'/'.$fileName, $documentContent);

        SigningStatusChanged::dispatch(
            $loadedSigner->document,
            $signer,
            SigningStatus::SIGNED
        );

        return $this->jsonResponse([
            'status' => 1,
            'redirect' => $signer->signingUrl().'/show',
        ])->notify(__('esign::label.signing_success_message'));
    }

    public function show(Request $request, Signer $signer)
    {
        /** @var Filesystem $disk */
        $disk = FilepondAction::getDisk();

        $document = $signer->loadMissing('document.document', 'elements')->document;
        $signedDocument = signerUploadPath($signer).'/'.$signer->document_id.'.pdf';

        abort_if(! $disk->exists($signedDocument), 404);

        $signedDocumentUrl = FilepondAction::loadFile($signedDocument, 'view');
        $formattedData = [];

        return view('esign::signing.show', compact(
            'signedDocument',
            'signedDocumentUrl',
            'signer',
            'document',
            'formattedData',
        ));
    }

    public function mailTrackingPixel(Signer $signer)
    {
        $pixel = sprintf('%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c', 71, 73, 70, 56, 57, 97, 1, 0, 1, 0, 128, 255, 0, 192, 192, 192, 0, 0, 0, 33, 249, 4, 1, 0, 0, 0, 0, 44, 0, 0, 0, 0, 1, 0, 1, 0, 0, 2, 2, 68, 1, 0, 59);

        $response = response($pixel, 200)
            ->header('Content-type', 'image/gif')
            ->header('Content-Length', 42)
            ->header('Cache-Control', 'private, no-cache, no-cache=Set-Cookie, proxy-revalidate')
            ->header('Expires', 'Wed, 11 Jan 2000 12:59:00 GMT')
            ->header('Last-Modified', 'Wed, 11 Jan 2006 12:59:00 GMT')
            ->header('Pragma', 'no-cache');

        if ($signer->read_status !== ReadStatus::OPENED) {
            ReadStatusChanged::dispatch($signer, ReadStatus::OPENED);
        }

        return $response;
    }
}
