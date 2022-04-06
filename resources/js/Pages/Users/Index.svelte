<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { page } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import { Inertia } from '@inertiajs/inertia'

    import Button from '@/Shared/Button'
    import Pagination from '@/Shared/Pagination'
    import DataTable from '@/Shared/DataTable'
    import DataTableMenu from '@/Shared/DataTableMenu'
    import { Item, Text } from '@smui/list'

    export let usuarios
    export let roles

    $title = 'Usuarios'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let filters = {
        roles: $page.props.filters.roles,
    }
</script>

<AuthenticatedLayout>
    <DataTable class="mt-20" bind:filters showFilters={true}>
        <div slot="title">Usuarios</div>

        <div slot="filters">
            <label for="roles" class="block text-gray-700">Roles:</label>
            <select id="roles" class="mt-1 w-full form-select" bind:value={filters.roles}>
                <option value={null}>Seleccione un rol</option>
                {#each roles as role}
                    <option value={role.name}>{role.name}</option>
                {/each}
            </select>
        </div>
        <div slot="actions">
            {#if isSuperAdmin || checkRole(authUser, [4, 21])}
                <Button on:click={() => Inertia.visit(route('users.online'))} variant="raised" class="ml-1">Usuarios activos</Button>
                <Button on:click={() => Inertia.visit(route('users.create'))} variant="raised">Crear usuario</Button>
            {/if}
        </div>

        <thead slot="thead">
            <tr class="text-left font-bold">
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Nombre</th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Correo electrónico</th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Centro de formación</th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Rol</th>
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl text-center th-actions">Acciones</th>
            </tr>
        </thead>
        <tbody slot="tbody">
            {#each usuarios.data as usuario (usuario.id)}
                <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                    <td class="border-t">
                        <p class="px-6 py-4 focus:text-indigo-500">
                            {usuario.nombre}
                        </p>
                    </td>
                    <td class="border-t">
                        <p class="px-6 py-4">
                            {usuario.email}
                        </p>
                    </td>
                    <td class="border-t">
                        <p class="px-6 py-4">
                            {usuario.centro_formacion?.nombre}
                        </p>
                    </td>
                    <td class="border-t">
                        <ul class="px-6 py-4 list-disc">
                            {#each usuario.roles as role}
                                <li>{role.name}</li>
                            {/each}
                        </ul>
                    </td>
                    <td class="border-t td-actions">
                        <DataTableMenu class={usuarios.data.length < 4 ? 'z-50' : ''}>
                            {#if isSuperAdmin || checkRole(authUser, [4, 21])}
                                <Item on:SMUI:action={() => Inertia.visit(route('users.edit', usuario.id))}>
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

            {#if usuarios.data.length === 0}
                <tr>
                    <td class="border-t px-6 py-4" colspan="5">Sin información registrada</td>
                </tr>
            {/if}
        </tbody>
    </DataTable>
    <Pagination links={usuarios.links} />
</AuthenticatedLayout>
