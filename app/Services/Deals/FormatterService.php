<?php

namespace App\Services\Deals;

use App\Models\Deal;

class FormatterService
{
    /**
     * Format a single deal with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Deal $deal): array
    {
        return [
            'id' => $deal->id,
            'title' => $deal->title,
            'description' => $deal->description,
            'pipeline_id' => $deal->pipeline_id,
            'stage_id' => $deal->stage_id,
            'status_id' => $deal->status_id,
            'company_id' => $deal->company_id,
            'invoice_id' => $deal->invoice_id,
            'value' => $deal->value,
            'currency' => $deal->currency,
            'probability' => $deal->probability,
            'expected_close_date' => $deal->expected_close_date,
            'closed_at' => $deal->closed_at,
            'meta' => $deal->meta,
            'created_at' => $deal->created_at,
            'updated_at' => $deal->updated_at,
            'deleted_at' => $deal->deleted_at,
            'restored_at' => $deal->restored_at,
            'created_by' => $deal->created_by,
            'updated_by' => $deal->updated_by,
            'deleted_by' => $deal->deleted_by,
            'restored_by' => $deal->restored_by,
            'pipeline' => $deal->pipeline ? ['id' => $deal->pipeline->id, 'title' => $deal->pipeline->title] : null,
            'stage' => $deal->stage ? [
                'id' => $deal->stage->id,
                'title' => $deal->stage->title,
                'background_colour' => $deal->stage->background_colour,
                'text_colour' => $deal->stage->text_colour,
            ] : null,
            'status' => $deal->status ? [
                'id' => $deal->status->id,
                'title' => $deal->status->title,
                'background_colour' => $deal->status->background_colour,
                'text_colour' => $deal->status->text_colour,
            ] : null,
            'company' => $deal->company ? ['id' => $deal->company->id, 'name' => $deal->company->name] : null,
            'invoice' => $deal->invoice ? ['id' => $deal->invoice->id, 'invoice_number' => $deal->invoice->invoice_number] : null,
            'creator' => $deal->creator ? ['id' => $deal->creator->id, 'name' => $deal->creator->name] : null,
            'updater' => $deal->updater ? ['id' => $deal->updater->id, 'name' => $deal->updater->name] : null,
            'deleter' => $deal->deleter ? ['id' => $deal->deleter->id, 'name' => $deal->deleter->name] : null,
            'restorer' => $deal->restorer ? ['id' => $deal->restorer->id, 'name' => $deal->restorer->name] : null,
        ];
    }
}
