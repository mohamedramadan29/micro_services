<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Columns the code writes but no migration ever created (they were probably
 * added by hand on the server), and NOT NULL columns that some flows never
 * fill. Every step checks first, so it is safe on a database that already has
 * them.
 */
return new class extends Migration
{
    /** table => [column => definition] */
    private function columns(): array
    {
        return [
            'products' => [
                'type' => fn (Blueprint $t) => $t->string('type', 20)->default('physical'),
                'digital_file' => fn (Blueprint $t) => $t->string('digital_file')->nullable(),
                'video' => fn (Blueprint $t) => $t->string('video')->nullable(),
            ],
            'product_orders' => [
                'phone' => fn (Blueprint $t) => $t->string('phone', 30)->nullable(),
            ],
            'order_details' => [
                'quantity' => fn (Blueprint $t) => $t->unsignedInteger('quantity')->default(1),
                'website_commission' => fn (Blueprint $t) => $t->decimal('website_commission', 12, 2)->default(0),
                'seller_commission' => fn (Blueprint $t) => $t->decimal('seller_commission', 12, 2)->default(0),
            ],
            'carts' => [
                'service_name' => fn (Blueprint $t) => $t->string('service_name')->nullable(),
            ],
            'courses' => [
                'slug' => fn (Blueprint $t) => $t->string('slug')->nullable(),
            ],
            'jobs' => [
                'slug' => fn (Blueprint $t) => $t->string('slug')->nullable(),
            ],
            'packages' => [
                // Plan group (from package_titles), used to group plans on the packages page.
                'title' => fn (Blueprint $t) => $t->string('title')->nullable(),
            ],
            'conversations' => [
                'chat_type' => fn (Blueprint $t) => $t->string('chat_type', 50)->nullable(),
                'consultant_id' => fn (Blueprint $t) => $t->unsignedBigInteger('consultant_id')->nullable(),
                'specialist_id' => fn (Blueprint $t) => $t->unsignedBigInteger('specialist_id')->nullable(),
            ],
            'with_draws' => [
                'country' => fn (Blueprint $t) => $t->string('country')->nullable(),
                'account_name' => fn (Blueprint $t) => $t->string('account_name')->nullable(),
                'account_number' => fn (Blueprint $t) => $t->string('account_number')->nullable(),
                'bank_name' => fn (Blueprint $t) => $t->string('bank_name')->nullable(),
                'iban_code' => fn (Blueprint $t) => $t->string('iban_code')->nullable(),
                'swift_code' => fn (Blueprint $t) => $t->string('swift_code')->nullable(),
            ],
            'users' => [
                'google_token' => fn (Blueprint $t) => $t->text('google_token')->nullable(),
            ],
        ];
    }

    /** Existing NOT NULL columns that some flows leave empty. */
    private function nullable(): array
    {
        return [
            // Digital products have no shipping address.
            'product_orders' => [
                'country' => fn (Blueprint $t) => $t->string('country')->nullable()->change(),
                'city' => fn (Blueprint $t) => $t->string('city')->nullable()->change(),
                'address' => fn (Blueprint $t) => $t->string('address')->nullable()->change(),
            ],
            // Bank withdrawals have no PayPal address.
            'with_draws' => [
                'paypal_mail' => fn (Blueprint $t) => $t->string('paypal_mail')->nullable()->change(),
            ],
            // Consultation chats are not about a service.
            'conversations' => [
                'service_id' => fn (Blueprint $t) => $t->integer('service_id')->nullable()->change(),
            ],
            // A cart order can hold services of several sellers (sellers are on order_details).
            'orders' => [
                'user_seller' => fn (Blueprint $t) => $t->integer('user_seller')->nullable()->change(),
                'seller_commission' => fn (Blueprint $t) => $t->string('seller_commission')->nullable()->change(),
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->columns() as $table => $columns) {
            $add = array_filter($columns, fn ($column) => ! Schema::hasColumn($table, $column), ARRAY_FILTER_USE_KEY);
            if ($add) {
                Schema::table($table, function (Blueprint $t) use ($add) {
                    foreach ($add as $define) {
                        $define($t);
                    }
                });
            }
        }

        foreach ($this->nullable() as $table => $columns) {
            Schema::table($table, function (Blueprint $t) use ($table, $columns) {
                foreach ($columns as $column => $define) {
                    if (Schema::hasColumn($table, $column)) {
                        $define($t);
                    }
                }
            });
        }

        // Course and job pages are addressed by slug.
        foreach (['courses', 'jobs'] as $table) {
            DB::table($table)->whereNull('slug')->orderBy('id')->select('id', 'title')->chunkById(200, function ($rows) use ($table) {
                foreach ($rows as $row) {
                    DB::table($table)->where('id', $row->id)->update(['slug' => self::slug((string) $row->title) ?: (string) $row->id]);
                }
            });
        }
    }

    public function down(): void
    {
        // Nothing to undo safely: on the server these columns may have existed
        // before this migration, and dropping them would lose data.
    }

    /** Same rule as App\Http\Traits\Slug_Trait::CustomeSlug (keeps Arabic). */
    private static function slug(string $text): string
    {
        $text = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s]/u', '-', $text);
        $text = trim(preg_replace('/-+/', '-', $text), '-');

        return mb_strtolower(str_replace(' ', '-', $text), 'UTF-8');
    }
};
