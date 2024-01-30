<?php

namespace NIIT\ESign\Http\Controllers;

use App\Actions\FilepondAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        SigningProcessStarted::dispatch($signer);

        $document = $signer->loadMissing('document.document', 'elements')->document;
        $formattedData = ['signers' => [json_decode((new SignerResource($signer))->toJson(), true)]];

        return view('esign::index', compact(
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

        config(['filesystems.disks.esign_temp' => [
            'driver' => 'local',
            'root' => storage_path('app/esign_temp/'.$loadedSigner->id),
            'throw' => false,
        ]]);

        $tempDisk = Storage::disk('esign_temp');

        $data = collect($request->validated()['element'])->map(function ($d) use ($loadedSigner, $disk) {
            $isSignaturePad = $d['type'] === 'signature_pad';

            $data = $d['data'];

            if ($isSignaturePad) {
                /** @var UploadedFile $file */
                $file = $d['data'];
                $fileName = $d['id'].'_'.trim($originalFileName = $file->getClientOriginalName());

                $filePath = $file->storeAs(
                    esignUploadPath('signer', [
                        'id' => $loadedSigner->document->id,
                        'signer' => $loadedSigner->id,
                    ]),
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
                'page' => $d['on_page'],
                'x' => $d['top'],
                'y' => $d['left'],
                'bottom' => $d['bottom'],
            ])->when($isSignaturePad, fn ($c) => $c->merge([
                'width' => $d['width'],
                'height' => $d['height'],
                'path' => $data,
                'type' => 'image',
            ]), fn ($c) => $c->merge([
                'type' => 'text',
                'content' => $data,
                'size' => $d['size'] ?? 16,
                'color' => $d['color'] ?? '0',
            ]));
        });

        $tempPath = $loadedSigner->document->id.'.pdf';

        if (! $tempDisk->fileExists($tempPath)) {
            $tempDisk->put(
                $tempPath,
                $storage->get($loadedSigner->document->document->path)
            );
        }

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile(StreamReader::createByString($tempDisk->get($tempPath)));

        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $templateId = $pdf->importPage($pageNumber);
            $pdf->addPage();
            $pdf->useTemplate($templateId, 0, 0);

            if ($data->where('page', $pageNumber)->isNotEmpty()) {
                $data->where('page', $pageNumber)->where('type', 'image')->each(function ($d) use ($pdf) {
                    $d['y'] = $pdf->GetPageHeight() - $d['y'];

                    $pdf->Image(
                        $d['path'],
                        $d['x'],
                        $d['y'],
                        $d['width'],
                        $d['height']
                    );
                });

                $data->where('page', $pageNumber)->where('type', 'text')->each(function ($d) use ($pdf) {
                    $d['y'] = $pdf->GetPageHeight() - $d['y'];
                    $bottom = $pdf->GetPageHeight() - $d['bottom'];

                    $pdf->SetFont('Arial', '', $d['size']);
                    $pdf->SetTextColor($d['color']);
                    $pdf->SetXY($d['x'], $bottom);
                    $pdf->Cell(0, 10, $d['content'], 0, 1);
                });
            }
        }

        $pdf->Output('F', config('filesystems.disks.esign_temp.root').'/'.$tempPath);

        SigningStatusChanged::dispatch($signer, SigningStatus::SIGNED);

        return $this->jsonResponse([
            'status' => 1,
        ])->notify(__('esign::label.signing_success_message'));
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

        ReadStatusChanged::dispatch($signer, ReadStatus::OPENED);

        return $response;
    }
}
