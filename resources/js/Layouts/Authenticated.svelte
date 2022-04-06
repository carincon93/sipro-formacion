<script context="module">
    import { writable } from 'svelte/store'
    export const title = writable(null)
    export const permissions = writable(null)
</script>

<script>
    import { Inertia } from '@inertiajs/inertia'
    import { inertia, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import ApplicationLogo from '@/Shared/ApplicationLogo'
    import Dropdown from '@/Shared/Dropdown'
    import Icon from '@/Shared/Icon'
    import ResponsiveNavLink from '@/Shared/ResponsiveNavLink'
    import FlashMessages from '@/Shared/FlashMessages'
    import Dialog from '@/Shared/Dialog'
    import Button from '@/Shared/Button'
    import Loading from '@/Shared/Loading'

    let dialogOpen = false
    let showingNavigationDropdown = false

    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let loading = true
    Inertia.on('start', () => {
        loading = false
    })
    Inertia.on('finish', () => {
        loading = true
    })
</script>

<svelte:head>
    <title>{$title ? `${$title} - SGPS-SIPRO` : 'SGPS-SIPRO'}</title>
</svelte:head>

<div>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <!-- Primary Navigation Menu -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a use:inertia href={route('dashboard')}>
                                <ApplicationLogo class="w-10" />
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex sm:items-center">
                            <Button on:click={() => (dialogOpen = true)} variant={null}>Menú de navegación</Button>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="bg-indigo-500 text-white py-2 px-4 shadow border-b-2 flex">
                            {@html $page.props.convocatoria.fase_formateada}
                            {#if isSuperAdmin}
                                <Button on:click={() => Inertia.visit(route('convocatorias.edit', [$page.props.convocatoria.id]))} class="ml-4 mb-2" variant="raised">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </Button>
                            {/if}
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="mr-5 ml-5 relative">
                            <a use:inertia href={route('notificaciones.index')} class="flex items-center hover:text-indigo-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                {#if $page.props.auth.numeroNotificaciones > 0}
                                    <span class="absolute bg-indigo-700 flex h-5/6 items-center justify-center rounded-full text-center text-white text-xs w-5/6" style="top: -10px; left: 10px;">{$page.props.auth.numeroNotificaciones}</span>
                                {/if}
                            </a>
                        </div>
                        <div class="ml-3 relative">
                            <Dropdown class="mt-1" placement="bottom-end">
                                <div class="flex items-center cursor-pointer select-none group">
                                    <div class="text-gray-700 group-hover:text-indigo-600 focus:text-indigo-600 mr-1 whitespace-no-wrap">
                                        <span class="capitalize">{authUser.nombre}</span>
                                    </div>
                                    <Icon name="cheveron-down" class="w-5 h-5 group-hover:fill-indigo-600 fill-gray-700 focus:fill-indigo-600" />
                                </div>
                                <div slot="dropdown" class="mt-2 py-2 shadow-xl bg-white rounded text-sm">
                                    <a use:inertia href={route('reportar-problemas.create')} class="flex items-center px-6 py-2 hover:bg-indigo-500 hover:text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" style="flex-basis: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <span class="ml-1.5">Soporte</span>
                                    </a>

                                    <a use:inertia href={route('users.change-password')} class="flex items-center px-6 py-2 hover:bg-indigo-500 hover:text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" style="flex-basis: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span class="ml-1.5">Cambiar contraseña</span>
                                    </a>

                                    <a use:inertia={{ method: 'post' }} href={route('logout')} class="flex items-center px-6 py-2 hover:bg-indigo-500 hover:text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="ml-1.5">{$_('Logout')}</span>
                                    </a>
                                </div>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <!-- <div class="-mr-2 flex items-center sm:hidden">
                        <button on:click={showingNavigationDropdown = ! showingNavigationDropdown} class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path class="{showingNavigationDropdown ? 'hidden': '', ! showingNavigationDropdown ? 'inline-flex': ''}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path class="{! showingNavigationDropdown ? 'hidden': '', showingNavigationDropdown ? 'inline-flex': ''}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div> -->
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div class="sm:hidden{(showingNavigationDropdown ? ' block' : '', !showingNavigationDropdown ? ' hidden' : '')}">
                <div class="pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink href={route('dashboard')} active={route().current('dashboard')}>Menú de navegación</ResponsiveNavLink>
                </div>

                <!-- Responsive Settings Options -->
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="flex items-center px-4">
                        <div class="font-medium text-base text-gray-800">
                            {authUser.name}
                        </div>
                        <div class="font-medium text-sm text-gray-500">
                            {authUser.email}
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink href={route('logout')} method="post" as="button">
                            {$_('Logout')}
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        {#if $$slots.header}
            <slot name="header" />
        {/if}

        <!-- Page Content -->
        <main class="lg:px-8 max-w-7xl md:p-12 mx-auto px-4 py-8 sm:px-6">
            <FlashMessages />
            <Loading {loading} />
            <slot />
        </main>
    </div>
</div>

<!-- Dialog -->
<Dialog bind:open={dialogOpen} id="main-menu">
    <div slot="title" class="mb-6 text-center text-primary">
        <div class="">Menú de navegación</div>
    </div>
    <div slot="content">
        <div class="grid grid-cols-3 gap-5 p-8">
            {#if isSuperAdmin}
                <Button on:click={() => Inertia.visit(route('centros-formacion.index'))} variant={route().current('centros-formacion.*') ? 'raised' : 'outlined'} class="p-2">Centros de formación</Button>
            {/if}

            {#if isSuperAdmin || checkPermission(authUser, [1, 3, 4, 14])}
                <Button on:click={() => Inertia.visit(route('convocatorias.index'))} variant={route().current('convocatorias.*') ? 'raised' : 'outlined'} class="p-2">Convocatorias</Button>
            {/if}

            {#if isSuperAdmin || checkRole(authUser, [4])}
                <Button on:click={() => Inertia.visit(route('grupos-investigacion.index'))} variant={route().current('grupos-investigacion.*') ? 'raised' : 'outlined'} class="p-2">Grupos, líneas y semilleros de investigación</Button>
            {/if}

            {#if isSuperAdmin}
                <Button on:click={() => Inertia.visit(route('mesas-tecnicas.index'))} variant={route().current('mesas-tecnicas.*') ? 'raised' : 'outlined'} class="p-2">Mesas técnicas</Button>
            {/if}

            {#if isSuperAdmin || checkRole(authUser, [4])}
                <Button on:click={() => Inertia.visit(route('programas-formacion.index'))} variant={route().current('programas-formacion.*') ? 'raised' : 'outlined'} class="p-2">Programas de formación</Button>
            {/if}

            {#if isSuperAdmin}
                <Button on:click={() => Inertia.visit(route('proyectos.index'))} variant={route().current('proyectos.*') ? 'raised' : 'outlined'} class="p-2">Proyectos</Button>
            {/if}

            {#if isSuperAdmin}
                <Button on:click={() => Inertia.visit(route('redes-conocimiento.index'))} variant={route().current('redes-conocimiento.*') ? 'raised' : 'outlined'} class="p-2">Redes de conocimiento</Button>
            {/if}

            {#if isSuperAdmin}
                <Button on:click={() => Inertia.visit(route('regionales.index'))} variant={route().current('regionales.*') ? 'raised' : 'outlined'} class="p-2">Regionales</Button>
            {/if}

            {#if isSuperAdmin}
                <Button on:click={() => Inertia.visit(route('roles.index'))} variant={route().current('roles.*') ? 'raised' : 'outlined'} class="p-2">Roles de sistema</Button>
            {/if}

            {#if isSuperAdmin}
                <Button on:click={() => Inertia.visit(route('tematicas-estrategicas.index'))} variant={route().current('tematicas-estrategicas.*') ? 'raised' : 'outlined'} class="p-2">Temáticas estratégicas SENA</Button>
            {/if}

            {#if isSuperAdmin || checkRole(authUser, [4, 21])}
                <Button on:click={() => Inertia.visit(route('users.index'))} variant={route().current('users.*') ? 'raised' : 'outlined'} class="p-2">Usuarios</Button>
            {/if}
        </div>
    </div>
    <div slot="actions">
        <div class="p-4">
            <Button on:click={(event) => (dialogOpen = false)} variant={null}>Cancelar</Button>
        </div>
    </div>
</Dialog>

<style>
    :global(#main-menu-dialog .mdc-dialog__surface) {
        width: 750px;
        max-width: calc(100vw - 32px) !important;
    }

    :global(#main-menu-dialog .mdc-dialog__content) {
        padding-top: 40px !important;
    }

    :global(#main-menu-dialog .mdc-dialog__title) {
        border-bottom: 1px solid rgba(0, 0, 0, 0.12);
        margin-bottom: 0;
    }

    :global(#main-menu-dialog .mdc-button--outlined) {
        height: auto;
    }

    :global(#main-menu-dialog .mdc-button--raised) {
        height: auto;
    }
</style>
