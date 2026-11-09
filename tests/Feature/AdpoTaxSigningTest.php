<?php

namespace Tests\Feature;

use App\AdpoTaxCertificate;
use App\AdpoTaxDelivery;
use App\AdPurchaseOrder;
use App\AreaDistributor;
use App\ProvincialDistributor;
use App\User;
use App\Services\AdpoTaxService;
use App\Mail\AdpoSigned2307;
use App\Http\Controllers\AdpoTaxCertificateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdpoTaxSigningTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        if (getenv('ADPO_RENDER_QA') === '1' && !is_dir(base_path('tmp/pdfs'))) {
            mkdir(base_path('tmp/pdfs'), 0755, true);
        }
        config(['database.default' => 'tax_test', 'database.connections.tax_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        require_once database_path('migrations/2026_10_29_000002_create_adpo_tax_certificates.php');
        (new \CreateAdpoTaxCertificates)->up();
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id'); $table->string('department')->nullable();
            $table->string('status')->nullable(); $table->string('email')->nullable();
        });
    }

    private function user($role = 'Area Distributor', $enabled = true)
    {
        $user = new User(['role' => $role, 'name' => 'QA SAMPLE - NOT A REAL SIGNATURE']);
        $user->id = 500;
        $profile = $role === 'Provincial Distributor' ? new ProvincialDistributor : new AreaDistributor;
        $profile->withholding_tax = $enabled;
        $user->setRelation($role === 'Provincial Distributor' ? 'provincialDistributor' : 'ad', $profile);
        return $user;
    }

    private function strokes()
    {
        return [[[0.1,0.7],[0.15,0.3],[0.2,0.7],[0.25,0.3],[0.3,0.7],[0.4,0.3],[0.5,0.7],[0.6,0.3],[0.8,0.7]]];
    }

    private function details()
    {
        return ['name' => 'QA SAMPLE DISTRIBUTOR', 'address' => '123 Sample Street, Sample City', 'tin' => '00000000000000', 'zip' => '0000', 'strokes' => $this->strokes()];
    }

    private function order()
    {
        $order = new AdPurchaseOrder(['po_number' => 'ADPO-QA-SAMPLE', 'order_source' => 'prei_warehouse', 'subtotal' => 11200, 'withholding_tax' => 100, 'total_amount' => 11100]);
        $order->id = 1;
        return $order;
    }

    public function test_requirement_uses_both_distributor_profiles_only()
    {
        $service = new AdpoTaxService;
        $this->assertTrue($service->required($this->user()));
        $this->assertTrue($service->required($this->user('Provincial Distributor')));
        $this->assertFalse($service->required($this->user('Area Distributor', false)));
        $this->assertFalse($service->required($this->user('Provincial Distributor', false)));
        $this->assertFalse($service->required($this->user('Admin')));
    }

    public function test_missing_signature_is_rejected_on_server()
    {
        $this->expectException(ValidationException::class);
        (new AdpoTaxService)->validateSigning(new Request, $this->user());
    }

    public function test_blank_or_out_of_bounds_signature_is_rejected()
    {
        foreach (['[]', '[[[2,2],[3,3]]]', str_repeat('x', 150001)] as $signature) {
            try {
                (new AdpoTaxService)->validateSigning(new Request([
                    'tax_registered_name' => 'Sample', 'tax_registered_address' => 'Sample address',
                    'tax_tin' => '000-000-000', 'tax_consent' => '1', 'tax_signature' => $signature,
                ]), $this->user());
                $this->fail('Invalid signature accepted');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('tax_signature', $exception->errors());
            }
        }
    }

    public function test_valid_signature_has_authenticated_signer_and_immutable_pdf()
    {
        $service = new AdpoTaxService;
        $signing = $service->validateSigning(new Request([
            'tax_registered_name' => 'Sample', 'tax_registered_address' => 'Sample address',
            'tax_tin' => '000-000-000', 'tax_consent' => '1', 'tax_signature' => json_encode($this->strokes()),
            'signer_name' => 'Spoofed Name',
        ]), $this->user('Provincial Distributor'));
        $first = $service->capture($this->order(), $this->user(), $signing);
        $this->assertSame(500, $first->signed_by);
        $this->assertSame('QA SAMPLE - NOT A REAL SIGNATURE', $first->signer_name);
        $pdf = base64_decode($first->pdf_base64);
        $this->assertSame('%PDF-', substr($pdf, 0, 5));
        $this->assertSame(hash('sha256', $pdf), $first->sha256);
        $service->capture($this->order(), $this->user(), $this->details());
        $this->assertNotNull($first->fresh()->superseded_at);
        $this->assertSame($pdf, base64_decode($first->fresh()->pdf_base64));
        if (getenv('ADPO_RENDER_QA') === '1') {
            file_put_contents(base_path('tmp/pdfs/adpo-2307-qa.pdf'), $pdf);
        }
    }

    public function test_rollback_does_not_leave_a_certificate()
    {
        DB::beginTransaction();
        (new AdpoTaxService)->capture($this->order(), $this->user(), $this->details());
        DB::rollBack();
        $this->assertSame(0, AdpoTaxCertificate::count());
    }

    public function test_emails_only_active_accounting_users_and_does_not_resend_success()
    {
        DB::table('users')->insert([
            ['department' => ' corporate accounting department ', 'status' => 'active', 'email' => 'accounting@example.test'],
            ['department' => 'SALES', 'status' => 'active', 'email' => 'sales@example.test'],
            ['department' => AdpoTaxService::DEPARTMENT, 'status' => 'inactive', 'email' => 'disabled@example.test'],
        ]);
        $service = new AdpoTaxService;
        $certificate = $service->capture($this->order(), $this->user(), $this->details());
        Mail::shouldReceive('to')->once()->with('accounting@example.test')->andReturnSelf();
        Mail::shouldReceive('send')->once()->with(\Mockery::on(function ($mail) use ($certificate) {
            $mail->build();
            return $mail instanceof AdpoSigned2307 && $mail->rawAttachments[0]['data'] === base64_decode($certificate->pdf_base64);
        }));
        Mail::shouldReceive('failures')->once()->andReturn([]);
        $service->deliver($certificate);
        $service->deliver($certificate);
        $this->assertSame(1, AdpoTaxDelivery::count());
        $this->assertNotNull(AdpoTaxDelivery::first()->sent_at);
    }

    public function test_email_failure_preserves_certificate_and_pending_retry()
    {
        DB::table('users')->insert(['department' => AdpoTaxService::DEPARTMENT, 'status' => 'active', 'email' => 'accounting@example.test']);
        $service = new AdpoTaxService;
        $certificate = $service->capture($this->order(), $this->user(), $this->details());
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Simulated unavailable mail transport'));
        $service->deliver($certificate);
        $this->assertNull(AdpoTaxDelivery::first()->sent_at);
        $this->assertSame(1, (int) AdpoTaxDelivery::first()->attempts);
        $this->assertNotNull($certificate->fresh());
    }

    public function test_unrelated_user_cannot_download_signature()
    {
        $certificate = (new AdpoTaxService)->capture($this->order(), $this->user(), $this->details());
        $other = $this->user(); $other->id = 501;
        $this->actingAs($other);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new AdpoTaxCertificateController)->download($certificate->id);
    }

    public function test_signing_form_renders_for_both_roles_and_hides_when_disabled()
    {
        foreach (['Area Distributor', 'Provincial Distributor'] as $role) {
            $user = $this->user($role);
            $this->actingAs($user);
            $html = view('ad_purchase_orders.partials.tax_signing', [
                'requiresTaxSignature' => true, 'taxProfile' => (new AdpoTaxService)->profile($user),
            ])->render();
            $this->assertContains('taxSignatureCanvas', $html);
            $this->assertContains($role, $html);
            if (getenv('ADPO_RENDER_QA') === '1') {
                $html = preg_replace('/<link[^>]+>|<script[^>]+><\/script>/', '', $html);
                file_put_contents(base_path('tmp/pdfs/signing-ui-qa.html'), '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="../../public/design/assets/css/styles.css"><link rel="stylesheet" href="../../public/css/adpo-tax-signing.css"><style>body{padding:24px;background:#edf3f8}main{max-width:1050px;margin:auto}</style></head><body><main><form id="qaForm"><input name="qty" value="1" type="number" aria-label="QA quantity">' . $html . '<button type="submit">Submit DPO</button></form></main><script src="../../public/js/adpo-tax-signing.js"></script><script>document.getElementById("qaForm").addEventListener("submit",function(e){e.preventDefault();window.qaSubmitted=true;});</script></body></html>');
            }
        }
        $html = view('ad_purchase_orders.partials.tax_signing', ['requiresTaxSignature' => false])->render();
        $this->assertNotContains('taxSignatureCanvas', $html);
    }

    public function test_no_recipient_keeps_document_available_without_sending()
    {
        Mail::shouldReceive('to')->never();
        $service = new AdpoTaxService;
        $certificate = $service->capture($this->order(), $this->user(), $this->details());
        $service->deliver($certificate);
        $this->assertSame(0, AdpoTaxDelivery::count());
        $this->assertNotNull($certificate->fresh());
    }

    public function test_owner_can_download_and_view_delivery_status()
    {
        $this->actingAs($this->user());
        $order = $this->order();
        $certificate = (new AdpoTaxService)->capture($order, $this->user(), $this->details());
        $response = (new AdpoTaxCertificateController)->download($certificate->id);
        $this->assertSame(base64_decode($certificate->pdf_base64), $response->getContent());
        $html = view('ad_purchase_orders.partials.tax_document', ['order' => $order])->render();
        $this->assertContains('Awaiting an active Corporate Accounting recipient', $html);
        AdpoTaxDelivery::create(['certificate_id' => $certificate->id, 'email' => 'accounting@example.test', 'sent_at' => now()]);
        $html = view('ad_purchase_orders.partials.tax_document', ['order' => $order])->render();
        $this->assertContains('1 of 1 Accounting emails sent', $html);
    }
}
