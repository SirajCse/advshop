<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_orders') && ! Schema::hasIndex('ec_orders', 'ec_orders_store_id_index')) {
            Schema::table('ec_orders', function (Blueprint $table): void {
                $table->index('store_id', 'ec_orders_store_id_index');
            });
        }

        if (Schema::hasTable('ec_discounts') && ! Schema::hasIndex('ec_discounts', 'ec_discounts_store_id_index')) {
            Schema::table('ec_discounts', function (Blueprint $table): void {
                $table->index('store_id', 'ec_discounts_store_id_index');
            });
        }

        if (Schema::hasTable('ec_customers')) {
            if (! Schema::hasIndex('ec_customers', 'ec_customers_is_vendor_index')) {
                Schema::table('ec_customers', function (Blueprint $table): void {
                    $table->index('is_vendor', 'ec_customers_is_vendor_index');
                });
            }

            if (! Schema::hasIndex('ec_customers', 'ec_customers_vendor_verified_at_index')) {
                Schema::table('ec_customers', function (Blueprint $table): void {
                    $table->index('vendor_verified_at', 'ec_customers_vendor_verified_at_index');
                });
            }
        }

        if (Schema::hasTable('mp_vendor_info') && ! Schema::hasIndex('mp_vendor_info', 'mp_vendor_info_customer_id_index')) {
            Schema::table('mp_vendor_info', function (Blueprint $table): void {
                $table->index('customer_id', 'mp_vendor_info_customer_id_index');
            });
        }

        if (Schema::hasTable('mp_customer_revenues')) {
            if (! Schema::hasIndex('mp_customer_revenues', 'mp_customer_revenues_customer_id_index')) {
                Schema::table('mp_customer_revenues', function (Blueprint $table): void {
                    $table->index('customer_id', 'mp_customer_revenues_customer_id_index');
                });
            }

            if (! Schema::hasIndex('mp_customer_revenues', 'mp_customer_revenues_order_id_index')) {
                Schema::table('mp_customer_revenues', function (Blueprint $table): void {
                    $table->index('order_id', 'mp_customer_revenues_order_id_index');
                });
            }
        }

        if (Schema::hasTable('mp_customer_withdrawals')) {
            if (! Schema::hasIndex('mp_customer_withdrawals', 'mp_customer_withdrawals_customer_id_index')) {
                Schema::table('mp_customer_withdrawals', function (Blueprint $table): void {
                    $table->index('customer_id', 'mp_customer_withdrawals_customer_id_index');
                });
            }

            if (! Schema::hasIndex('mp_customer_withdrawals', 'mp_customer_withdrawals_status_index')) {
                Schema::table('mp_customer_withdrawals', function (Blueprint $table): void {
                    $table->index('status', 'mp_customer_withdrawals_status_index');
                });
            }
        }

        if (Schema::hasTable('mp_stores')) {
            if (! Schema::hasIndex('mp_stores', 'mp_stores_customer_id_index')) {
                Schema::table('mp_stores', function (Blueprint $table): void {
                    $table->index('customer_id', 'mp_stores_customer_id_index');
                });
            }

            if (! Schema::hasIndex('mp_stores', 'mp_stores_status_index')) {
                Schema::table('mp_stores', function (Blueprint $table): void {
                    $table->index('status', 'mp_stores_status_index');
                });
            }
        }

        if (Schema::hasTable('ec_orders') && ! Schema::hasIndex('ec_orders', 'ec_orders_store_finished_index')) {
            Schema::table('ec_orders', function (Blueprint $table): void {
                $table->index(['store_id', 'is_finished'], 'ec_orders_store_finished_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ec_orders')) {
            if (Schema::hasIndex('ec_orders', 'ec_orders_store_id_index')) {
                Schema::table('ec_orders', function (Blueprint $table): void {
                    $table->dropIndex('ec_orders_store_id_index');
                });
            }

            if (Schema::hasIndex('ec_orders', 'ec_orders_store_finished_index')) {
                Schema::table('ec_orders', function (Blueprint $table): void {
                    $table->dropIndex('ec_orders_store_finished_index');
                });
            }
        }

        if (Schema::hasTable('ec_discounts') && Schema::hasIndex('ec_discounts', 'ec_discounts_store_id_index')) {
            Schema::table('ec_discounts', function (Blueprint $table): void {
                $table->dropIndex('ec_discounts_store_id_index');
            });
        }

        if (Schema::hasTable('ec_customers')) {
            if (Schema::hasIndex('ec_customers', 'ec_customers_is_vendor_index')) {
                Schema::table('ec_customers', function (Blueprint $table): void {
                    $table->dropIndex('ec_customers_is_vendor_index');
                });
            }

            if (Schema::hasIndex('ec_customers', 'ec_customers_vendor_verified_at_index')) {
                Schema::table('ec_customers', function (Blueprint $table): void {
                    $table->dropIndex('ec_customers_vendor_verified_at_index');
                });
            }
        }

        if (Schema::hasTable('mp_vendor_info') && Schema::hasIndex('mp_vendor_info', 'mp_vendor_info_customer_id_index')) {
            Schema::table('mp_vendor_info', function (Blueprint $table): void {
                $table->dropIndex('mp_vendor_info_customer_id_index');
            });
        }

        if (Schema::hasTable('mp_customer_revenues')) {
            if (Schema::hasIndex('mp_customer_revenues', 'mp_customer_revenues_customer_id_index')) {
                Schema::table('mp_customer_revenues', function (Blueprint $table): void {
                    $table->dropIndex('mp_customer_revenues_customer_id_index');
                });
            }

            if (Schema::hasIndex('mp_customer_revenues', 'mp_customer_revenues_order_id_index')) {
                Schema::table('mp_customer_revenues', function (Blueprint $table): void {
                    $table->dropIndex('mp_customer_revenues_order_id_index');
                });
            }
        }

        if (Schema::hasTable('mp_customer_withdrawals')) {
            if (Schema::hasIndex('mp_customer_withdrawals', 'mp_customer_withdrawals_customer_id_index')) {
                Schema::table('mp_customer_withdrawals', function (Blueprint $table): void {
                    $table->dropIndex('mp_customer_withdrawals_customer_id_index');
                });
            }

            if (Schema::hasIndex('mp_customer_withdrawals', 'mp_customer_withdrawals_status_index')) {
                Schema::table('mp_customer_withdrawals', function (Blueprint $table): void {
                    $table->dropIndex('mp_customer_withdrawals_status_index');
                });
            }
        }

        if (Schema::hasTable('mp_stores')) {
            if (Schema::hasIndex('mp_stores', 'mp_stores_customer_id_index')) {
                Schema::table('mp_stores', function (Blueprint $table): void {
                    $table->dropIndex('mp_stores_customer_id_index');
                });
            }

            if (Schema::hasIndex('mp_stores', 'mp_stores_status_index')) {
                Schema::table('mp_stores', function (Blueprint $table): void {
                    $table->dropIndex('mp_stores_status_index');
                });
            }
        }
    }
};
