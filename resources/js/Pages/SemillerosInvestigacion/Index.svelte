<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { page, inertia } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import { Inertia } from '@inertiajs/inertia'

    import Button from '@/Shared/Button'
    import Pagination from '@/Shared/Pagination'
    import DataTable from '@/Shared/DataTable'
    import DataTableMenu from '@/Shared/DataTableMenu'
    import { Item, Text } from '@smui/list'

    export let semillerosInvestigacion
    export let grupoInvestigacion

    $title = 'Semilleros de investigación'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let filters = {}
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1>
                    {#if isSuperAdmin || checkRole(authUser, [4])}
                        <a use:inertia href={route('grupos-investigacion.index')} class="text-indigo-400 hover:text-indigo-600"> Grupos de investigación </a>
                    {/if}
                </h1>
            </div>
        </div>
    </header>
    <DataTable class="mt-20">
        <div slot="title">Semilleros de investigación - Grupo: {grupoInvestigacion.nombre}</div>

        <div slot="actions">
            {#if isSuperAdmin || checkRole(authUser, [4])}
                <Button on:click={() => Inertia.visit(route('grupos-investigacion.semilleros-investigacion.create', grupoInvestigacion.id))} variant="raised">Crear semillero de investigación</Button>
            {/if}
        </div>

        <thead slot="thead">
            <tr class="text-left font-bold">
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Nombre </th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Línea de investigación principal </th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Código </th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl text-center w-1/4"> Acciones </th>
            </tr>
        </thead>
        <tbody slot="tbody">
            {#each semillerosInvestigacion.data as semilleroInvestigacion (semilleroInvestigacion.id)}
                <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                    <td class="border-t">
                        <p class="px-6 py-4 focus:text-indigo-500">
                            {semilleroInvestigacion.nombre}
                        </p>
                    </td>
                    <td class="border-t">
                        <p class="px-6 py-4 focus:text-indigo-500">
                            {semilleroInvestigacion.nombre_linea_principal}
                        </p>
                    </td>
                    <td class="border-t">
                        <p class="px-6 py-4 focus:text-indigo-500">
                            {semilleroInvestigacion.codigo}
                        </p>
                    </td>
                    <td class="border-t td-actions">
                        <DataTableMenu class={semillerosInvestigacion.data.length < 4 ? 'z-50' : ''}>
                            {#if isSuperAdmin || checkRole(authUser, [4])}
                                <Item on:SMUI:action={() => Inertia.visit(route('grupos-investigacion.semilleros-investigacion.edit', [grupoInvestigacion.id, semilleroInvestigacion.id]))}>
                                    <Text>Ver detalles</Text>
                                </Item>
                            {:else}
                                <Item>
                                    <Text>No tiene permisos</Text>
                                </Item>
                            {/if}
                        </DataTableMenu>
                    </td>
                </tr>
            {/each}

            {#if semillerosInvestigacion.data.length === 0}
                <tr>
                    <td class="border-t px-6 py-4" colspan="3"> Sin información registrada </td>
                </tr>
            {/if}
        </tbody>
    </DataTable>
    <Pagination links={semillerosInvestigacion.links} />
</AuthenticatedLayout>
