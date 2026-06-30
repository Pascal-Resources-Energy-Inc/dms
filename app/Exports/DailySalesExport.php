<?php

namespace App\Exports;

use App\OrderDetail;
use App\OtherCharge;
use App\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailySalesExport implements FromCollection, WithHeadings
{
    protected $from;
    protected $to;
    protected $user;

    public function __construct($from, $to, $user)
    {
        $this->from = $from;
        $this->to = $to;
        $this->user = $user;
    }

    public function collection()
    {
        $user = auth()->user();

        $reports = OrderDetail::with('dealer')
            ->where('ad_id', $user->ad->id)
            ->where('status', 'Completed')
            ->whereBetween(
                DB::raw('DATE(date)'),
                [$this->from, $this->to]
            )
            ->orderBy('date', 'ASC')
            ->get();

        $items = Product::where('ad_user_id', $this->user->id)
            ->where('status', 'Activate')
            ->orderBy('product_name')
            ->get();
        $otherCharges = $this->otherCharges();

        $rows = collect();

        $grouped = $reports->groupBy(function ($item) {

            return Carbon::parse($item->date)->format('Y-m-d');

        });

        foreach ($grouped as $date => $transactions) {

            $dailySubtotal = 0;
            $dailyDeliveryFeeTotal = 0;
            $dailyOtherChargeTotal = 0;

            $paymentTotals = [
                'cash' => 0,
                'gcash' => 0,
                'bank_transfer' => 0,
                'credit' => 0,
            ];

            $productSubtotals = [];

            foreach ($items as $item) {

                $productSubtotals[$item->product_name] = [
                    'qty' => 0,
                    'amount' => 0,
                ];

            }

            foreach ($transactions as $r) {

                $lineSubtotal = (float) $r->qty * (float) $r->price;
                $deliveryFee = (float) ($r->delivery_fee ?? 0);
                $otherChargeTotal = $this->calculateOtherCharges($lineSubtotal, $r, $otherCharges);
                $lineTotal = $lineSubtotal + $deliveryFee + $otherChargeTotal;

                $dailySubtotal += $lineTotal;
                $dailyDeliveryFeeTotal += $deliveryFee;
                $dailyOtherChargeTotal += $otherChargeTotal;

                if (isset($productSubtotals[$r->item])) {

                    $productSubtotals[$r->item]['qty'] += $r->qty;

                    $productSubtotals[$r->item]['amount'] += $lineSubtotal;

                }

                if (isset($paymentTotals[$r->payment_method])) {

                    $paymentTotals[$r->payment_method] += $lineTotal;

                }

                $row = [
                    'Date' => Carbon::parse($r->date)->format('M d, Y'),
                    'Dealer' => optional($r->dealer)->name,
                    'Order #' => $r->transaction_id,
                ];

                foreach ($items as $item) {

                    if ($r->item == $item->product_name) {

                        $row[$item->product_name . ' Qty'] = $r->qty;

                        $row[$item->product_name . ' Amount'] = $lineSubtotal;

                    } else {

                        $row[$item->product_name . ' Qty'] = 0;

                        $row[$item->product_name . ' Amount'] = 0;

                    }

                }

                $row['Delivery Fee'] = $deliveryFee;
                $row['Other Charges'] = $otherChargeTotal;
                $row['Total Amount'] = $lineTotal;
                $row['Cash'] = $r->payment_method == 'cash' ? $lineTotal : 0;
                $row['GCash'] = $r->payment_method == 'gcash' ? $lineTotal : 0;
                $row['Bank Transfer'] = $r->payment_method == 'bank_transfer' ? $lineTotal : 0;
                $row['Credit'] = $r->payment_method == 'credit' ? $lineTotal : 0;

                $rows->push($row);

            }

            /*
            |--------------------------------------------------------------------------
            | SUBTOTAL ROW
            |--------------------------------------------------------------------------
            */

            $subtotalRow = [
                'Date' => '',
                'Dealer' => '',
                'Order #' => 'DAILY SUBTOTAL',
            ];

            foreach ($items as $item) {

                $subtotalRow[$item->product_name . ' Qty']
                    = $productSubtotals[$item->product_name]['qty'];

                $subtotalRow[$item->product_name . ' Amount']
                    = $productSubtotals[$item->product_name]['amount'];

            }

            $subtotalRow['Delivery Fee'] = $dailyDeliveryFeeTotal;
            $subtotalRow['Other Charges'] = $dailyOtherChargeTotal;
            $subtotalRow['Total Amount'] = $dailySubtotal;
            $subtotalRow['Cash'] = $paymentTotals['cash'];
            $subtotalRow['GCash'] = $paymentTotals['gcash'];
            $subtotalRow['Bank Transfer'] = $paymentTotals['bank_transfer'];
            $subtotalRow['Credit'] = $paymentTotals['credit'];

            $rows->push($subtotalRow);

        }

        return $rows;
    }

    public function headings(): array
    {
        $items = Product::where('ad_user_id', $this->user->id)
            ->where('status', 'Activate')
            ->orderBy('product_name')
            ->get();

        $headers = [
            'Date',
            'Dealer',
            'Order #',
        ];

        foreach ($items as $item) {

            $headers[] = $item->product_name . ' Qty';
            $headers[] = $item->product_name . ' Amount';

        }

        $headers[] = 'Delivery Fee';
        $headers[] = 'Other Charges';
        $headers[] = 'Total Amount';
        $headers[] = 'Cash';
        $headers[] = 'GCash';
        $headers[] = 'Bank Transfer';
        $headers[] = 'Credit';

        return $headers;
    }

    private function otherCharges()
    {
        if (!Schema::hasTable('other_charges')) {
            return collect();
        }

        return OtherCharge::where('ad_user_id', $this->user->id)
            ->where('is_active', 1)
            ->whereIn('applies_to', ['order', 'delivery', 'dealer', 'customer'])
            ->get();
    }

    private function calculateOtherCharges($lineSubtotal, $order, $otherCharges)
    {
        return $otherCharges->sum(function ($charge) use ($lineSubtotal, $order) {
            if ($charge->applies_to === 'delivery' && $order->delivery_type !== 'delivery') {
                return 0;
            }

            if ($charge->charge_type === 'percentage') {
                return (float) $lineSubtotal * ((float) $charge->amount / 100);
            }

            return (float) $charge->amount;
        });
    }
}
