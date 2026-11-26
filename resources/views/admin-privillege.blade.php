<div class="modal fade access-modal" id="accessUserModal" tabindex="-1" aria-labelledby="accessUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
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
                <p id="accessPersonnelNote" class="small text-muted mb-3" hidden>These permissions apply only to this personnel account. The assigned distributor keeps its own access.</p>

                <div class="access-section">
                    <div class="access-section-title">
                        <div>
                            <strong>Detailed Module Permissions</strong>
                            <small>Turn on every module at once, or fine-tune access by module and action.</small>
                        </div>
                    </div>

                    <div class="access-tools">
                        <label class="access-all-toggle" for="allModulesAccess">
                            <input type="checkbox" id="allModulesAccess">
                            <span class="access-toggle-ui" aria-hidden="true"></span>
                            <span><strong>All modules access</strong><small>Enable every available permission</small></span>
                        </label>
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

                        @foreach(config('user_access_modules') as $group)
                            @php
                                $groupModules = collect($group['modules'])->map(function ($definition) {
                                    return explode('.', $definition['key'], 2)[0];
                                })->unique()->implode(' ');
                            @endphp
                            <div class="access-module-group" data-module-group="{{ $groupModules }}">
                                <div class="access-module-heading">
                                    <span><i class="fas {{ $group['icon'] }}"></i> {{ $group['label'] }}</span>
                                    <label class="module-toggle"><input type="checkbox" class="module-access-toggle" data-modules="{{ $groupModules }}"><span>All</span></label>
                                </div>
                                @foreach($group['modules'] as $definition)
                                    @php
                                        list($module, $submodule) = explode('.', $definition['key'], 2);
                                    @endphp
                                    <div class="access-row">
                                        <div><strong>{{ $definition['label'] }}</strong><small>{{ $definition['description'] }}</small></div>
                                        @foreach(['view', 'add', 'edit', 'delete'] as $action)
                                            @php
                                                $legacyId = $action !== 'view' && $definition['key'] === 'users.accounts'
                                                    ? 'can_'.$action
                                                    : ($action !== 'view' && $definition['key'] === 'settings.rewards' ? 'can_'.$action.'_rewards' : null);
                                            @endphp
                                            @if(array_key_exists($action, $definition['routes']))
                                                <label><input type="checkbox" class="access-permission-check" @if($legacyId) id="{{ $legacyId }}" @endif data-module="{{ $module }}" data-submodule="{{ $submodule }}" data-action="{{ $action }}" aria-label="{{ ucfirst($action).' '.$definition['label'] }}"><span></span></label>
                                            @else
                                                <label class="is-disabled"><input type="checkbox" disabled><span></span></label>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
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

    .access-all-toggle {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-height: 42px;
        margin: 0;
        padding: 7px 11px;
        border: 1px solid #cfe0f4;
        border-radius: 8px;
        background: #f8fbff;
        color: #102a43;
        cursor: pointer;
    }

    .access-all-toggle input,
    .module-toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .access-all-toggle strong,
    .access-all-toggle small {
        display: block;
        line-height: 1.2;
    }

    .access-all-toggle strong { font-size: 12px; font-weight: 900; }
    .access-all-toggle small { color: #64748b; font-size: 10px; margin-top: 2px; }

    .access-toggle-ui {
        width: 34px;
        height: 20px;
        position: relative;
        border-radius: 999px;
        background: #94a3b8;
        transition: background .2s ease;
        flex: 0 0 auto;
    }

    .access-toggle-ui::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #fff;
        transition: transform .2s ease;
    }

    .access-all-toggle input:checked + .access-toggle-ui { background: #0d6efd; }
    .access-all-toggle input:checked + .access-toggle-ui::after { transform: translateX(14px); }
    .access-all-toggle input:indeterminate + .access-toggle-ui { background: #4f8edb; }

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

    .access-module-heading > span:first-child { display: inline-flex; align-items: center; gap: 8px; }

    .module-toggle {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin: 0 0 0 auto;
        color: #52606d;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
    }

    .module-toggle span::before {
        content: '';
        display: inline-block;
        width: 15px;
        height: 15px;
        margin-right: 5px;
        vertical-align: -3px;
        border: 1px solid #9fb3c8;
        border-radius: 4px;
        background: #fff;
    }

    .module-toggle input:checked + span::before {
        content: '\2713';
        border-color: #0d6efd;
        background: #0d6efd;
        color: #fff;
        text-align: center;
        line-height: 13px;
    }

    .module-toggle input:indeterminate + span::before {
        content: '\2212';
        border-color: #4f8edb;
        background: #4f8edb;
        color: #fff;
        text-align: center;
        line-height: 12px;
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

        .access-tools { align-items: stretch; }
        .access-all-toggle { width: 100%; }
        .access-modal-footer { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .access-modal-footer .btn { margin: 0; }
    }
</style>
