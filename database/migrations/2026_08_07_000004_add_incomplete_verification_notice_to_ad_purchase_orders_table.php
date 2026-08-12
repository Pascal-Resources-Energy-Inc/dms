<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIncompleteVerificationNoticeToAdPurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('ad_purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('ad_purchase_orders', 'verification_incomplete_remarks')) {
                $table->text('verification_incomplete_remarks')->nullable()->after('verification_items');
            }
            if (!Schema::hasColumn('ad_purchase_orders', 'verification_incomplete_notified_at')) {
                $table->timestamp('verification_incomplete_notified_at')->nullable()->after('verification_incomplete_remarks');
            }
            if (!Schema::hasColumn('ad_purchase_orders', 'verification_incomplete_notified_by')) {
                $table->unsignedBigInteger('verification_incomplete_notified_by')->nullable()->after('verification_incomplete_notified_at');
            }
        });
    }

    public function down()
    {
        Schema::table('ad_purchase_orders', function (Blueprint $table) {
            $columns = [];
            foreach (['verification_incomplete_remarks', 'verification_incomplete_notified_at', 'verification_incomplete_notified_by'] as $column) {
                if (Schema::hasColumn('ad_purchase_orders', $column)) {
                    $columns[] = $column;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
}
