<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Media\CreateUploadSignature;
use App\Data\Media\CreateUploadSignatureData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\CreateUploadSignatureRequest;
use Illuminate\Http\JsonResponse;

final class MediaSignatureController extends Controller
{
    /**
     * Generate restrictive signed upload parameters for direct media upload.
     */
    public function __invoke(
        CreateUploadSignatureRequest $request,
        CreateUploadSignature $createUploadSignature,
    ): JsonResponse {
        $signedUpload = $createUploadSignature->handle(
            CreateUploadSignatureData::fromRequest($request)
        );

        return response()->json([
            'data' => $signedUpload->toArray(),
        ]);
    }
}
