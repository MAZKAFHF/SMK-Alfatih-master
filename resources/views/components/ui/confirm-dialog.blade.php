<div
    data-confirm-dialog
    class="fixed inset-0 z-[90] hidden"
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="confirm-dialog-title"
    aria-describedby="confirm-dialog-message"
>
    <div data-confirm-backdrop class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" aria-hidden="true"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="ctl-popover relative w-full max-w-md !rounded-2xl p-6">
            <div class="flex items-start gap-4">
                <div data-confirm-icon class="flex size-11 shrink-0 items-center justify-center rounded-full" style="background: var(--ctl-danger-soft); color: var(--ctl-danger);">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 id="confirm-dialog-title" class="text-base font-semibold" style="color: var(--ctl-text);"></h3>
                    <p id="confirm-dialog-message" class="ctl-muted mt-1 text-sm"></p>
                </div>
            </div>

            <div class="mt-6 flex justify-center gap-3">
                <x-ui.button variant="outline" size="md" type="button" class="flex-1" data-confirm-cancel>Batal</x-ui.button>
                <x-ui.button variant="danger" size="md" type="button" class="flex-1" data-confirm-ok>Ya, lanjutkan</x-ui.button>
            </div>
        </div>
    </div>
</div>
