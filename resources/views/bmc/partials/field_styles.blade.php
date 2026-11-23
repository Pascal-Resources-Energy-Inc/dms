<style>
    /* Examples are distinct from values while remaining readable. */
    :is(.bmc-page, .bmc-list-page, .bmc-detail, .bmc-accountability, .bmc-return-modal, .bmc-extension-modal, .bmc-signature-modal) input::placeholder,
    :is(.bmc-page, .bmc-list-page, .bmc-detail, .bmc-accountability, .bmc-return-modal, .bmc-extension-modal, .bmc-signature-modal) textarea::placeholder {
        color: #667085;
        opacity: 1;
    }
    :is(.bmc-return-modal, .bmc-extension-modal, .bmc-signature-modal) select:required:invalid {
        color: #667085;
    }
    :is(.bmc-return-modal, .bmc-extension-modal, .bmc-signature-modal) select option {
        color: #1d2939;
    }
</style>
