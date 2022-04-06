<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { page, useForm } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'
    import { Inertia } from '@inertiajs/inertia'

    import Button from '@/Shared/Button'
    import Stepper from '@/Shared/Stepper'
    import Gantt from '@/Shared/Gantt'
    import InfoMessage from '@/Shared/InfoMessage'
    import Label from '@/Shared/Label'
    import Textarea from '@/Shared/Textarea'
    import LoadingButton from '@/Shared/LoadingButton'
    import Pagination from '@/Shared/Pagination'
    import DataTable from '@/Shared/DataTable'
    import DataTableMenu from '@/Shared/DataTableMenu'
    import { Item, Text } from '@smui/list'

    export let convocatoria
    export let proyecto
    export let actividades
    export let actividadesGantt
    export let errors
    export let to_pdf = false

    $title = 'Actividades'

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let showGantt = false
    let sending = false
    let form = useForm({
        metodologia: proyecto.metodologia,
        metodologia_local: proyecto.metodologia_local,
    })

    function submit() {
        if (isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true)) {
            $form.put(route('convocatorias.proyectos.metodologia', [convocatoria.id, proyecto.id]), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }
</script>

<AuthenticatedLayout>
    <Stepper {convocatoria} {proyecto} />

    <h1 class="text-3xl m-24 text-center">Metodología</h1>

    <form on:submit|preventDefault={submit}>
        <fieldset disabled={isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true) ? undefined : true}>
            <div class="mt-4">
                <div>
                    <Label required class="mb-4" labelFor="metodologia" value="Metodología" />
                    <InfoMessage message="Se debe evidenciar que la metodología se presente de forma organizada y de manera secuencial, de acuerdo con el ciclo P-H-V-A “Planificar – Hacer – Verificar - Actuar” para alcanzar el objetivo general y cada uno de los objetivos específicos." />
                    <Textarea disabled={isSuperAdmin ? false : checkPermission(authUser, [1, 3, 4]) ? false : true} sinContador={true} id="metodologia" error={errors.metodologia} bind:value={$form.metodologia} required />
                </div>
            </div>
        </fieldset>
        <div class="py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
            {#if isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true)}
                <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Guardar metodología</LoadingButton>
            {/if}
        </div>
    </form>

    <hr class="mb-20 mt-20" />

    <h1 class="text-3xl m-24 text-center">Actividades</h1>

    <InfoMessage
        message={actividadesGantt.length == 0
            ? "Debe generar las actividadesGantt en el 'Árbol de objetivos'. <br /><strong>Importante</strong> Una vez creadas las actividadesGantt, edite cada una haciendo clic en los tres puntos, a continuación, registre las fechas (<strong>Se deben registrar todas las fechas para visualizar el diagrama de Gantt</strong>), enlace los productos y rubros correspondientes, de esta manera se completa la cadena de valor."
            : '<strong>Importante</strong> Una vez creadas las actividades, edite cada una haciendo clic en los tres puntos, a continuación, registre las fechas (<strong>Se deben registrar todas las fechas para visualizar el diagrama de Gantt</strong>), enlace los productos y rubros correspondientes, de esta manera se completa la cadena de valor.'}
    />

    {#if showGantt}
        <Button on:click={() => (showGantt = false)}>Ocultar diagrama de Gantt</Button>
    {:else}
        <Button on:click={() => (showGantt = true)}>Visualizar diagrama de Gantt</Button>
    {/if}

    {#if showGantt || to_pdf}
        <Gantt
            items={actividadesGantt}
            request={isSuperAdmin || checkPermission(authUser, [3, 4, 14])
                ? {
                      uri: 'convocatorias.proyectos.actividades.edit',
                      params: [convocatoria.id, proyecto.id],
                  }
                : null}
        />
    {:else}
        <DataTable class="mt-20" routeParams={[convocatoria.id, proyecto.id]}>
            <thead slot="thead">
                <tr class="text-left font-bold">
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Descripción</th>
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Fechas</th>
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Objetivo específico</th>
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl text-center th-actions">Acciones</th>
                </tr>
            </thead>

            <tbody slot="tbody">
                {#each actividades.data as actividad (actividad.id)}
                    <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                        <td class="border-t">
                            <p class="focus:text-indigo-500 my-2 paragraph-ellipsis px-6">
                                {actividad.descripcion}
                            </p>
                        </td>

                        <td class="border-t">
                            <p class="focus:text-indigo-500 my-2 paragraph-ellipsis px-6">
                                {#if actividad.fecha_inicio}
                                    Del {actividad.fecha_inicio} al {actividad.fecha_finalizacion}
                                {:else}
                                    <span class="bg-red-100 text-red-400 hover:bg-red-200 px-2 py-1 rounded-3xl text-center inline-block mt-2 mb-2">Sin fechas definidas</span>
                                {/if}
                            </p>
                        </td>
                        <td class="border-t">
                            <p class="focus:text-indigo-500 my-2 paragraph-ellipsis px-6">
                                {actividad.objetivo_especifico.descripcion}
                            </p>
                        </td>

                        <td class="border-t td-actions">
                            <DataTableMenu class={actividades.data.length < 4 ? 'z-50' : ''}>
                                {#if isSuperAdmin || checkPermission(authUser, [3, 4, 14])}
                                    <Item on:SMUI:action={() => Inertia.visit(route('convocatorias.proyectos.actividades.edit', [convocatoria.id, proyecto.id, actividad.id]))}>
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

                {#if actividades.data.length === 0}
                    <tr>
                        <td class="border-t px-6 py-4" colspan="4">Sin información registrada</td>
                    </tr>
                {/if}
            </tbody>
        </DataTable>
        <Pagination links={actividades.links} />
    {/if}
</AuthenticatedLayout>
