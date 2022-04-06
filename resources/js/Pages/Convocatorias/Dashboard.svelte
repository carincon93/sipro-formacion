<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    export let convocatoria

    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    $title = 'Convocatorias - Dashboard'
</script>

<AuthenticatedLayout>
    <div class="py-12">
        {#if isSuperAdmin || checkPermission(authUser, [1, 3, 4, 14])}
            <h1 class="text-4xl text-center">Por favor seleccione el tipo de proyecto para formular proyectos.</h1>
            <div class="flex justify-around mt-24 gap-4">
                {#if isSuperAdmin || checkPermission(authUser, [1, 3, 4, 14])}
                    <a use:inertia href={route('convocatorias.idi.index', convocatoria.id)} class="bg-white overflow-hidden text-center shadow-sm sm:rounded-lg block px-6 py-2 hover:bg-indigo-500 hover:text-white h-52 flex justify-around items-center flex-col w-80 h-96">
                        <figure>
                            <img src={window.basePath + '/images/idi.png'} alt="Proyectos formativos" class="bg-white h-44 w-44 object-contain rounded-full" />
                        </figure>
                        Proyecto formativo
                    </a>
                {/if}
            </div>
        {/if}
    </div>
</AuthenticatedLayout>
