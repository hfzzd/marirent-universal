<?php

use App\Support\Phone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cegah data identitas duplikat (email/no.HP dll) masuk database:
 * 1. Normalisasi nilai phone & email yang sudah ada (format 62xx…).
 * 2. Dedupe (keep id terkecil; sisanya dikosongkan / dihapus bila kolom NOT NULL).
 * 3. Tambah index UNIQUE pada kolom identitas rawan duplikat.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeAndDedupePhone('users', 'phone');
        $this->normalizeAndDedupePhone('merchants', 'phone');
        $this->normalizeAndDedupePhone('companies', 'phone');
        $this->normalizeAndDedupePhone('demo_requests', 'phone');

        $this->normalizeAndDedupeNullable('merchants', 'company_email', fn ($v) => strtolower(trim($v)));
        $this->normalizeAndDedupeNullable('companies', 'company_email', fn ($v) => strtolower(trim($v)));
        $this->normalizeAndDedupeNullable('drivers', 'license_number', fn ($v) => trim($v));

        $this->dedupeDelete('demo_requests', 'email', fn ($v) => strtolower(trim($v)));
        $this->dedupeKeepOneRowPerGroup('drivers', 'user_id');

        Schema::table('users', function (Blueprint $table) {
            $table->unique('phone', 'users_phone_unique');
        });
        Schema::table('merchants', function (Blueprint $table) {
            $table->unique('phone', 'merchants_phone_unique');
            $table->unique('company_email', 'merchants_company_email_unique');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->unique('phone', 'companies_phone_unique');
            $table->unique('company_email', 'companies_company_email_unique');
        });
        Schema::table('drivers', function (Blueprint $table) {
            $table->unique('license_number', 'drivers_license_number_unique');
            $table->unique('user_id', 'drivers_user_id_unique');
        });
        Schema::table('demo_requests', function (Blueprint $table) {
            $table->unique('email', 'demo_requests_email_unique');
            $table->unique('phone', 'demo_requests_phone_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropUnique('users_phone_unique'));
        Schema::table('merchants', function (Blueprint $t) {
            $t->dropUnique('merchants_phone_unique');
            $t->dropUnique('merchants_company_email_unique');
        });
        Schema::table('companies', function (Blueprint $t) {
            $t->dropUnique('companies_phone_unique');
            $t->dropUnique('companies_company_email_unique');
        });
        Schema::table('drivers', function (Blueprint $t) {
            $t->dropUnique('drivers_license_number_unique');
            $t->dropUnique('drivers_user_id_unique');
        });
        Schema::table('demo_requests', function (Blueprint $t) {
            $t->dropUnique('demo_requests_email_unique');
            $t->dropUnique('demo_requests_phone_unique');
        });
    }

    /** Normalisasi phone lalu NULL-kan nilai duplikat (keep id terkecil). */
    private function normalizeAndDedupePhone(string $table, string $column): void
    {
        $rows = DB::table($table)->whereNotNull($column)->orderBy('id')->get(['id', $column]);
        $seen = [];
        foreach ($rows as $row) {
            $normalized = Phone::normalize($row->{$column});
            if ($normalized === null) {
                continue;
            }
            if (isset($seen[$normalized])) {
                DB::table($table)->where('id', $row->id)->update([$column => null]);
            } else {
                $seen[$normalized] = true;
                DB::table($table)->where('id', $row->id)->update([$column => $normalized]);
            }
        }
    }

    /** Normalisasi nilai nullable (email/license) lalu NULL-kan duplikat. */
    private function normalizeAndDedupeNullable(string $table, string $column, callable $transform): void
    {
        $rows = DB::table($table)->whereNotNull($column)->orderBy('id')->get(['id', $column]);
        $seen = [];
        foreach ($rows as $row) {
            $value = $transform($row->{$column});
            if ($value === '' || $value === null) {
                continue;
            }
            if (isset($seen[$value])) {
                DB::table($table)->where('id', $row->id)->update([$column => null]);
            } else {
                $seen[$value] = true;
                DB::table($table)->where('id', $row->id)->update([$column => $value]);
            }
        }
    }

    /** Kolom NOT NULL: hapus baris duplikat (keep id terkecil). */
    private function dedupeDelete(string $table, string $column, callable $transform): void
    {
        $rows = DB::table($table)->orderBy('id')->get(['id', $column]);
        $seen = [];
        foreach ($rows as $row) {
            $value = $transform($row->{$column});
            if (isset($seen[$value])) {
                DB::table($table)->where('id', $row->id)->delete();
            } else {
                $seen[$value] = true;
            }
        }
    }

    /** Satu baris per kunci (keep id terkecil), hapus sisanya. */
    private function dedupeKeepOneRowPerGroup(string $table, string $column): void
    {
        $rows = DB::table($table)->select($column, DB::raw('MIN(id) as keep_id'))
            ->groupBy($column)
            ->get();
        foreach ($rows as $group) {
            DB::table($table)
                ->where($column, $group->{ $column })
                ->where('id', '!=', $group->keep_id)
                ->delete();
        }
    }
};