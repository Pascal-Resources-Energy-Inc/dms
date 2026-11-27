<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DealerQuantities
{
    public function addTo($query, $connection, $profileTable, $profileAlias, $dedicated = false)
    {
        $db = DB::connection($connection);
        $schema = $db->getSchemaBuilder();
        $stock = $this->sum($db, $schema, 'order_details', 'mega_receipts', function ($rows, $alias) use ($schema, $profileTable, $profileAlias) {
            // dealer_id is the recipient's user ID. mega_dealer_id is the
            // supplier's profile ID, so it must never be used for receipts.
            $this->matchUser($rows, $schema, 'order_details', $alias, $profileTable, $profileAlias);
        }, true);
        $retail = $this->sum($db, $schema, 'transaction_details', 'mega_retail', function ($rows, $alias) use ($db, $schema, $profileTable, $profileAlias, $dedicated) {
            if ($dedicated && $schema->hasColumn('transaction_details', 'mega_dealer_id')) {
                $rows->where(function ($seller) use ($schema, $profileTable, $profileAlias, $alias) {
                    $seller->whereColumn($alias.'.mega_dealer_id', $profileAlias.'.id')
                        ->orWhere(function ($legacy) use ($schema, $profileTable, $profileAlias, $alias) {
                            $legacy->where(function ($missing) use ($alias) {
                                $missing->whereNull($alias.'.mega_dealer_id')->orWhere($alias.'.mega_dealer_id', 0);
                            });
                            $this->matchUser($legacy, $schema, 'transaction_details', $alias, $profileTable, $profileAlias);
                        });
                });
                $this->matchSource($rows, $db, $schema, 'transaction_details', $alias);
            } else {
                $this->matchUser($rows, $schema, 'transaction_details', $alias, $profileTable, $profileAlias);
            }
        }, false);
        $dealerSales = ['sql' => '0', 'bindings' => []];
        if ($dedicated && $schema->hasTable('order_details') && $schema->hasColumn('order_details', 'mega_dealer_id')) {
            $dealerSales = $this->sum($db, $schema, 'order_details', 'mega_deliveries', function ($rows, $alias) use ($db, $schema, $profileAlias) {
                $rows->whereColumn($alias.'.mega_dealer_id', $profileAlias.'.id');
                $this->matchSource($rows, $db, $schema, 'order_details', $alias);
            }, true);
        }

        $soldSql = '('.$retail['sql'].' + '.$dealerSales['sql'].')';
        $soldBindings = array_merge($retail['bindings'], $dealerSales['bindings']);

        return $query->selectRaw($stock['sql'].' AS stock_qty', $stock['bindings'])
            ->selectRaw($soldSql.' AS sold_qty', $soldBindings)
            ->selectRaw('('.$stock['sql'].' - '.$soldSql.') AS remaining_qty', array_merge($stock['bindings'], $soldBindings));
    }

    private function matchUser($rows, $schema, $table, $alias, $profileTable, $profileAlias)
    {
        $column = $schema->hasColumn($table, 'dealer_id') ? 'dealer_id'
            : ($schema->hasColumn($table, 'user_id') ? 'user_id' : null);
        if (!$column || !$schema->hasColumn($profileTable, 'user_id')) {
            $rows->whereRaw('1 = 0');
            return;
        }
        $rows->whereColumn($alias.'.'.$column, $profileAlias.'.user_id')
            ->where($profileAlias.'.user_id', '>', 0);
    }

    private function matchSource($rows, $db, $schema, $table, $alias)
    {
        if (!$schema->hasColumn($table, 'mega_dealer_source')) return;
        $rows->where(function ($source) use ($db, $alias) {
            $source->whereNull($alias.'.mega_dealer_source')
                ->orWhereIn($alias.'.mega_dealer_source', ['', $db->getName(), $db->getDatabaseName()]);
        });
    }

    private function sum($db, $schema, $table, $alias, $matchSeller, $isOrder)
    {
        if (!$schema->hasTable($table)) return ['sql' => '0', 'bindings' => []];
        $quantity = $schema->hasColumn($table, 'qty') ? 'qty'
            : ($schema->hasColumn($table, 'quantity') ? 'quantity' : null);
        if (!$quantity) return ['sql' => '0', 'bindings' => []];

        $rows = $db->table($table.' as '.$alias)
            ->selectRaw('COALESCE(SUM('.$db->getQueryGrammar()->wrap($alias.'.'.$quantity).'), 0)');
        $matchSeller($rows, $alias);
        if ($schema->hasColumn($table, 'deleted_at')) $rows->whereNull($alias.'.deleted_at');
        if ($schema->hasColumn($table, 'status')) {
            $status = $db->getQueryGrammar()->wrap($alias.'.status');
            if ($isOrder) {
                $rows->whereRaw('LOWER(TRIM('.$status.')) = ?', ['completed']);
            } else {
                // Older retail transactions have no status; they were posted
                // immediately. Explicit pending/cancelled transactions are not sales.
                $rows->where(function ($completed) use ($alias, $status) {
                    $completed->whereNull($alias.'.status')->orWhereRaw('TRIM('.$status.') = ?', [''])
                        ->orWhereRaw('LOWER(TRIM('.$status.')) = ?', ['completed']);
                });
            }
        }

        return ['sql' => '('.$rows->toSql().')', 'bindings' => $rows->getBindings()];
    }
}
