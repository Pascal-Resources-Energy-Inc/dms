<?php

namespace App\Console\Commands;

use App\AdpoTaxCertificate;
use App\Services\AdpoTaxService;
use Illuminate\Console\Command;

class SendAdpoTaxCertificates extends Command
{
    protected $signature = 'adpo:send-tax-certificates';
    protected $description = 'Retry delivery of signed DPO 2307 documents to Corporate Accounting';

    public function handle(AdpoTaxService $service)
    {
        AdpoTaxCertificate::whereNull('superseded_at')
            ->where(function ($query) {
                $query->whereDoesntHave('deliveries')->orWhereHas('deliveries', function ($delivery) { $delivery->whereNull('sent_at'); });
            })->chunkById(50, function ($certificates) use ($service) {
                foreach ($certificates as $certificate) {
                    $service->deliver($certificate);
                }
            });
        $this->info('Pending 2307 deliveries processed.');
    }
}
