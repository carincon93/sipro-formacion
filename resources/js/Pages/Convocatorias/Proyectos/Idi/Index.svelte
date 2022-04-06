<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission, checkPermissionByUser } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import { Inertia } from '@inertiajs/inertia'

    import Pagination from '@/Shared/Pagination'
    import DataTableMenu from '@/Shared/DataTableMenu'
    import Button from '@/Shared/Button'
    import { Item, Text } from '@smui/list'
    import DataTable from '@/Shared/DataTable'
    import Dialog from '@/Shared/Dialog'

    export let convocatoria
    export let idi

    $title = 'Proyectos formativos'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let filters = {}

    let dialogOpen = true
</script>

<AuthenticatedLayout>
    <DataTable class="mt-20" routeParams={[convocatoria.id]} bind:filters showFilters={true}>
        <div slot="title">Proyectos formativos</div>

        <div slot="actions">
            {#if isSuperAdmin || (checkPermission(authUser, [1]) && convocatoria.fase == 1) || checkPermissionByUser(authUser, [1])}
                <Button on:click={() => Inertia.visit(route('convocatorias.idi.create', [convocatoria.id]))} variant="raised">Crear proyecto formativo</Button>
            {/if}
        </div>

        <thead slot="thead">
            <tr class="text-left font-bold">
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Código </th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Título </th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Fecha de ejecución </th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl text-center th-actions"> Acciones </th>
            </tr>
        </thead>

        <tbody slot="tbody">
            {#each idi.data as { id, proyecto, titulo, fecha_ejecucion }}
                <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                    <td class="border-t">
                        <p class="px-6 py-4 focus:text-indigo-500">
                            {proyecto.codigo}
                        </p>
                    </td>
                    <td class="border-t">
                        <p class="px-6 py-4 focus:text-indigo-500">
                            {titulo}
                        </p>
                    </td>
                    <td class="border-t">
                        <p class="px-6 py-4">
                            {fecha_ejecucion}
                        </p>
                    </td>

                    <td class="border-t td-actions">
                        <DataTableMenu class={idi.data.length < 4 ? 'z-50' : ''}>
                            {#if isSuperAdmin || checkPermission(authUser, [3, 4, 14])}
                                <Item on:SMUI:action={() => Inertia.visit(route('convocatorias.idi.edit', [convocatoria.id, id]))}>
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

            {#if idi.data.length === 0}
                <tr>
                    <td class="border-t px-6 py-4" colspan="5"> Sin información registrada </td>
                </tr>
            {/if}
        </tbody>
    </DataTable>
    <Pagination links={idi.links} />
</AuthenticatedLayout>
