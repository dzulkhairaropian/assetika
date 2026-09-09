<div x-data="{
    isOpen: false,
    popperInstance: null,
    init() {
        this.$nextTick(() => {
            this.popperInstance = createPopper(this.$refs.button, this.$refs.content, {
                placement: 'bottom-end',
                strategy: 'fixed',
                modifiers: [
                    {
                        name: 'offset',
                        options: {
                            offset: [0, 4],
                        },
                    },
                ],
            });
        });
    },
    toggle() {
        this.isOpen = !this.isOpen;
        if (this.popperInstance) {
            this.popperInstance.update();
        }
    }
}"
@click.away="isOpen = false">
    <div @click="toggle()" x-ref="button" class="cursor-pointer active:scale-95 transition-transform duration-100">
        {{ $button }}
    </div>

    <div class="z-50 fixed" x-ref="content">
        <div x-show="isOpen" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="p-2 bg-white border border-gray-200/90 rounded-xl shadow-lg dark:border-gray-800 dark:bg-gray-900 w-44 origin-top-right">
            <div class="space-y-0.5" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                {{ $content }}
            </div>
        </div>
    </div>
</div>
