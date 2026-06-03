<div class="modal fade customer-alert-modal" id="homeModal" tabindex="-1" aria-labelledby="homeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header customer-alert-header">
        <div class="d-flex align-items-center gap-3">
          <span class="customer-alert-icon">
            <i class="bi bi-exclamation-circle"></i>
          </span>
          <div>
            <p class="customer-alert-kicker mb-1">Customer follow-up alert</p>
            <h5 class="modal-title text-white mb-0" id="homeModalLabel">Last purchase was more than 7 days ago</h5>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-0">
        <div class="customer-alert-toolbar">
          <div class="customer-alert-summary">
            <span class="customer-alert-count" id="alertVisibleCount">{{ $customers_less->count() }}</span>
            <span class="customer-alert-label">of {{ $customers_less->count() }} customers shown</span>
            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-sm-2">7+ days inactive</span>
          </div>

          <div class="customer-alert-search">
            <i class="bi bi-search"></i>
            <input type="search" id="alertSearchInput" class="form-control" placeholder="Search client, address, SPO, center, or date" autocomplete="off">
            <button type="button" class="btn btn-link customer-alert-clear" id="alertSearchClear" aria-label="Clear search">
              <i class="bi bi-x-circle"></i>
            </button>
          </div>
        </div>

        <div class="customer-alert-table-wrap">
          <div class="table-responsive">
            <table class="table align-middle mb-0 customer-alert-table" id="table-alert">
              <thead>
                <tr>
                  <th scope="col">Client</th>
                  <th scope="col">Address</th>
                  <th scope="col">SPO</th>
                  <th scope="col">Center</th>
                  <th scope="col">Last Purchase</th>
                </tr>
              </thead>
              <tbody id="alertTableBody">
                @foreach($customers_less as $cus)
                  <tr class="customer-alert-row">
                    <td>
                      <div class="fw-semibold text-dark">{{ strtoupper($cus->name) }}</div>
                    </td>
                    <td class="text-wrap">
                      <span class="text-muted">{{ $cus->location_barangay }}, {{ $cus->location_city }}, Bicol Region</span>
                    </td>
                    <td>{{ $cus->spo }}</td>
                    <td>{{ $cus->center }}</td>
                    <td>
                      <span class="customer-alert-date">{{ date('M d, Y', strtotime($cus->latestTransaction->date)) }}</span>
                    </td>
                  </tr>
                @endforeach
                <tr id="alertNoResultsRow" class="d-none">
                  <td colspan="5" class="text-center text-muted py-5">
                    <i class="bi bi-search d-block fs-2 mb-2"></i>
                    No customers match your search.
                  </td>
                </tr>
                @if($customers_less->isEmpty())
                  <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                      <i class="bi bi-check-circle d-block fs-2 text-success mb-2"></i>
                      No inactive customers found.
                    </td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="modal-footer customer-alert-footer">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-info text-white" onclick="printTable()">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  .customer-alert-modal .modal-content {
    border-radius: 10px;
    overflow: hidden;
  }

  .customer-alert-header {
    background: linear-gradient(135deg, #157fbf 0%, #6dbcf1 58%, #5BC2E7 100%);
  }

  .customer-alert-icon {
    align-items: center;
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.26);
    border-radius: 8px;
    color: #fff;
    display: inline-flex;
    flex: 0 0 44px;
    font-size: 24px;
    height: 44px;
    justify-content: center;
    width: 44px;
  }

  .customer-alert-kicker {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0;
    text-transform: uppercase;
  }

  .customer-alert-toolbar {
    align-items: center;
    background: #f8fafc;
    border-bottom: 1px solid #e9edf3;
    display: flex;
    gap: 16px;
    justify-content: space-between;
    padding: 18px 24px;
  }

  .customer-alert-summary {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    min-width: max-content;
  }

  .customer-alert-count {
    color: #1f2937;
    font-size: 1.65rem;
    font-weight: 800;
    line-height: 1;
  }

  .customer-alert-label {
    color: #667085;
    font-weight: 600;
  }

  .customer-alert-search {
    max-width: 460px;
    position: relative;
    width: 100%;
  }

  .customer-alert-search .bi-search {
    color: #98a2b3;
    left: 14px;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
  }

  .customer-alert-search .form-control {
    border-color: #d0d5dd;
    border-radius: 8px;
    min-height: 42px;
    padding-left: 40px;
    padding-right: 42px;
  }

  .customer-alert-search .form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.14);
  }

  .customer-alert-clear {
    color: #98a2b3;
    display: none;
    padding: 0;
    position: absolute;
    right: 14px;
    text-decoration: none;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
  }

  .customer-alert-search.has-value .customer-alert-clear {
    display: inline-flex;
  }

  .customer-alert-table-wrap {
    max-height: min(62vh, 620px);
    overflow: auto;
  }

  .customer-alert-table {
    min-width: 860px;
  }

  .customer-alert-table thead th {
    background: #fff;
    border-bottom: 1px solid #e9edf3;
    color: #475467;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: 0;
    padding: 14px 20px;
    position: sticky;
    text-transform: uppercase;
    top: 0;
    z-index: 1;
  }

  .customer-alert-table tbody td {
    border-color: #eef2f6;
    color: #344054;
    padding: 16px 20px;
    vertical-align: middle;
  }

  .customer-alert-table tbody tr:hover td {
    background: #f6fbff;
  }

  .customer-alert-date {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 999px;
    color: #9a3412;
    display: inline-flex;
    font-size: 0.83rem;
    font-weight: 700;
    padding: 5px 10px;
    white-space: nowrap;
  }

  .customer-alert-footer {
    background: #fff;
    border-top: 1px solid #e9edf3;
    padding: 16px 24px;
  }

  @media (max-width: 767.98px) {
    .customer-alert-toolbar {
      align-items: stretch;
      flex-direction: column;
    }

    .customer-alert-summary {
      min-width: 0;
    }

    .customer-alert-search {
      max-width: none;
    }
  }

  @media (max-width: 575.98px) {
    .customer-alert-header,
    .customer-alert-toolbar,
    .customer-alert-footer {
      padding-left: 16px;
      padding-right: 16px;
    }
  }
</style>

<script>
  function filterAlertTable() {
    var input = document.getElementById('alertSearchInput');
    var clearButton = document.getElementById('alertSearchClear');
    var searchBox = input.closest('.customer-alert-search');
    var rows = document.querySelectorAll('#alertTableBody .customer-alert-row');
    var noResultsRow = document.getElementById('alertNoResultsRow');
    var visibleCount = document.getElementById('alertVisibleCount');
    var query = input.value.trim().toLowerCase();
    var shown = 0;

    searchBox.classList.toggle('has-value', query.length > 0);

    rows.forEach(function(row) {
      var isMatch = row.innerText.toLowerCase().indexOf(query) !== -1;
      row.classList.toggle('d-none', !isMatch);

      if (isMatch) {
        shown++;
      }
    });

    visibleCount.textContent = shown;
    noResultsRow.classList.toggle('d-none', shown !== 0 || rows.length === 0);
    clearButton.disabled = query.length === 0;
  }

  document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('alertSearchInput');
    var clearButton = document.getElementById('alertSearchClear');

    if (!input || !clearButton) {
      return;
    }

    input.addEventListener('input', filterAlertTable);
    clearButton.addEventListener('click', function() {
      input.value = '';
      input.focus();
      filterAlertTable();
    });
    filterAlertTable();
  });

  function escapePrintHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function printTable() {
    var sourceRows = document.querySelectorAll('#alertTableBody .customer-alert-row:not(.d-none)');
    var searchInput = document.getElementById('alertSearchInput');
    var searchText = searchInput && searchInput.value.trim() ? searchInput.value.trim() : '';
    var tableRows = '';

    sourceRows.forEach(function(row) {
      var cells = row.querySelectorAll('td');
      tableRows += '<tr>';
      cells.forEach(function(cell) {
        tableRows += '<td>' + escapePrintHtml(cell.innerText.trim()) + '</td>';
      });
      tableRows += '</tr>';
    });

    if (!tableRows) {
      tableRows = '<tr><td colspan="5" class="empty-row">No customers to print.</td></tr>';
    }

    var printWindow = window.open('', 'printCustomerAlert', 'height=700,width=1000');

    if (!printWindow) {
      alert('Please allow pop-ups to print this table.');
      return;
    }

    printWindow.document.open();
    printWindow.document.write(`
      <html>
        <head>
          <title>Print Table</title>
          <style>
            * { box-sizing: border-box; }
            body { color:#111827; font-family: Arial, sans-serif; padding:24px; }
            h3 { margin:0 0 6px; }
            p { color:#667085; margin:0 0 18px; }
            .print-meta { color:#344054; font-size:12px; margin-bottom:14px; }
            table { border-collapse: collapse; width:100%; }
            th, td { border:1px solid #d0d5dd; padding:10px; text-align:left; vertical-align:top; }
            th { background:#f2f4f7; color:#344054; font-size:12px; text-transform:uppercase; }
            td { font-size:13px; }
            .empty-row { color:#667085; padding:24px; text-align:center; }
            @media print {
              body { padding:0; }
              thead { display: table-header-group; }
              tr { page-break-inside: avoid; }
            }
          </style>
        </head>
        <body>
          <h3>Customer follow-up alert</h3>
          <p>Last purchase was more than 7 days ago.</p>
          <div class="print-meta">
            Showing ${sourceRows.length} customer${sourceRows.length === 1 ? '' : 's'}${searchText ? ' filtered by "' + escapePrintHtml(searchText) + '"' : ''}
          </div>
          <table>
            <thead>
              <tr>
                <th>Client</th>
                <th>Address</th>
                <th>SPO</th>
                <th>Center</th>
                <th>Last Purchase</th>
              </tr>
            </thead>
            <tbody>${tableRows}</tbody>
          </table>
          <script>
            window.onload = function() {
              window.focus();
              window.print();
            };
            window.onafterprint = function() {
              window.close();
            };
          <\/script>
        </body>
      </html>
    `);
    printWindow.document.close();
  }
</script>
