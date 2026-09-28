<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeders that are safe to run in production.
     *
     * Reference data only. Never add seeders that create sample users,
     * customers, orders or other test records.
     */
    private const PRODUCTION_SEEDERS = [
        RolePermissionSeeder::class,
        TaskStatusSeeder::class,
        OrderStatusSeeder::class,
        InvoiceStatusSeeder::class,
        PipelineStatusSeeder::class,
        DealStatusSeeder::class,
        TicketPrioritySeeder::class,
        TicketStatusSeeder::class,
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            OrganisationSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,

            IndustrySeeder::class,
            CompanySeeder::class,
            ContactSeeder::class,
            AddressSeeder::class,

            TaskStatusSeeder::class,
            TaskSeeder::class,

            OrderStatusSeeder::class,
            OrderSeeder::class,

            PlanSeeder::class,

            CategorySeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
            TagSeeder::class,
            RegistrationInterestSeeder::class,

            InvoiceStatusSeeder::class,
            InvoiceSeeder::class,
            InvoiceItemSeeder::class,

            PipelineStatusSeeder::class,
            PipelineSeeder::class,
            PipelineStageSeeder::class,

            DealStatusSeeder::class,
            DealSeeder::class,

            TicketPrioritySeeder::class,
            TicketStatusSeeder::class,
            LabelSeeder::class,
            TicketSeeder::class,

            ActivitySeeder::class,
            InteractionLogSeeder::class,

            NotificationBroadcastSeeder::class,
            SettingSeeder::class,
            ReportSeeder::class,
        ];

        if (app()->environment('production', 'staging')) {
            $seeders = array_values(
                array_intersect($seeders, self::PRODUCTION_SEEDERS)
            );
        }

        $this->call($seeders);
    }
}
