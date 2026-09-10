<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private function itemTables(): array
    {
        return [
            'vehicles',
            'phones',
            'cameras',
            'camping_equipments',
            'playstations',
            'drones',
            'musical_instruments',
        ];
    }

    public function up(): void
    {
        DB::transaction(function () {
            $owners = DB::table('users')->where('role', 'owner')->get();

            foreach ($owners as $owner) {
                $company = DB::table('companies')->where('user_id', $owner->id)->first();
                if ($company) {
                    if ($company->deleted_at !== null) {
                        DB::table('companies')->where('id', $company->id)->update([
                            'deleted_at' => null,
                            'updated_at' => now(),
                        ]);
                    }
                    continue;
                }

                $merchant = DB::table('merchants')->where('user_id', $owner->id)->first();
                $name = $merchant?->name ?: ('Company ' . $owner->name);
                $slugBase = Str::slug($merchant?->slug ?: $name) ?: ('company-' . $owner->id);
                $slug = $slugBase;
                $suffix = 1;

                while (DB::table('companies')->where('slug', $slug)->exists()) {
                    $slug = $slugBase . '-' . $suffix++;
                }

                $now = now();
                DB::table('companies')->insert([
                    'user_id' => $owner->id,
                    'slug' => $slug,
                    'name' => $name,
                    'description' => $merchant?->description,
                    'logo' => $merchant?->logo,
                    'banner' => $merchant?->banner,
                    'phone' => $merchant?->phone ?: $owner->phone,
                    'company_email' => $merchant?->company_email ?: $owner->email,
                    'website' => $merchant?->website,
                    'instagram' => $merchant?->instagram,
                    'address' => $merchant?->address,
                    'city' => $merchant?->city,
                    'pickup_address' => $merchant?->pickup_address,
                    'operational_hours' => $merchant?->operational_hours,
                    'commission_rate' => $merchant?->commission_rate ?? 0,
                    'is_active' => $merchant?->is_active ?? true,
                    'status' => $merchant?->status ?: 'pending',
                    'verified_at' => $merchant?->verified_at,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($this->itemTables() as $table) {
                DB::statement("\n                    UPDATE {$table} i\n                    JOIN users u ON u.id = i.owner_id\n                    JOIN companies c ON c.user_id = CASE\n                        WHEN u.role = 'admin' AND u.owner_id IS NOT NULL THEN u.owner_id\n                        ELSE u.id\n                    END\n                    SET i.company_id = c.id\n                    WHERE i.company_id IS NULL OR i.company_id <> c.id\n                ");
            }

            DB::statement("\n                UPDATE drivers d\n                JOIN users u ON u.id = d.owner_id\n                JOIN companies c ON c.user_id = CASE\n                    WHEN u.role = 'admin' AND u.owner_id IS NOT NULL THEN u.owner_id\n                    ELSE u.id\n                END\n                SET d.company_id = c.id\n                WHERE d.company_id IS NULL OR d.company_id <> c.id\n            ");
        });
    }

    public function down(): void
    {
        // Backfilled records are intentionally retained when this migration is rolled back.
    }
};
