<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
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
        ]);
    }
}
