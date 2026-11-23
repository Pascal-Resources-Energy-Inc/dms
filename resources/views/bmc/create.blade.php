@extends('layouts.header')

@section('css')
@include('bmc.partials.field_styles')
<style>
    .bmc-page {
        color: #172033;
        font-family: Inter, Nunito, system-ui, sans-serif;
        padding: 18px 0 30px;
    }
    .bmc-head,
    .bmc-card {
        background: #fff;
        border: 1px solid #e4e7ec;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.045);
        margin-bottom: 16px;
    }
    .bmc-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        padding: 22px;
        background: linear-gradient(135deg, #fff, #f4f8ff);
    }
    .bmc-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-left: auto;
        padding: 9px 15px;
        color: #1d4ed8;
        background: #fff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }
    .bmc-back:hover { color: #fff; background: #1d4ed8; border-color: #1d4ed8; }
    .bmc-back:focus-visible { outline: 3px solid #93c5fd; outline-offset: 2px; }
    .bmc-head h3 {
        margin: 0 0 6px;
        font-weight: 800;
    }
    .bmc-head p,
    .bmc-muted {
        margin: 0;
        color: #667085;
        font-size: 13px;
    }
    .bmc-card-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid #e4e7ec;
    }
    .bmc-step {
        display: grid;
        place-items: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #2563eb;
        color: white;
        font-weight: 800;
    }
    .bmc-card-head h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
    }
    .bmc-body {
        padding: 20px;
    }
    .bmc-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .bmc-full {
        grid-column: 1/-1;
    }
    .bmc-label {
        display: block;
        margin-bottom: 6px;
        color: #344054;
        font-size: 12px;
        font-weight: 800;
    }
    .bmc-required {
        color: #dc2626;
    }
    .bmc-field {
        width: 100%;
        min-height: 43px;
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        padding: 10px 12px;
        color: #344054;
        background: white;
        font-size: 13px;
    }
    .bmc-field:focus {
        outline: 0;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .bmc-field[readonly] {
        background: #f8fafc;
    }
    textarea.bmc-field {
        min-height: 100px;
    }
    .bmc-item {
        display: grid;
        grid-template-columns: 1fr 100px;
        gap: 12px;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid #eef2f6;
    }
    .bmc-item strong {
        display: block;
        font-size: 13px;
    }
    .bmc-item small {
        color: #667085;
    }
    .bmc-guide {
        margin: 16px 0;
        text-align: center;
    }
    .bmc-guide img {
        display: block;
        width: 100%;
        max-width: 900px;
        height: auto;
        margin: 0 auto;
    }
    .bmc-total {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 16px;
        padding: 15px;
        background: #eff6ff;
        border-radius: 8px;
        color: #1d4ed8;
        font-weight: 800;
    }
    .bmc-deposit-note {
        margin: 10px 0 0;
        color: #475467;
        font-size: 12px;
        line-height: 1.5;
    }
    .bmc-alert {
        padding: 14px 16px;
        margin-bottom: 16px;
        border-radius: 8px;
        font-size: 13px;
    }
    .bmc-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .bmc-success {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .bmc-submit {
        background: #2563eb;
        border: 0;
        border-radius: 8px;
        color: white;
        padding: 12px 24px;
        font-weight: 800;
    }
    .bmc-actions {
        display: flex;
        justify-content: flex-end;
    }
    @media (max-width: 640px) {
        .bmc-grid {
            grid-template-columns: 1fr;
        }
        .bmc-full {
            grid-column: auto;
        }
        .bmc-body {
            padding: 16px;
        }
        .bmc-submit {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
@php
    $editing = isset($borrowing);
    $borrowedQuantities = collect($borrowing['borrowed_items'] ?? [])->pluck('requested_quantity', 'item_name');
@endphp
<div class="bmc-page">
    <header class="bmc-head">
        <div>
            <h3>{{ $editing ? 'Edit Borrowing Request' : 'Borrowing Marketing Collateral' }}</h3>
            <p>CEO-MBD-FOR-012-001.B</p>
        </div>
        <a class="bmc-back" href="{{ $editing ? route('bmc.show', $borrowing['id']) : route('bmc.index') }}"><i class="ti ti-arrow-left" aria-hidden="true"></i> Back</a>
    </header>

    @if($errors->any())
        <div class="bmc-alert bmc-error" role="alert">
            <strong>Please check your request.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $editing ? route('bmc.update', $borrowing['id']) : route('bmc.store') }}" method="POST" id="bmcForm">
        @csrf
        @if($editing) @method('PATCH') @endif

        <section class="bmc-card">
            <div class="bmc-card-head">
                <span class="bmc-step">1</span>
                <h4>Borrower information</h4>
            </div>
            <div class="bmc-body bmc-grid">
                <div>
                    <label class="bmc-label" for="first">First name <span class="bmc-required">*</span></label>
                    <input class="bmc-field" id="first" name="borrower_first_name" maxlength="255" autocapitalize="off"
                        value="{{ old('borrower_first_name', $editing ? $firstName : mb_convert_case($firstName, MB_CASE_TITLE, 'UTF-8')) }}" required>
                </div>
                <div>
                    <label class="bmc-label" for="last">Last name <span class="bmc-required">*</span></label>
                    <input class="bmc-field" id="last" name="borrower_last_name" maxlength="255" autocapitalize="off"
                        value="{{ old('borrower_last_name', $editing ? $lastName : mb_convert_case($lastName, MB_CASE_TITLE, 'UTF-8')) }}" required>
                </div>
                <div>
                    <label class="bmc-label" for="profile">Partner type</label>
                    <input class="bmc-field" id="profile" value="{{ $user->role }}" readonly>
                </div>
                <div>
                    <label class="bmc-label" for="email">Email address</label>
                    <input class="bmc-field" id="email" value="{{ $user->email }}" readonly>
                </div>
                <div>
                    <label class="bmc-label" for="business">Business name</label>
                    <input class="bmc-field" id="business" name="business_name" maxlength="255"
                        value="{{ old('business_name', $borrowing['business_name'] ?? '') }}">
                </div>
                <div>
                    <label class="bmc-label" for="phone">Mobile number <span class="bmc-required">*</span></label>
                    <input class="bmc-field" id="phone" name="phone_number" type="tel" inputmode="numeric"
                        pattern="09[0-9]{9}" maxlength="11" placeholder="09xxxxxxxxx"
                        value="{{ old('phone_number', $borrowing['phone_number'] ?? '') }}" required>
                </div>
            </div>
        </section>

        <section class="bmc-card">
            <div class="bmc-card-head">
                <span class="bmc-step">2</span>
                <h4>Event details</h4>
            </div>
            <div class="bmc-body bmc-grid">
                <div class="bmc-full">
                    <label class="bmc-label" for="event">Event name <span class="bmc-required">*</span></label>
                    <input class="bmc-field" id="event" name="event_name" maxlength="255"
                        value="{{ old('event_name', $borrowing['event_name'] ?? '') }}" required>
                </div>
                <div>
                    <label class="bmc-label" for="date">Date needed <span class="bmc-required">*</span></label>
                    <input class="bmc-field" id="date" name="date_needed" type="date"
                        @if(!$editing) min="{{ now()->toDateString() }}" @endif value="{{ old('date_needed', isset($borrowing['date_needed']) ? substr($borrowing['date_needed'], 0, 10) : '') }}" required>
                </div>
                <div>
                    <label class="bmc-label" for="days">Number of days needed <span class="bmc-required">*</span></label>
                    <input class="bmc-field" id="days" name="number_of_days_item_needed" type="number"
                        min="1" max="40" value="{{ old('number_of_days_item_needed', $borrowing['number_of_days_item_needed'] ?? '') }}" required>
                    <small class="bmc-muted">Maximum of 40 days.</small>
                </div>
                <div class="bmc-full">
                    <label class="bmc-label" for="details">Request details <span class="bmc-required">*</span></label>
                    <textarea class="bmc-field" id="details" name="request_details" required>{{ old('request_details', $borrowing['request_details'] ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <section class="bmc-card">
            <div class="bmc-card-head">
                <span class="bmc-step">3</span>
                <h4>Items to borrow</h4>
            </div>
            <div class="bmc-body">
                <p class="bmc-muted">Enter a quantity for at least one item. Deposit values are based on the quantities requested.</p>
                <figure class="bmc-guide">
                    <a href="{{ asset('images/BMC_ITEMS_PRICE.png') }}" target="_blank" rel="noopener noreferrer" aria-label="Open the borrowing items reference guide in a new tab">
                        <img src="{{ asset('images/BMC_ITEMS_PRICE.png') }}" alt="Reference guide showing the booth and roll up banners with their prices">
                    </a>
                    <small class="bmc-muted">Click the image to view it at full size.</small>
                </figure>
                @foreach($items as $name => $rate)
                    <div class="bmc-item">
                        <div>
                            <strong>{{ $name }}</strong>
                            <small>₱{{ number_format($rate, 2) }} deposit per item</small>
                        </div>
                        <div>
                            <label class="bmc-label" for="item{{ $loop->index }}">Quantity</label>
                            <input type="hidden" name="borrowed_items[{{ $loop->index }}][item_name]" value="{{ $name }}">
                            <input class="bmc-field bmc-quantity" id="item{{ $loop->index }}"
                                aria-label="{{ $name }} quantity" type="number" min="0" max="99"
                                name="borrowed_items[{{ $loop->index }}][requested_quantity]"
                                value="{{ old('borrowed_items.'.$loop->index.'.requested_quantity', $borrowedQuantities->get($name, 0)) }}"
                                data-rate="{{ $rate }}" required>
                        </div>
                    </div>
                @endforeach
                <div class="bmc-total">
                    <span>Deposit</span>
                    <span id="bmcTotal">₱0.00</span>
                </div>
                <p class="bmc-deposit-note"><strong>Note:</strong> A security deposit is required for the items to be borrowed.</p>
            </div>
        </section>

        <div class="bmc-actions">
            <button class="bmc-submit" type="submit">{{ $editing ? 'Save changes' : 'Submit request' }}</button>
        </div>
    </form>
</div>
<script>
    (function () {
        var fields = document.querySelectorAll(".bmc-quantity");
        function updateTotal() {
            var total = 0;
            fields.forEach(function (field) {
                total +=
                    Math.max(0, Number(field.value) || 0) *
                    Number(field.dataset.rate);
            });
            document.getElementById("bmcTotal").textContent =
                "₱" +
                total.toLocaleString("en-PH", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
        }
        fields.forEach(function (field) {
            field.addEventListener("input", updateTotal);
        });
        updateTotal();
        document.getElementById("bmcForm").addEventListener("submit", function () {
            this.querySelector("button[type=submit]").disabled = true;
        });
    })();
</script>
@endsection
