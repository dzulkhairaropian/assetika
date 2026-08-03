{{-- <div
    x-show="$store.sidebar.isMobileOpen"
    @click="$store.sidebar.toggleMobileOpen()"
    class="fixed inset-0 bg-gray-900/50 z-[9999] xl:hidden"
>
sidebarToggle ? 'block xl:hidden' : 'hidden'
</div> --}}

<div
  x-show="$store.sidebar.isMobileOpen"
  @click="$store.sidebar.setMobileOpen(false)"
  class="fixed inset-0 z-50 h-screen w-full bg-gray-900/50 xl:hidden"
  style="display: none;"
></div>
