<?php

declare(strict_types=1);

namespace App\Http\Requests\PipelineStages;

use App\Http\Requests\BulkIdsRequest;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Validation\Validator;

class BulkPipelineStageIdsRequest extends BulkIdsRequest
{
    /**
     * Reject any stage ID that belongs to a different pipeline.
     *
     * Unknown IDs still pass, matching the previous closure behaviour.
     * One query is used instead of one per ID.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            // Skip the DB check if basic validation already failed.
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Pipeline $pipeline */
            $pipeline = $this->route('pipeline');
            $ids = $this->input('ids');

            $foreign = PipelineStage::withTrashed()
                ->whereIn('id', $ids)
                ->where('pipeline_id', '!=', $pipeline->id)
                ->pluck('id')
                ->all();

            foreach ($ids as $index => $id) {
                if (in_array((int) $id, $foreign, true)) {
                    $validator->errors()->add(
                        "ids.$index",
                        "The selected ids.$index is invalid."
                    );
                }
            }
        });
    }
}
