<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { page, useForm } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import { Inertia } from '@inertiajs/inertia'

    import Pagination from '@/Shared/Pagination'
    import DataTable from '@/Shared/DataTable'
    import DataTableMenu from '@/Shared/DataTableMenu'
    import LoadingButton from '@/Shared/LoadingButton'
    import Tags from '@/Shared/Tags'
    import FormField from '@smui/form-field'
    import Radio from '@smui/radio'
    import Button from '@/Shared/Button'
    import InfoMessage from '@/Shared/InfoMessage'
    import { Item, Text } from '@smui/list'

    export let proyectos
    export let proyectosId

    $title = 'Proyectos'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let filters = {
        year: $page.props.filters.year,
    }

    let sending = false
    let form = useForm({
        proyectos_id: null,
        estado: false,
    })
    function submit() {
        if (isSuperAdmin) {
            $form.post(route('proyectos.update.actualizar-estados-proyectos'), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }
</script>

<AuthenticatedLayout>
    <DataTable class="mt-20" bind:filters showFilters={false}>
        <div slot="title">Proyectos</div>

        <div slot="caption">
            <InfoMessage>
                <p>En esta sección puede seleccionar varios códigos de proyectos y cambiar el estado al mismo tiempo. Seleccione entre: Subsanar, finalizar o evaluar.</p>
                <form on:submit|preventDefault={submit}>
                    <Tags enforceWhitelist={false} id="proyectos_id" class="mt-4" whitelist={proyectosId} bind:tags={$form.proyectos_id} placeholder="Código(s) SGPS" required />

                    <div class="flex mt-4 items-center">
                        <FormField>
                            <Radio bind:group={$form.estado} value="1" />
                            <span slot="label">Subsanar</span>
                        </FormField>
                    </div>

                    <div class="flex mt-4 items-center">
                        <FormField>
                            <Radio bind:group={$form.estado} value="2" />
                            <span slot="label">Finalizar</span>
                        </FormField>
                    </div>

                    <div class="flex mt-4 items-center">
                        <FormField>
                            <Radio bind:group={$form.estado} value="3" />
                            <span slot="label">Evaluar</span>
                        </FormField>
                    </div>

                    <div class="py-4 flex items-center sticky bottom-0">
                        {#if isSuperAdmin}
                            <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Actualizar estados de los proyectos</LoadingButton>
                        {/if}
                    </div>
                </form>
            </InfoMessage>

            <hr class="my-10 w-full" />
        </div>

        <div slot="actions">
            {#if isSuperAdmin}
                <Button on:click={() => Inertia.visit(route('proyectos.activos'))} variant="raised">Proyectos activos</Button>
            {/if}
        </div>

        <thead slot="thead">
            <tr class="text-left font-bold">
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Código </th>
                {#if isSuperAdmin}
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Versiones (.pdf) </th>
                {/if}
                <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl text-center th-actions"> Acciones </th>
            </tr>
        </thead>

        <tbody slot="tbody">
            {#each proyectos.data as { id, estado, titulo, codigo, fecha_ejecucion, pdf_versiones, convocatoria }}
                <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                    <td class="border-t">
                        <p class="px-6 py-4 focus:text-indigo-500">
                            {codigo}
                            {#if JSON.parse(estado)?.requiereSubsanar}
                                <br />
                                <span class="bg-red-100 inline-block mt-2 p-2 rounded text-red-400"> Requiere modificaciones </span>
                            {/if}
                        </p>
                    </td>
                    {#if isSuperAdmin}
                        <td class="border-t">
                            {#if pdf_versiones}
                                <ul>
                                    {#each pdf_versiones as version}
                                        <li>
                                            {#if version.estado == 1}
                                                <a class="text-indigo-500 underline" href={route('convocatorias.proyectos.version', [convocatoria.id, id, version.version])}> {version.version}.pdf - Descargar</a>
                                                <small class="block">{version.created_at}</small>
                                            {/if}
                                        </li>
                                    {/each}
                                    {#if pdf_versiones.length == 0}
                                        <p>No se ha generado algún pdf</p>
                                    {/if}
                                </ul>
                            {:else}
                                <p>No se ha generado algún pdf</p>
                            {/if}
                        </td>
                    {/if}

                    <td class="border-t td-actions">
                        <DataTableMenu class={proyectos.data.length < 4 ? 'z-50' : ''}>
                            {#if isSuperAdmin}
                                <Item on:SMUI:action={() => Inertia.visit(route('proyectos.edit', [id]))}>
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

            {#if proyectos.data.length === 0}
                <tr>
                    <td class="border-t px-6 py-4" colspan="5"> Sin información registrada </td>
                </tr>
            {/if}
        </tbody>
    </DataTable>
    <Pagination links={proyectos.links} />
</AuthenticatedLayout>
