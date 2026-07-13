<?php

use App\Models\Contact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Contact::withTrashed()
            ->whereNotNull('address')
            ->orWhereNotNull('city')
            ->orWhereNotNull('postal_code')
            ->orWhereNotNull('country')
            ->get()
            ->each(function (Contact $contact) {
                DB::table('addresses')->insert([
                    'addressable_type' => Contact::class,
                    'addressable_id' => $contact->id,
                    'address_line_one' => $contact->address ?? 'Not provided',
                    'address_line_two' => null,
                    'town' => null,
                    'city' => $contact->city ?? 'Not provided',
                    'county' => null,
                    'postcode' => $contact->postal_code,
                    'country' => $contact->country ?? 'Not provided',
                    'is_primary' => true,
                    'meta' => null,
                    'created_by' => $contact->created_by,
                    'updated_by' => $contact->updated_by,
                    'created_at' => $contact->created_at,
                    'updated_at' => now(),
                ]);
            });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['country']);
            $table->dropColumn(['address', 'city', 'postal_code', 'country']);
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->index('country');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
        });

        DB::table('addresses')
            ->where('addressable_type', Contact::class)
            ->get()
            ->each(function ($address) {
                DB::table('contacts')->where('id', $address->addressable_id)->update([
                    'address' => $address->address_line_one,
                    'city' => $address->city,
                    'postal_code' => $address->postcode,
                    'country' => $address->country,
                ]);
            });

        DB::table('addresses')->where('addressable_type', Contact::class)->delete();
    }
};
