<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { inertia, page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import { Inertia } from '@inertiajs/inertia'

    import DataTableMenu from '@/Shared/DataTableMenu'
    import { Item, Text } from '@smui/list'
    import DataTable from '@/Shared/DataTable'
    import Button from '@/Shared/Button'
    import Pagination from '@/Shared/Pagination'
    import InfoMessage from '@/Shared/InfoMessage'

    export let convocatoria
    export let proyecto
    export let entidadAliada
    export let miembrosEntidadAliada

    $title = 'Miembros de la entidad aliada'

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
                <h1 class="overflow-ellipsis overflow-hidden w-breadcrumb-ellipsis whitespace-nowrap">
                    {#if isSuperAdmin || checkPermission(authUser, [1, 3, 4])}
                        <a use:inertia href={route('convocatorias.proyectos.entidades-aliadas.index', [convocatoria.id, proyecto.id])} class="text-indigo-400 hover:text-indigo-600"> Entidades aliadas </a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    <a use:inertia href={route('convocatorias.proyectos.entidades-aliadas.edit', [convocatoria.id, proyecto.id, entidadAliada.id])} class="text-indigo-400 hover:text-indigo-600">
                        {entidadAliada.nombre}
                    </a>
                    <span class="text-indigo-400 font-medium">/</span>
                    Miembros de la entidad aliada
                </h1>
            </div>
        </div>
    </header>

    <InfoMessage message="Por favor ingrese cada uno de los miembros de la entidad aliada." />

    <DataTable class="mt-20" routeParams={[convocatoria.id, proyecto.id]}>
        <div slot="title">Miembros de la entidad aliada</div>

        <div slot="actions">
            {#if isSuperAdmin || (checkPermission(authUser, [1]) && proyecto.modificable == true)}
                <Button on:click={() => Inertia.visit(route('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada.create', [convocatoria.id, proyecto.id, entidadAliada.id]))} variant="raised">Crear miembro de la entidad aliada</Button>
            {/if}
        </div>

        <thead slot="thead">
            <tr class="text-left font-bold">
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Nombre</th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Correo electrónico</th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Número de celular</th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl text-center th-actions">Acciones</th>
            </tr>
        </thead>

        <tbody slot="tbody">
            {#each miembrosEntidadAliada.data as miembroEntidadAliada (miembroEntidadAliada.id)}
                <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                    <td class="border-t">
                        <p class="px-6 py-4 focus:text-indigo-500">
                            {miembroEntidadAliada.nombre}
                        </p>
                    </td>

                    <td class="border-t">
                        <p class="px-6 py-4 focus:text-indigo-500">
                            {miembroEntidadAliada.email}
                        </p>
                    </td>

                    <td class="border-t">
                        <p class="px-6 py-4 focus:text-indigo-500">
                            {miembroEntidadAliada.numero_celular}
                        </p>
                    </td>

                    <td class="border-t td-actions">
                        <DataTableMenu class={miembrosEntidadAliada.data.length < 4 ? 'z-50' : ''}>
                            {#if isSuperAdmin || checkPermission(authUser, [3, 4, 14])}
                                <Item on:SMUI:action={() => Inertia.visit(route('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada.edit', [convocatoria.id, proyecto.id, entidadAliada.id, miembroEntidadAliada.id]))}>
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

            {#if miembrosEntidadAliada.data.length === 0}
                <tr>
                    <td class="border-t px-6 py-4" colspan="4">Sin información registrada</td>
                </tr>
            {/if}
        </tbody>
    </DataTable>
    <Pagination links={miembrosEntidadAliada.links} />
</AuthenticatedLayout>
