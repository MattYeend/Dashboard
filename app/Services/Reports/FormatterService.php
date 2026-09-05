<?php

namespace App\Services\Reports;

use App\Models\Report;
use App\Models\User;

class FormatterService
{
    /**
     * Inject the required services into the formatter service.
     */
    public function __construct(
        private readonly ReportTypeRegistryService $registry,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function format(Report $report, ?User $viewer = null): array
    {
        return [
            'id' => $report->id,
            'title' => $report->title,
            'description' => $report->description,
            'type' => $report->type,
            'type_label' => $this->registry->labelForKey($report->type),
            'format' => $report->format,
            'filters' => $report->filters,
            'is_scheduled' => $report->is_scheduled,
            'schedule_frequency' => $report->schedule_frequency,
            'schedule_time' => $report->schedule_time,
            'recipients' => $report->recipients,
            'last_run_at' => $report->last_run_at,
            'next_run_at' => $report->next_run_at,
            'meta' => $report->meta,
            'created_at' => $report->created_at,
            'updated_at' => $report->updated_at,
            'deleted_at' => $report->deleted_at,
            'restored_at' => $report->restored_at,
            'created_by' => $report->created_by,
            'updated_by' => $report->updated_by,
            'deleted_by' => $report->deleted_by,
            'restored_by' => $report->restored_by,
            'creator' => $report->creator ? ['id' => $report->creator->id, 'name' => $report->creator->name] : null,
            'updater' => $report->updater ? ['id' => $report->updater->id, 'name' => $report->updater->name] : null,
            'deleter' => $report->deleter ? ['id' => $report->deleter->id, 'name' => $report->deleter->name] : null,
            'restorer' => $report->restorer ? ['id' => $report->restorer->id, 'name' => $report->restorer->name] : null,
            'can_update' => $viewer ? $viewer->can('update', $report) : false,
            'can_delete' => $viewer ? $viewer->can('delete', $report) : false,
            'can_schedule' => $viewer ? $viewer->can('schedule', Report::class) : false,
        ];
    }
}
