<style>
    .user-form .form-section {
        border: 1px solid #dfe8f5;
        border-radius: .6rem;
        padding: 1.1rem;
        margin-bottom: 1rem;
        background: #fff;
        box-shadow: 0 .35rem 1rem rgba(16, 35, 63, .045);
    }

    .form-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border: 1px solid #cfe2ff;
        border-radius: .7rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #ffffff 0%, #f4f9ff 100%);
        box-shadow: 0 .35rem 1.25rem rgba(7, 95, 195, .08);
    }

    .form-hero-main {
        min-width: 0;
    }

    .form-kicker {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .22rem .55rem;
        margin-bottom: .45rem;
        background: #eef6ff;
        color: #075fc3;
        font-size: .72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .form-hero h3 {
        margin: 0;
        color: #10233f;
        font-size: 1.25rem;
        font-weight: 900;
    }

    .form-hero p {
        margin: .25rem 0 0;
        color: #6c7a90;
        font-weight: 700;
    }

    .form-hero-side {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 220px;
        justify-content: flex-end;
    }

    .form-hero-side strong,
    .form-hero-side small {
        display: block;
    }

    .form-hero-side strong {
        color: #10233f;
        font-weight: 900;
    }

    .form-hero-side small {
        color: #6c7a90;
        font-weight: 800;
    }

    .dms-user-form label {
        color: #10233f;
        margin-bottom: .35rem;
    }

    .dms-user-form .form-control {
        border-color: #d6e0ec;
        border-radius: .35rem;
        font-weight: 600;
        min-height: 39px;
    }

    .dms-user-form .form-control:focus {
        border-color: #7db7ff;
        box-shadow: 0 0 0 .18rem rgba(7, 95, 195, .12);
    }

    .dms-user-form textarea.form-control {
        min-height: auto;
    }

    .dms-user-form .form-text {
        font-weight: 700;
    }

    .dms-user-form > .d-flex.justify-content-end {
        position: sticky;
        bottom: 0;
        z-index: 5;
        margin: 1rem -1.1rem -1.1rem;
        padding: .85rem 1.1rem;
        background: rgba(255, 255, 255, .94);
        border-top: 1px solid #e5eaf2;
        backdrop-filter: blur(6px);
    }

    .section-heading {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: 1.05rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid #edf2f8;
    }

    .section-heading strong {
        display: block;
        font-size: 1rem;
        color: #10233f;
    }

    .section-heading small {
        display: block;
        color: #6c7a90;
        font-weight: 700;
    }

    .section-icon {
        width: 36px;
        height: 36px;
        border-radius: .5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #075fc3;
        color: #fff;
        font-weight: 900;
        flex: 0 0 34px;
    }

    .avatar-preview {
        width: 78px;
        height: 78px;
        object-fit: contain;
        border: 1px solid #e5eaf2;
        border-radius: .6rem;
        background: #f8fafc;
        padding: .5rem;
    }

    .delivery-panel,
    .map-panel,
    .warehouse-panel {
        border: 1px solid #d9e8fb;
        border-radius: .6rem;
        padding: 1rem;
        background: #f8fbff;
    }

    .project-card-grid,
    .warehouse-options {
        display: grid;
        gap: .5rem;
    }

    .project-card,
    .warehouse-card {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin: 0;
        padding: .65rem .75rem;
        border: 1px solid #e5eaf2;
        border-radius: .5rem;
        background: #fff;
        cursor: pointer;
        font-weight: 800;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    .project-card:hover,
    .warehouse-card:hover {
        border-color: #8dbfff;
        background: #f8fbff;
        box-shadow: 0 .35rem .8rem rgba(7, 95, 195, .08);
    }

    .project-card input:checked + span,
    .warehouse-card input:checked + .warehouse-content {
        color: #075fc3;
    }

    .warehouse-content {
        display: flex;
        align-items: center;
        gap: .7rem;
    }

    .warehouse-icon {
        width: 32px;
        height: 32px;
        border-radius: .5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef6ff;
        color: #075fc3;
        font-weight: 900;
    }

    .warehouse-card small {
        display: block;
        color: #6c7a90;
        font-weight: 700;
    }

    .project-row {
        border: 1px solid #e5eaf2;
        border-radius: .55rem;
        padding: .75rem .75rem 0;
        margin: 0 0 .75rem;
        background: #fff;
    }

    .leaflet-map {
        position: relative;
        height: 360px;
        min-height: 320px;
        border-radius: .6rem;
        border: 1px solid #d8e4f2;
        overflow: hidden;
        background: #eef6ff;
    }

    .map-fallback {
        height: 100%;
        min-height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1.25rem;
        color: #6c7a90;
        font-weight: 800;
        background: repeating-linear-gradient(
            45deg,
            #f8fbff,
            #f8fbff 12px,
            #eef6ff 12px,
            #eef6ff 24px
        );
    }

    .pin-readout {
        border: 1px solid #e5eaf2;
        border-radius: .5rem;
        background: #fff;
        padding: .65rem .8rem;
        color: #10233f;
        font-weight: 800;
    }

    .map-panel .btn-group {
        flex-wrap: wrap;
    }

    .modal-xl {
        max-width: 1140px;
    }

    .modal-select2 .modal-content {
        border: 0;
        border-radius: .75rem;
        overflow: visible;
        box-shadow: 0 1rem 3rem rgba(16, 35, 63, .22);
    }

    .modal-select2 .modal-header,
    .modal-select2 .modal-footer {
        background: #f8fbff;
        border-color: #e5eaf2;
    }

    .modal-select2 .modal-body {
        max-height: calc(100vh - 210px);
        overflow-y: auto;
        background: #f5f7fb;
    }

    .dms-user-form .select2-container {
        width: 100% !important;
    }

    .dms-user-form .select2-container--default .select2-selection--single {
        height: calc(1.6em + .75rem + 2px);
        border: 1px solid #ced4da;
        border-radius: .25rem;
    }

    .dms-user-form .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.6em + .75rem);
        padding-left: .75rem;
        color: #495057;
    }

    .dms-user-form .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.6em + .75rem);
    }

    .dms-user-form .select2-container--default.select2-container--disabled .select2-selection--single {
        background: #e9ecef;
    }

    .dms-user-form .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    .modal-select2 .select2-container {
        z-index: 2055;
    }

    .select2-container--open {
        z-index: 2060;
    }

    @media (max-width: 575.98px) {
        .form-hero {
            display: block;
        }

        .form-hero-side {
            justify-content: flex-start;
            margin-top: .85rem;
            min-width: 0;
        }

        .section-heading {
            align-items: flex-start;
        }

        .avatar-preview {
            width: 68px;
            height: 68px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var prefixes = {
            super_admin: 'SA',
            provincial_distributor: 'PD',
            area_distributor: 'AD',
            mega_dealer: 'MD',
            dealer: 'DL'
        };
        var leafletLoadPromise = null;
        var select2LoadPromise = null;
        var psgcBase = 'https://psgc.gitlab.io/api';
        var defaultLat = 13.1391000;
        var defaultLng = 123.7438000;
        var zipCodes = {
            'Caloocan City': '1400',
            'City of Caloocan': '1400',
            'Las Pinas City': '1740',
            'City of Las Pinas': '1740',
            'Makati City': '1200',
            'City of Makati': '1200',
            'Malabon City': '1470',
            'City of Malabon': '1470',
            'Mandaluyong City': '1550',
            'City of Mandaluyong': '1550',
            'Manila': '1000',
            'City of Manila': '1000',
            'Marikina City': '1800',
            'City of Marikina': '1800',
            'Muntinlupa City': '1770',
            'City of Muntinlupa': '1770',
            'Navotas City': '1485',
            'City of Navotas': '1485',
            'Paranaque City': '1700',
            'City of Paranaque': '1700',
            'Pasay City': '1300',
            'City of Pasay': '1300',
            'Pasig City': '1600',
            'City of Pasig': '1600',
            'Pateros': '1620',
            'Quezon City': '1100',
            'City of Quezon': '1100',
            'San Juan City': '1500',
            'City of San Juan': '1500',
            'Taguig City': '1630',
            'City of Taguig': '1630',
            'Valenzuela City': '1440',
            'City of Valenzuela': '1440',
            'Bacacay': '4509',
            'Camalig': '4502',
            'Daraga': '4501',
            'Guinobatan': '4503',
            'Jovellar': '4515',
            'Legazpi City': '4500',
            'Libon': '4507',
            'Ligao City': '4504',
            'Malilipot': '4510',
            'Malinao': '4512',
            'Manito': '4514',
            'Oas': '4505',
            'Pio Duran': '4516',
            'Polangui': '4506',
            'Rapu-Rapu': '4517',
            'Santo Domingo': '4508',
            'Tabaco City': '4511',
            'Tiwi': '4513'
        };
        var barangayZipCodes = {
            'Makati City|Bel-Air': '1209',
            'City of Makati|Bel-Air': '1209',
            'Makati City|Dasmarinas': '1222',
            'City of Makati|Dasmarinas': '1222',
            'Makati City|Forbes Park': '1219',
            'City of Makati|Forbes Park': '1219',
            'Makati City|Guadalupe Nuevo': '1212',
            'City of Makati|Guadalupe Nuevo': '1212',
            'Makati City|Guadalupe Viejo': '1211',
            'City of Makati|Guadalupe Viejo': '1211',
            'Makati City|Magallanes': '1232',
            'City of Makati|Magallanes': '1232',
            'Makati City|Poblacion': '1210',
            'City of Makati|Poblacion': '1210',
            'Makati City|San Antonio': '1203',
            'City of Makati|San Antonio': '1203',
            'Makati City|San Isidro': '1234',
            'City of Makati|San Isidro': '1234',
            'Makati City|San Lorenzo': '1223',
            'City of Makati|San Lorenzo': '1223',
            'Makati City|Urdaneta': '1225',
            'City of Makati|Urdaneta': '1225',
            'Makati City|Valenzuela': '1208',
            'City of Makati|Valenzuela': '1208',
            'Taguig City|Bonifacio Global City': '1634',
            'City of Taguig|Bonifacio Global City': '1634',
            'Taguig City|Fort Bonifacio': '1630',
            'City of Taguig|Fort Bonifacio': '1630',
            'Taguig City|Western Bicutan': '1630',
            'City of Taguig|Western Bicutan': '1630',
            'Taguig City|Ususan': '1639',
            'City of Taguig|Ususan': '1639',
            'Quezon City|Cubao': '1109',
            'City of Quezon|Cubao': '1109',
            'Quezon City|Diliman': '1101',
            'City of Quezon|Diliman': '1101',
            'Quezon City|Novaliches Proper': '1123',
            'City of Quezon|Novaliches Proper': '1123',
            'Quezon City|Project 6': '1100',
            'City of Quezon|Project 6': '1100',
            'Manila|Binondo': '1006',
            'City of Manila|Binondo': '1006',
            'Manila|Ermita': '1000',
            'City of Manila|Ermita': '1000',
            'Manila|Intramuros': '1002',
            'City of Manila|Intramuros': '1002',
            'Manila|Malate': '1004',
            'City of Manila|Malate': '1004',
            'Manila|Paco': '1007',
            'City of Manila|Paco': '1007',
            'Manila|Pandacan': '1011',
            'City of Manila|Pandacan': '1011',
            'Manila|Quiapo': '1001',
            'City of Manila|Quiapo': '1001',
            'Manila|Sampaloc': '1008',
            'City of Manila|Sampaloc': '1008',
            'Manila|San Andres': '1017',
            'City of Manila|San Andres': '1017',
            'Manila|San Miguel': '1005',
            'City of Manila|San Miguel': '1005',
            'Manila|San Nicolas': '1010',
            'City of Manila|San Nicolas': '1010',
            'Manila|Santa Ana': '1009',
            'City of Manila|Santa Ana': '1009',
            'Manila|Santa Cruz': '1003',
            'City of Manila|Santa Cruz': '1003',
            'Manila|Tondo': '1012',
            'City of Manila|Tondo': '1012',
            'Legazpi City|Bagumbayan': '4500',
            'Legazpi City|Bonot': '4500',
            'Legazpi City|Cabangan': '4500',
            'Legazpi City|Dap-Dap': '4500',
            'Legazpi City|Dinagaan': '4500',
            'Legazpi City|EMs Barrio': '4500',
            'Legazpi City|Gogon': '4500',
            'Legazpi City|Ilawod East': '4500',
            'Legazpi City|Ilawod West': '4500',
            'Legazpi City|Oro Site-Magallanes St.': '4500',
            'Legazpi City|Rawis': '4500',
            'Legazpi City|Rizal Street': '4500',
            'Legazpi City|Taysan': '4500',
            'Legazpi City|Washington Drive': '4500',
            'Daraga|Alobo': '4501',
            'Daraga|Anislag': '4501',
            'Daraga|Bagumbayan': '4501',
            'Daraga|Banadero': '4501',
            'Daraga|Binitayan': '4501',
            'Daraga|Busay': '4501',
            'Daraga|Kimantong': '4501',
            'Daraga|Market Area': '4501',
            'Daraga|Penafrancia': '4501',
            'Daraga|Sagpon': '4501',
            'Guinobatan|Bagumbayan': '4503',
            'Guinobatan|Banao': '4503',
            'Guinobatan|Batbat': '4503',
            'Guinobatan|Calzada': '4503',
            'Guinobatan|Irisan': '4503',
            'Guinobatan|Masarawag': '4503',
            'Guinobatan|Mauraro': '4503',
            'Guinobatan|Poblacion': '4503',
            'Guinobatan|Quitago': '4503',
            'Tabaco City|Bacolod': '4511',
            'Tabaco City|Basagan': '4511',
            'Tabaco City|Bombon': '4511',
            'Tabaco City|Cobo': '4511',
            'Tabaco City|Comon': '4511',
            'Tabaco City|Divino Rostro': '4511',
            'Tabaco City|San Roque': '4511',
            'Tabaco City|Tayhi': '4511'
        };
        var fallbackLocations = {
            regions: [
                { code: '050000000', name: 'Bicol Region (Region V)' }
            ],
            provinces: {
                '050000000': [
                    { code: '050500000', name: 'Albay' }
                ]
            },
            cities: {
                '050500000': [
                    { code: '050506000', name: 'Daraga' },
                    { code: '050508000', name: 'Legazpi City' },
                    { code: '050505000', name: 'Guinobatan' },
                    { code: '050517000', name: 'Tabaco City' }
                ]
            },
            barangays: {
                '050508000': [
                    'Bagumbayan', 'Bonot', 'Cabangan', 'Dap-Dap', 'Dinagaan',
                    'EMs Barrio', 'Gogon', 'Ilawod East', 'Ilawod West',
                    'Oro Site-Magallanes St.', 'Rawis', 'Rizal Street',
                    'Taysan', 'Washington Drive'
                ],
                '050506000': [
                    'Alobo', 'Anislag', 'Bagumbayan', 'Bañadero', 'Binitayan',
                    'Busay', 'Kimantong', 'Market Area', 'Peñafrancia', 'Sagpon'
                ],
                '050505000': [
                    'Bagumbayan', 'Banao', 'Batbat', 'Calzada', 'Irisan',
                    'Masarawag', 'Mauraro', 'Poblacion', 'Quitago'
                ],
                '050517000': [
                    'Bacolod', 'Basagan', 'Bombon', 'Cobo', 'Comon',
                    'Divino Rostro', 'San Roque', 'Tayhi'
                ]
            }
        };

        function addressPartFields(form) {
            return form.querySelectorAll(
                '.js-address-part, [name="street_address"], [name="location_region"], [name="location_province"], [name="location_city"], [name="location_barangay"], [name="zipcode"]'
            );
        }

        function buildAddress(form) {
            var values = [];
            addressPartFields(form).forEach(function (field) {
                if (field.value.trim()) {
                    values.push(field.value.trim());
                }
            });
            return values.join(', ');
        }

        function normalizeText(value) {
            return String(value || '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, ' ')
                .trim();
        }

        function normalizePsgcCode(value) {
            var code = String(value || '').replace(/\D/g, '');
            return code.length === 10 && code.slice(-1) === '0' ? code.slice(0, 9) : code;
        }

        function isNcrRegion(regionCode, regionName) {
            regionCode = normalizePsgcCode(regionCode);
            regionName = normalizeText(regionName);

            return regionCode.indexOf('130000000') === 0 ||
                regionName.indexOf('national capital') !== -1 ||
                regionName.indexOf('ncr') !== -1 ||
                regionName.indexOf('metro manila') !== -1;
        }

        function calculateAge(value) {
            if (!value) {
                return '';
            }
            var birthdate = new Date(value);
            var today = new Date();
            var age = today.getFullYear() - birthdate.getFullYear();
            var monthDiff = today.getMonth() - birthdate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                age--;
            }
            return age >= 0 ? age : '';
        }

        function option(value, label, code) {
            var item = document.createElement('option');
            item.value = value;
            item.textContent = label;
            if (code) {
                item.dataset.code = code;
            }
            return item;
        }

        function resetSelect(select, label) {
            if (!select) {
                return;
            }
            select.innerHTML = '';
            select.appendChild(option('', label));
            select.disabled = true;
            refreshSelect2(select);
        }

        function fillSelect(select, items, placeholder, currentValue) {
            resetSelect(select, placeholder);
            items.forEach(function (item) {
                var name = typeof item === 'string' ? item : item.name;
                var code = typeof item === 'string' ? '' : item.code;
                select.appendChild(option(name, name, code));
            });
            select.disabled = false;
            if (currentValue) {
                select.value = currentValue;
            }
            if (currentValue && select.value !== currentValue) {
                var normalizedCurrent = normalizeText(currentValue);
                Array.prototype.some.call(select.options, function (item) {
                    var normalizedOption = normalizeText(item.value);
                    if (normalizedOption === normalizedCurrent || normalizedOption.indexOf(normalizedCurrent) !== -1 || normalizedCurrent.indexOf(normalizedOption) !== -1) {
                        select.value = item.value;
                        return true;
                    }
                    return false;
                });
            }
            if (currentValue && select.value !== currentValue) {
                select.appendChild(option(currentValue, currentValue));
                select.value = currentValue;
            }
            refreshSelect2(select);
        }

        function fetchJson(url) {
            return fetch(url).then(function (response) {
                if (!response.ok) {
                    throw new Error('Location request failed');
                }
                return response.json();
            });
        }

        function sortByName(items) {
            return items.sort(function (a, b) {
                var aName = typeof a === 'string' ? a : a.name;
                var bName = typeof b === 'string' ? b : b.name;
                return aName.localeCompare(bName);
            });
        }

        function loadScript(src) {
            return new Promise(function (resolve, reject) {
                var existing = document.querySelector('script[src="' + src + '"]');
                if (existing) {
                    existing.addEventListener('load', resolve);
                    existing.addEventListener('error', reject);
                    if (existing.dataset.loaded === 'true') {
                        resolve();
                    }
                    return;
                }

                var script = document.createElement('script');
                script.src = src;
                script.async = true;
                script.onload = function () {
                    script.dataset.loaded = 'true';
                    resolve();
                };
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        function ensureLeaflet() {
            if (window.L) {
                return Promise.resolve();
            }

            if (leafletLoadPromise) {
                return leafletLoadPromise;
            }

            if (!document.querySelector('link[data-leaflet-css]')) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                link.dataset.leafletCss = 'true';
                document.head.appendChild(link);
            }

            leafletLoadPromise = loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');

            return leafletLoadPromise;
        }

        function ensureSelect2() {
            if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
                return Promise.resolve();
            }

            if (select2LoadPromise) {
                return select2LoadPromise;
            }

            if (!document.querySelector('link[data-select2-css]')) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css';
                link.dataset.select2Css = 'true';
                document.head.appendChild(link);
            }

            select2LoadPromise = loadScript('https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js');

            return select2LoadPromise;
        }

        function initSelect2Field(select, form) {
            if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2 || !select || select.dataset.select2Ready === 'true') {
                return;
            }

            var modal = form.closest('.modal');
            var placeholder = select.dataset.placeholder || (select.options.length ? select.options[0].textContent : 'Select an option');
            var allowManualEntry = select.classList.contains('js-province') || select.classList.contains('js-city') || select.classList.contains('js-barangay');
            var options = {
                width: '100%',
                placeholder: placeholder,
                allowClear: !select.required,
                dropdownParent: modal ? jQuery(modal) : jQuery(document.body)
            };

            if (allowManualEntry) {
                options.tags = true;
                options.createTag = function (params) {
                    var term = jQuery.trim(params.term);
                    if (!term) {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                };
            }

            jQuery(select).select2(options);
            select.dataset.select2Ready = 'true';
        }

        function initFormSelect2(form) {
            if (!form) {
                return Promise.resolve();
            }

            return ensureSelect2()
                .then(function () {
                    form.querySelectorAll('select.js-select2').forEach(function (select) {
                        initSelect2Field(select, form);
                    });
                })
                .catch(function () {
                    // Native selects remain usable if Select2 CDN is unavailable.
                });
        }

        function refreshSelect2(select) {
            if (window.jQuery && jQuery.fn && jQuery.fn.select2 && select && select.dataset.select2Ready === 'true') {
                var form = select.closest('.dms-user-form');
                jQuery(select).select2('destroy');
                select.dataset.select2Ready = 'false';
                initSelect2Field(select, form);
                jQuery(select).trigger('change.select2');
            }
        }

        function bindSelectChange(select, namespace, handler) {
            if (!select) {
                return;
            }

            select.addEventListener('change', handler);

            if (window.jQuery) {
                jQuery(select).off('change.' + namespace).on('change.' + namespace, function (event) {
                    if (!event.originalEvent) {
                        handler.call(select, event);
                    }
                });
            }
        }

        document.querySelectorAll('.dms-user-form').forEach(function (form) {
            var role = form.querySelector('.js-role');
            var reference = form.querySelector('.js-reference');
            var birthdate = form.querySelector('.js-birthdate');
            var age = form.querySelector('.js-age');
            var sameAddress = form.querySelector('.js-same-address');
            var delivery = form.querySelector('.js-delivery-address');
            var completeAddress = form.querySelector('.js-complete-address');
            var avatarInput = form.querySelector('.js-avatar-input');
            var avatarPreview = form.querySelector('.js-avatar-preview');
            var latitude = form.querySelector('.js-latitude');
            var longitude = form.querySelector('.js-longitude');
            var pinLat = form.querySelector('.js-pin-lat');
            var pinLng = form.querySelector('.js-pin-lng');
            var locationMap = form.querySelector('.js-location-map');
            var mapInstance = null;
            var mapMarker = null;
            var areaRows = form.querySelector('.js-area-rows');
            var addAreaRow = form.querySelector('.js-add-area-row');
            var region = form.querySelector('.js-region, [name="region"], [name="location_region"], #location_region');
            var province = form.querySelector('.js-province, [name="province"], [name="location_province"], #location_province');
            var city = form.querySelector('.js-city, [name="city"], [name="location_city"], #location_city');
            var barangay = form.querySelector('.js-barangay, [name="barangay"], [name="location_barangay"], #location_barangay');
            var zipCode = form.querySelector('.js-zip-code, [name="zip_code"], [name="zipcode"], #zip_code, #zipcode');
            var locateAddress = form.querySelector('.js-locate-address');
            var useMyLocation = form.querySelector('.js-use-my-location');
            var mapStatus = form.querySelector('.js-map-status');
            var autoLocateTimer = null;
            var lastAutoLocatedAddress = '';

            initFormSelect2(form);

            function updateReference() {
                if (reference && role && !reference.value) {
                    var prefix = prefixes[role.value] || 'US';
                    reference.placeholder = prefix + '-' + new Date().getFullYear() + '-0001';
                }
            }

            function syncAddress() {
                var address = buildAddress(form);
                if (completeAddress) {
                    completeAddress.value = address;
                }
                if (sameAddress && sameAddress.checked && delivery) {
                    delivery.value = address;
                }
                scheduleAutoLocateAddress();
            }

            function syncPin() {
                if (pinLat && latitude) {
                    pinLat.textContent = latitude.value || '--';
                }
                if (pinLng && longitude) {
                    pinLng.textContent = longitude.value || '--';
                }
                if (mapMarker && latitude && longitude && latitude.value && longitude.value) {
                    var lat = parseFloat(latitude.value);
                    var lng = parseFloat(longitude.value);
                    if (!isFinite(lat) || !isFinite(lng)) {
                        return;
                    }
                    var position = [lat, lng];
                    mapMarker.setLatLng(position);
                    mapInstance.panTo(position);
                }
            }

            function showMapStatus(message, type) {
                if (!mapStatus) {
                    return;
                }

                mapStatus.className = 'alert py-2 px-3 mt-3 mb-0 js-map-status alert-' + (type || 'info');
                mapStatus.textContent = message;
                mapStatus.style.display = message ? '' : 'none';
            }

            function setPin(lat, lng, moveMap) {
                lat = parseFloat(lat);
                lng = parseFloat(lng);
                if (!isFinite(lat) || !isFinite(lng)) {
                    return;
                }
                if (latitude) {
                    latitude.value = lat.toFixed(7);
                }
                if (longitude) {
                    longitude.value = lng.toFixed(7);
                }
                if (pinLat) {
                    pinLat.textContent = latitude.value;
                }
                if (pinLng) {
                    pinLng.textContent = longitude.value;
                }
                if (mapMarker) {
                    mapMarker.setLatLng([lat, lng]);
                    mapMarker.bindPopup('Pinned delivery location');
                }
                if (moveMap && mapInstance) {
                    mapInstance.setView([lat, lng], Math.max(mapInstance.getZoom(), 17));
                }
            }

            function showMapFallback() {
                if (!locationMap) {
                    return;
                }

                locationMap.innerHTML = '<div class="map-fallback">Map could not load. You can still type latitude and longitude manually.</div>';
            }

            function createLeafletMap() {
                if (!locationMap || mapInstance) {
                    return;
                }

                var lat = parseFloat(latitude && latitude.value ? latitude.value : defaultLat);
                var lng = parseFloat(longitude && longitude.value ? longitude.value : defaultLng);
                if (!isFinite(lat) || !isFinite(lng)) {
                    lat = defaultLat;
                    lng = defaultLng;
                }

                mapInstance = L.map(locationMap, {
                    scrollWheelZoom: false
                }).setView([lat, lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(mapInstance);

                mapMarker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(mapInstance).bindPopup('Pinned delivery location');

                mapInstance.on('click', function (event) {
                    setPin(event.latlng.lat, event.latlng.lng, false);
                });

                mapMarker.on('dragend', function (event) {
                    var position = event.target.getLatLng();
                    setPin(position.lat, position.lng, false);
                });

                setTimeout(function () {
                    mapInstance.invalidateSize();
                }, 250);
            }

            function initLeafletMap() {
                if (!locationMap || mapInstance) {
                    return;
                }

                if (!locationMap.offsetParent) {
                    return;
                }

                ensureLeaflet()
                    .then(createLeafletMap)
                    .catch(showMapFallback);
            }

            function selectedCode(select) {
                if (!select || !select.selectedOptions.length) {
                    return '';
                }

                return normalizePsgcCode(select.selectedOptions[0].dataset.code || select.selectedOptions[0].value || '');
            }

            function addressForGeocode() {
                var address = buildAddress(form);
                return address ? address + ', Philippines' : '';
            }

            function hasCompleteLocationDetails() {
                var street = form.querySelector('[name="street_address"]');

                return !!(
                    street && street.value.trim() &&
                    region && region.value.trim() &&
                    city && city.value.trim() &&
                    barangay && barangay.value.trim()
                );
            }

            function scheduleAutoLocateAddress() {
                clearTimeout(autoLocateTimer);

                if (!hasCompleteLocationDetails()) {
                    return;
                }

                autoLocateTimer = setTimeout(function () {
                    var query = addressForGeocode();
                    if (!query || query === lastAutoLocatedAddress) {
                        return;
                    }

                    lastAutoLocatedAddress = query;
                    geocodeAddress(true);
                }, 900);
            }

            function geocodeAddress(isAutomatic) {
                var query = addressForGeocode();
                if (!query) {
                    if (!isAutomatic) {
                        showMapStatus('Complete the address first before locating it on the map.', 'warning');
                    }
                    return;
                }

                showMapStatus(isAutomatic ? 'Auto-locating completed address on the map...' : 'Locating address on the map...', 'info');

                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=ph&q=' + encodeURIComponent(query), {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Geocoding request failed');
                        }
                        return response.json();
                    })
                    .then(function (results) {
                        if (!results.length) {
                            if (!isAutomatic) {
                                showMapStatus('Address not found. Please drag the pin manually or type coordinates.', 'warning');
                            } else {
                                showMapStatus('Address completed, but no map match was found. You may drag the pin manually.', 'warning');
                            }
                            return;
                        }

                        initLeafletMap();
                        setPin(results[0].lat, results[0].lon, true);
                        showMapStatus(isAutomatic ? 'Pin automatically moved to the completed address. Please check if it is exact.' : 'Pin moved to the best match for your address. Please check if it is exact.', 'success');
                    })
                    .catch(function () {
                        if (!isAutomatic) {
                            showMapStatus('Unable to locate address right now. Please drag the pin manually or type coordinates.', 'warning');
                        }
                    });
            }

            function lookupZipCode(cityName, barangayName) {
                var normalizedCity = normalizeText(cityName);
                var normalizedBarangay = normalizeText(barangayName);
                var zip = '';

                if (cityName && barangayName) {
                    zip = barangayZipCodes[cityName + '|' + barangayName] || '';

                    if (!zip) {
                        Object.keys(barangayZipCodes).some(function (knownLocation) {
                            var parts = knownLocation.split('|');
                            if (normalizeText(parts[0]) === normalizedCity && normalizeText(parts[1]) === normalizedBarangay) {
                                zip = barangayZipCodes[knownLocation];
                                return true;
                            }
                            return false;
                        });
                    }
                }

                if (!zip && cityName) {
                    zip = zipCodes[cityName] || '';

                    if (!zip) {
                        Object.keys(zipCodes).some(function (knownCity) {
                            if (normalizeText(knownCity) === normalizedCity) {
                                zip = zipCodes[knownCity];
                                return true;
                            }
                            return false;
                        });
                    }
                }

                return zip;
            }

            function setZipFromLocation() {
                if (!zipCode || !city) {
                    return;
                }

                var cityName = city.value || '';
                var barangayName = barangay ? (barangay.value || '') : '';
                var zip = lookupZipCode(cityName, barangayName);

                if (zip) {
                    zipCode.value = zip;
                    zipCode.dataset.autoZip = zip;
                } else if (zipCode.dataset.autoZip && zipCode.value === zipCode.dataset.autoZip) {
                    zipCode.value = '';
                    zipCode.dataset.autoZip = '';
                }
                zipCode.readOnly = !!zip;
                zipCode.placeholder = zip ? 'Auto generated' : 'Enter zip code';
                syncAddress();
            }

            function loadBarangays(cityCode, currentValue) {
                resetSelect(barangay, '-- Loading Barangays --');
                cityCode = normalizePsgcCode(cityCode);
                if (!cityCode) {
                    if (city && city.value) {
                        fillSelect(barangay, [], '-- Type Barangay --', currentValue);
                        setZipFromLocation();
                        return Promise.resolve();
                    }

                    resetSelect(barangay, '-- Select City First --');
                    setZipFromLocation();
                    return Promise.resolve();
                }
                return fetchJson(psgcBase + '/cities-municipalities/' + cityCode + '/barangays')
                    .then(function (items) {
                        fillSelect(barangay, sortByName(items), '-- Select Barangay --', currentValue);
                    })
                    .catch(function () {
                        fillSelect(barangay, fallbackLocations.barangays[cityCode] || [], '-- Select Barangay --', currentValue);
                    })
                    .then(setZipFromLocation);
            }

            function loadCities(provinceCode, currentValue, currentBarangay) {
                resetSelect(city, '-- Loading Cities --');
                resetSelect(barangay, '-- Select City First --');
                provinceCode = normalizePsgcCode(provinceCode);
                if (!provinceCode) {
                    if (province && province.value) {
                        fillSelect(city, [], '-- Type City/Municipality --', currentValue);
                        return loadBarangays(selectedCode(city), currentBarangay);
                    }

                    resetSelect(city, '-- Select Province First --');
                    return Promise.resolve();
                }
                return fetchJson(psgcBase + '/provinces/' + provinceCode + '/cities-municipalities')
                    .then(function (items) {
                        fillSelect(city, sortByName(items), '-- Select City/Municipality --', currentValue);
                    })
                    .catch(function () {
                        fillSelect(city, fallbackLocations.cities[provinceCode] || [], '-- Select City/Municipality --', currentValue);
                    })
                    .then(function () {
                        return loadBarangays(selectedCode(city), currentBarangay);
                    });
            }

            function loadCitiesByRegion(regionCode, currentValue, currentBarangay) {
                resetSelect(city, '-- Loading Cities --');
                resetSelect(barangay, '-- Select City First --');
                regionCode = normalizePsgcCode(regionCode);
                if (!regionCode) {
                    resetSelect(city, '-- Select Region First --');
                    return Promise.resolve();
                }

                return fetchJson(psgcBase + '/regions/' + regionCode + '/cities-municipalities')
                    .then(function (items) {
                        fillSelect(city, sortByName(items), '-- Select City/Municipality --', currentValue);
                    })
                    .catch(function () {
                        fillSelect(city, [], '-- Select City/Municipality --', currentValue);
                    })
                    .then(function () {
                        return loadBarangays(selectedCode(city), currentBarangay);
                    });
            }

            function loadProvinces(regionCode, currentValue, currentCity, currentBarangay) {
                resetSelect(province, '-- Loading Provinces --');
                resetSelect(city, '-- Select Province First --');
                resetSelect(barangay, '-- Select City First --');
                regionCode = normalizePsgcCode(regionCode);
                if (!regionCode) {
                    resetSelect(province, '-- Select Region First --');
                    return Promise.resolve();
                }

                if (isNcrRegion(regionCode, region ? region.value : '')) {
                    fillSelect(province, [{ code: regionCode, name: 'Metro Manila' }], '-- Province --', 'Metro Manila');
                    province.value = 'Metro Manila';
                    province.dataset.current = 'Metro Manila';
                    refreshSelect2(province);
                    return loadCitiesByRegion(regionCode, currentCity, currentBarangay);
                }

                return fetchJson(psgcBase + '/regions/' + regionCode + '/provinces')
                    .then(function (items) {
                        if (!items.length) {
                            resetSelect(province, '-- No Province Required --');
                            return loadCitiesByRegion(regionCode, currentCity, currentBarangay);
                        }

                        fillSelect(province, sortByName(items), '-- Select Province --', currentValue);
                        return loadCities(selectedCode(province), currentCity, currentBarangay);
                    })
                    .catch(function () {
                        var fallbackProvinces = fallbackLocations.provinces[regionCode] || [];
                        if (!fallbackProvinces.length) {
                            resetSelect(province, '-- No Province Required --');
                            return loadCitiesByRegion(regionCode, currentCity, currentBarangay);
                        }

                        fillSelect(province, fallbackProvinces, '-- Select Province --', currentValue);
                        return loadCities(selectedCode(province), currentCity, currentBarangay);
                    });
            }

            function initLocations() {
                if (!region || !province || !city || !barangay) {
                    return;
                }

                var currentRegion = region.dataset.current || '';
                var currentProvince = province.dataset.current || '';
                var currentCity = city.dataset.current || '';
                var currentBarangay = barangay.dataset.current || '';
                var existingRegions = Array.prototype.slice.call(region.options)
                    .filter(function (item) {
                        return item.value;
                    })
                    .map(function (item) {
                        return {
                            code: normalizePsgcCode(item.dataset.code || item.value),
                            name: item.value
                        };
                    });

                resetSelect(region, '-- Loading Regions --');
                resetSelect(province, '-- Select Region First --');
                resetSelect(city, '-- Select Province First --');
                resetSelect(barangay, '-- Select City First --');

                fetchJson(psgcBase + '/regions')
                    .then(function (items) {
                        fillSelect(region, sortByName(items), '-- Select Region --', currentRegion);
                    })
                    .catch(function () {
                        fillSelect(region, existingRegions.length ? existingRegions : fallbackLocations.regions, '-- Select Region --', currentRegion);
                    })
                    .then(function () {
                        return loadProvinces(selectedCode(region), currentProvince, currentCity, currentBarangay);
                    });

                bindSelectChange(region, 'dmsLocation', function () {
                    region.dataset.current = region.value;
                    loadProvinces(selectedCode(region), '', '', '');
                    syncAddress();
                });
                bindSelectChange(province, 'dmsLocation', function () {
                    loadCities(selectedCode(province), '', '');
                    syncAddress();
                });
                bindSelectChange(city, 'dmsLocation', function () {
                    loadBarangays(selectedCode(city), '');
                    setZipFromLocation();
                });
                bindSelectChange(barangay, 'dmsLocation', function () {
                    setZipFromLocation();
                    syncAddress();
                });
            }

            if (role) {
                bindSelectChange(role, 'dmsRole', updateReference);
                updateReference();
            }

            if (addAreaRow && areaRows) {
                addAreaRow.addEventListener('click', function () {
                    var firstRow = areaRows.querySelector('.project-row');
                    if (!firstRow) {
                        return;
                    }

                    var clone = firstRow.cloneNode(true);
                    clone.querySelectorAll('input').forEach(function (input) {
                        input.value = '';
                    });
                    areaRows.appendChild(clone);
                });

                areaRows.addEventListener('click', function (event) {
                    if (!event.target.classList.contains('js-remove-area-row')) {
                        return;
                    }

                    var rows = areaRows.querySelectorAll('.project-row');
                    if (rows.length > 1) {
                        event.target.closest('.project-row').remove();
                    }
                });
            }

            if (birthdate && age) {
                birthdate.addEventListener('change', function () {
                    age.value = calculateAge(birthdate.value);
                });
                if (birthdate.value && !age.value) {
                    age.value = calculateAge(birthdate.value);
                }
            }

            addressPartFields(form).forEach(function (field) {
                field.addEventListener('input', syncAddress);
                field.addEventListener('change', syncAddress);
                if (window.jQuery && field.tagName === 'SELECT') {
                    jQuery(field).off('change.dmsAddress').on('change.dmsAddress', function (event) {
                        if (!event.originalEvent) {
                            syncAddress();
                        }
                    });
                }
            });

            if (sameAddress) {
                sameAddress.addEventListener('change', syncAddress);
            }

            if (avatarInput && avatarPreview) {
                avatarInput.addEventListener('change', function () {
                    if (avatarInput.files && avatarInput.files[0]) {
                        avatarPreview.src = URL.createObjectURL(avatarInput.files[0]);
                    }
                });
            }

            if (latitude) {
                latitude.addEventListener('input', syncPin);
            }

            if (longitude) {
                longitude.addEventListener('input', syncPin);
            }

            var defaultPin = form.querySelector('.js-default-pin');
            if (defaultPin) {
                defaultPin.addEventListener('click', function () {
                    initLeafletMap();
                    setPin(defaultLat, defaultLng, true);
                    showMapStatus('Default pin set to Legazpi City, Albay. Drag the marker for the exact delivery point.', 'info');
                });
            }

            if (locateAddress) {
                locateAddress.addEventListener('click', function () {
                    initLeafletMap();
                    geocodeAddress();
                });
            }

            if (useMyLocation) {
                useMyLocation.addEventListener('click', function () {
                    if (!navigator.geolocation) {
                        showMapStatus('Your browser does not support current location.', 'warning');
                        return;
                    }

                    showMapStatus('Getting your current location...', 'info');
                    navigator.geolocation.getCurrentPosition(function (position) {
                        initLeafletMap();
                        setPin(position.coords.latitude, position.coords.longitude, true);
                        showMapStatus('Pin moved to your current location. Please check if this is the delivery point.', 'success');
                    }, function () {
                        showMapStatus('Unable to get current location. Please allow location permission or drag the pin manually.', 'warning');
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });
                });
            }

            syncAddress();
            syncPin();
            initLocations();
            initLeafletMap();

            var modal = form.closest('.modal');
            if (modal && window.jQuery) {
                jQuery(modal).on('shown.bs.modal', function () {
                    initFormSelect2(form);
                    initLeafletMap();
                    if (mapInstance) {
                        setTimeout(function () {
                            mapInstance.invalidateSize();
                        }, 150);
                    } else {
                        initLeafletMap();
                    }
                });
            }
        });
    });
</script>
