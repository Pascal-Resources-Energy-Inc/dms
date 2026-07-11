<div class="modal fade access-modal" id="accessUserModal" tabindex="-1" aria-labelledby="accessUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header access-modal-header">
                <div>
                    <h5 class="modal-title" id="accessUserModalLabel">
                        <i class="fas fa-key me-2"></i>User Access
                    </h5>
                    <p class="access-subtitle">Manage dashboard permissions for this account.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="access_user_id">

                <div class="access-user-summary">
                    <div class="access-avatar" id="accessUserInitials">--</div>
                    <div>
                        <div class="access-user-name" id="accessUserName">Select user</div>
                        <div class="access-user-meta">
                            <span id="accessUserEmail">No email loaded</span>
                            <span class="access-role-pill" id="accessUserRole">Role</span>
                        </div>
                    </div>
                </div>

                <div class="access-section">
                    <div class="access-section-title">
                        <div>
                            <strong>Detailed Module Permissions</strong>
                            <small>Set View, Add, Edit, and Delete access per submodule.</small>
                        </div>
                    </div>

                    <div class="access-tools">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="checkAllAccess">
                            <i class="fas fa-check-double me-1"></i>Allow All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllAccess">
                            <i class="fas fa-times me-1"></i>Clear
                        </button>
                    </div>

                    <div class="access-matrix">
                        <div class="access-matrix-head">
                            <span>Module / Submodule</span>
                            <span>View</span>
                            <span>Add</span>
                            <span>Edit</span>
                            <span>Delete</span>
                        </div>

                        <div class="access-module-group">
                            <div class="access-module-heading">
                                <i class="fas fa-users-cog"></i>
                                <span>Administration</span>
                            </div>
                            <div class="access-row">
                                <div><strong>Users</strong><small>Account list and access control</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="users" data-submodule="accounts" data-action="view"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" id="can_add" data-module="users" data-submodule="accounts" data-action="add"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" id="can_edit" data-module="users" data-submodule="accounts" data-action="edit"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" id="can_delete" data-module="users" data-submodule="accounts" data-action="delete"><span></span></label>
                            </div>
                        </div>

                        <div class="access-module-group">
                            <div class="access-module-heading">
                                <i class="fas fa-handshake"></i>
                                <span>Partners</span>
                            </div>
                            <div class="access-row">
                                <div><strong>Distributors</strong><small>Provincial and Area Distributor records</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="distributors" data-submodule="records" data-action="view"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="distributors" data-submodule="records" data-action="add"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="distributors" data-submodule="records" data-action="edit"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="distributors" data-submodule="records" data-action="delete"><span></span></label>
                            </div>
                            <div class="access-row">
                                <div><strong>Dealers</strong><small>Dealer and Mega Dealer records</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="dealers" data-submodule="records" data-action="view"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="dealers" data-submodule="records" data-action="add"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="dealers" data-submodule="records" data-action="edit"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="dealers" data-submodule="records" data-action="delete"><span></span></label>
                            </div>
                            <div class="access-row">
                                <div><strong>Customers</strong><small>Customer records and profiles</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="customers" data-submodule="records" data-action="view"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="customers" data-submodule="records" data-action="add"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="customers" data-submodule="records" data-action="edit"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="customers" data-submodule="records" data-action="delete"><span></span></label>
                            </div>
                        </div>

                        <div class="access-module-group">
                            <div class="access-module-heading">
                                <i class="fas fa-cash-register"></i>
                                <span>Operations</span>
                            </div>
                            <div class="access-row">
                                <div><strong>Transactions</strong><small>Sales and payment activity</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="transactions" data-submodule="sales" data-action="view"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="transactions" data-submodule="sales" data-action="add"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="transactions" data-submodule="sales" data-action="edit"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="transactions" data-submodule="sales" data-action="delete"><span></span></label>
                            </div>
                            <div class="access-row">
                                <div><strong>Purchase Orders</strong><small>AD purchase order workflow</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="purchase_orders" data-submodule="adpo" data-action="view"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="purchase_orders" data-submodule="adpo" data-action="add"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="purchase_orders" data-submodule="adpo" data-action="edit"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="purchase_orders" data-submodule="adpo" data-action="delete"><span></span></label>
                            </div>
                            <div class="access-row">
                                <div><strong>Inventory</strong><small>Stock reports and inventory tools</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="inventory" data-submodule="stock" data-action="view"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="inventory" data-submodule="stock" data-action="add"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="inventory" data-submodule="stock" data-action="edit"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="inventory" data-submodule="stock" data-action="delete"><span></span></label>
                            </div>
                        </div>

                        <div class="access-module-group">
                            <div class="access-module-heading">
                                <i class="fas fa-cog"></i>
                                <span>Settings and Rewards</span>
                            </div>
                            <div class="access-row">
                                <div><strong>Items</strong><small>Products, items, and catalog setup</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="settings" data-submodule="items" data-action="view"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="settings" data-submodule="items" data-action="add"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="settings" data-submodule="items" data-action="edit"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="settings" data-submodule="items" data-action="delete"><span></span></label>
                            </div>
                            <div class="access-row">
                                <div><strong>Rewards</strong><small>Reward setup and maintenance</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="settings" data-submodule="rewards" data-action="view"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" id="can_add_rewards" data-module="settings" data-submodule="rewards" data-action="add"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" id="can_edit_rewards" data-module="settings" data-submodule="rewards" data-action="edit"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" id="can_delete_rewards" data-module="settings" data-submodule="rewards" data-action="delete"><span></span></label>
                            </div>
                            <div class="access-row">
                                <div><strong>Vouchers and Raffles</strong><small>Voucher, raffle, and area settings</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="settings" data-submodule="campaigns" data-action="view"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="settings" data-submodule="campaigns" data-action="add"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="settings" data-submodule="campaigns" data-action="edit"><span></span></label>
                                <label><input type="checkbox" class="access-permission-check" data-module="settings" data-submodule="campaigns" data-action="delete"><span></span></label>
                            </div>
                        </div>

                        <div class="access-module-group">
                            <div class="access-module-heading">
                                <i class="fas fa-chart-line"></i>
                                <span>Reports</span>
                            </div>
                            <div class="access-row">
                                <div><strong>Sales Reports</strong><small>Daily and monthly sales reports</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="reports" data-submodule="sales" data-action="view"><span></span></label>
                                <label class="is-disabled"><input type="checkbox" disabled><span></span></label>
                                <label class="is-disabled"><input type="checkbox" disabled><span></span></label>
                                <label class="is-disabled"><input type="checkbox" disabled><span></span></label>
                            </div>
                            <div class="access-row">
                                <div><strong>Inventory and Aging Reports</strong><small>Stock level, aging, and voucher history</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="reports" data-submodule="operations" data-action="view"><span></span></label>
                                <label class="is-disabled"><input type="checkbox" disabled><span></span></label>
                                <label class="is-disabled"><input type="checkbox" disabled><span></span></label>
                                <label class="is-disabled"><input type="checkbox" disabled><span></span></label>
                            </div>
                            <div class="access-row">
                                <div><strong>SEDP Reports</strong><small>Sign up and repeat purchase incentives</small></div>
                                <label><input type="checkbox" class="access-permission-check" data-module="reports" data-submodule="sedp" data-action="view"><span></span></label>
                                <label class="is-disabled"><input type="checkbox" disabled><span></span></label>
                                <label class="is-disabled"><input type="checkbox" disabled><span></span></label>
                                <label class="is-disabled"><input type="checkbox" disabled><span></span></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer access-modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary access-save-btn" id="saveAccess">
                    <i class="fas fa-save me-1"></i>
                    <span>Save Access</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .access-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }

    .access-modal-header {
        align-items: flex-start;
        background: #f8fbff;
        border-bottom: 1px solid #e4edf7;
        padding: 18px 20px;
    }

    .access-modal-header .modal-title {
        color: #102a43;
        font-weight: 800;
        line-height: 1.2;
    }

    .access-subtitle {
        color: #64748b;
        font-size: 13px;
        margin: 4px 0 0;
    }

    .access-user-summary {
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #dbe7f3;
        border-radius: 8px;
        background: #ffffff;
        padding: 12px;
        margin-bottom: 14px;
    }

    .access-avatar {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #e7f1ff;
        color: #0d6efd;
        font-size: 13px;
        font-weight: 900;
        flex: 0 0 auto;
    }

    .access-user-name {
        color: #102a43;
        font-weight: 800;
    }

    .access-user-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        color: #64748b;
        font-size: 12px;
        margin-top: 2px;
    }

    .access-role-pill {
        border-radius: 999px;
        background: #eaf7ef;
        color: #16794c;
        font-size: 11px;
        font-weight: 800;
        padding: 3px 8px;
    }

    .access-section {
        border: 1px solid #e2eaf3;
        border-radius: 8px;
        padding: 12px;
    }

    .access-section + .access-section {
        margin-top: 12px;
    }

    .access-section-title {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .access-section-title strong,
    .access-section-title small {
        display: block;
    }

    .access-section-title strong {
        color: #102a43;
        font-size: 14px;
    }

    .access-section-title small {
        color: #64748b;
        font-size: 12px;
        margin-top: 2px;
    }

    .access-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .access-grid-modules {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .access-tools {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .access-tools .btn {
        display: inline-flex;
        align-items: center;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 800;
    }

    .access-matrix {
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    .access-matrix-head,
    .access-row {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) repeat(4, 74px);
        align-items: stretch;
    }

    .access-matrix-head {
        background: #f1f6fb;
        color: #334e68;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .access-matrix-head span {
        padding: 9px 10px;
        text-align: center;
    }

    .access-matrix-head span:first-child {
        text-align: left;
    }

    .access-module-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8fbff;
        border-top: 1px solid #e2eaf3;
        border-bottom: 1px solid #e2eaf3;
        color: #102a43;
        font-size: 13px;
        font-weight: 900;
        padding: 10px;
    }

    .access-module-group:first-of-type .access-module-heading {
        border-top: 0;
    }

    .access-row {
        min-height: 58px;
        border-bottom: 1px solid #edf2f7;
    }

    .access-row:last-child {
        border-bottom: 0;
    }

    .access-row > div {
        padding: 10px;
    }

    .access-row strong,
    .access-row small {
        display: block;
        line-height: 1.25;
    }

    .access-row strong {
        color: #102a43;
        font-size: 13px;
        font-weight: 900;
    }

    .access-row small {
        color: #64748b;
        font-size: 11px;
        margin-top: 2px;
    }

    .access-row label {
        display: flex;
        align-items: center;
        justify-content: center;
        border-left: 1px solid #edf2f7;
        cursor: pointer;
        margin: 0;
    }

    .access-row label input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .access-row label span {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #fff;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }

    .access-row label span::after {
        content: "\2713";
        font-size: 13px;
        font-weight: 900;
        opacity: 0;
    }

    .access-row label input:checked + span {
        border-color: #0d6efd;
        background: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
    }

    .access-row label input:checked + span::after {
        opacity: 1;
    }

    .access-row label.is-disabled {
        cursor: not-allowed;
        background: #f8fafc;
    }

    .access-row label.is-disabled span {
        background: #e5e7eb;
        border-color: #e5e7eb;
    }

    .access-option {
        min-height: 86px;
        display: flex;
        align-items: flex-start;
        gap: 9px;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        padding: 10px;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }

    .access-option:hover {
        border-color: #8bb7ef;
        background: #f8fbff;
    }

    .access-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .access-option-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #edf2f7;
        color: #52606d;
        flex: 0 0 auto;
    }

    .access-option strong,
    .access-option small {
        display: block;
        line-height: 1.25;
    }

    .access-option strong {
        color: #102a43;
        font-size: 13px;
        font-weight: 800;
    }

    .access-option small {
        color: #64748b;
        font-size: 11px;
        margin-top: 2px;
    }

    .access-option:has(input:checked) {
        border-color: #0d6efd;
        background: #eef6ff;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
    }

    .access-option:has(input:checked) .access-option-icon {
        background: #0d6efd;
        color: #fff;
    }

    .access-modal-footer {
        border-top: 1px solid #e4edf7;
        padding: 14px 20px;
    }

    .access-save-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    @media (max-width: 575.98px) {
        .access-grid {
            grid-template-columns: 1fr;
        }

        .access-modal .modal-dialog {
            margin: 8px;
        }

        .access-matrix {
            overflow-x: auto;
        }

        .access-matrix-head,
        .access-row {
            min-width: 540px;
        }

        .access-modal-footer .btn {
            width: 100%;
        }
    }
</style>
