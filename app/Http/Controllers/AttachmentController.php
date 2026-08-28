<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachments\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Services\Attachments\ManagementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected readonly ManagementService $management,
    ) {}

    /**
     * Store a newly uploaded attachment.
     *
     * Validation (including MIME/size checks) is handled upstream by
     * StoreAttachmentRequest.
     */
    public function store(StoreAttachmentRequest $request): JsonResponse|RedirectResponse
    {
        $attachment = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($attachment, 201);
        }

        return redirect()->back();
    }

    /**
     * Download an attachment's file.
     *
     * The file is streamed from the private disk — never a public URL —
     * and gated by the 'download' policy on every request.
     */
    public function download(Attachment $attachment): StreamedResponse
    {
        $this->authorize('download', $attachment);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk(Attachment::DISK);

        return $disk->download(
            $attachment->disk_path,
            $attachment->original_filename,
        );
    }

    /**
     * Soft delete an attachment.
     */
    public function destroy(Attachment $attachment): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $attachment);

        $this->management->destroy($attachment, request()->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->back();
    }
}
