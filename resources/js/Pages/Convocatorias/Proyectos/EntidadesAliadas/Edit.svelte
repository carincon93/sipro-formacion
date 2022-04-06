<script>
    import AuthenticatedLayout, { title } from '@/Layouts/Authenticated'
    import { Inertia } from '@inertiajs/inertia'
    import { inertia, page, useForm } from '@inertiajs/inertia-svelte'
    import { route, checkRole, checkPermission } from '@/Utils'
    import { _ } from 'svelte-i18n'

    import Label from '@/Shared/Label'
    import LoadingButton from '@/Shared/LoadingButton'
    import Button from '@/Shared/Button'
    import Textarea from '@/Shared/Textarea'
    import Input from '@/Shared/Input'
    import Switch from '@/Shared/Switch'
    import File from '@/Shared/File'
    import Select from '@/Shared/Select'
    import Checkbox from '@smui/checkbox'
    import FormField from '@smui/form-field'
    import { Item, Text } from '@smui/list'
    import Dialog from '@/Shared/Dialog'
    import DataTableMenu from '@/Shared/DataTableMenu'
    import InputError from '@/Shared/InputError'

    export let errors
    export let convocatoria
    export let proyecto
    export let entidadAliada
    export let actividades
    export let actividadesRelacionadas
    export let objetivosEspecificosRelacionados
    export let tiposEntidadAliada
    export let naturalezaEntidadAliada
    export let tiposEmpresa

    $: $title = entidadAliada ? entidadAliada.nombre : null

    /**
     * Permisos
     */
    let authUser = $page.props.auth.user
    let isSuperAdmin = checkRole(authUser, [1])

    let dialogOpen = false
    let sending = false

    let form = useForm({
        _method: 'put',
        tipo: { value: tiposEntidadAliada.find((item) => item.label == entidadAliada.tipo)?.value, label: tiposEntidadAliada.find((item) => item.label == entidadAliada.tipo)?.label },
        nombre: entidadAliada.nombre,
        naturaleza: { value: naturalezaEntidadAliada.find((item) => item.label == entidadAliada.naturaleza)?.value, label: naturalezaEntidadAliada.find((item) => item.label == entidadAliada.naturaleza)?.label },
        tipo_empresa: { value: tiposEmpresa.find((item) => item.label == entidadAliada.tipo_empresa)?.value, label: tiposEmpresa.find((item) => item.label == entidadAliada.tipo_empresa)?.label },
        nit: entidadAliada.nit,
        tiene_convenio: entidadAliada.descripcion_convenio != null ? true : false,
        tiene_grupo_investigacion: entidadAliada.grupo_investigacion != null ? true : false,
        descripcion_convenio: entidadAliada.descripcion_convenio,
        grupo_investigacion: entidadAliada.grupo_investigacion,
        codigo_gruplac: entidadAliada.codigo_gruplac,
        enlace_gruplac: entidadAliada.enlace_gruplac,
        actividades_transferencia_conocimiento: entidadAliada.actividades_transferencia_conocimiento,
        recursos_especie: entidadAliada.recursos_especie,
        descripcion_recursos_especie: entidadAliada.descripcion_recursos_especie,
        recursos_dinero: entidadAliada.recursos_dinero,
        descripcion_recursos_dinero: entidadAliada.descripcion_recursos_dinero,
        carta_intencion: null,
        carta_propiedad_intelectual: null,
        actividad_id: actividadesRelacionadas,
        soporte_convenio: null,
    })

    function submit() {
        if (isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true)) {
            $form.post(route('convocatorias.proyectos.entidades-aliadas.update', [convocatoria.id, proyecto.id, entidadAliada.id]), {
                onStart: () => (sending = true),
                onFinish: () => (sending = false),
                preserveScroll: true,
            })
        }
    }

    function destroy() {
        if (isSuperAdmin || (checkPermission(authUser, [4]) && proyecto.modificable == true)) {
            $form.delete(route('convocatorias.proyectos.entidades-aliadas.destroy', [convocatoria.id, proyecto.id, entidadAliada.id]))
        }
    }
</script>

<AuthenticatedLayout>
    <header class="shadow bg-white" slot="header">
        <div class="flex items-center justify-between lg:px-8 max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div>
                <h1 class="overflow-ellipsis overflow-hidden w-breadcrumb-ellipsis whitespace-nowrap">
                    {#if isSuperAdmin || checkPermission(authUser, [3, 4, 14])}
                        <a use:inertia href={route('convocatorias.proyectos.entidades-aliadas.index', [convocatoria.id, proyecto.id])} class="text-indigo-400 hover:text-indigo-600">Entidades aliadas</a>
                    {/if}
                    <span class="text-indigo-400 font-medium">/</span>
                    {entidadAliada.nombre}
                </h1>
            </div>
        </div>
    </header>

    <div class="flex">
        <div class="bg-white rounded shadow max-w-3xl flex-1">
            <form on:submit|preventDefault={submit}>
                <fieldset class="p-8" disabled={isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true) ? undefined : true}>
                    <div class="mt-8">
                        <Label required class="mb-4" labelFor="tipo" value="Tipo de entidad aliada" />
                        <Select id="tipo" items={tiposEntidadAliada} bind:selectedValue={$form.tipo} error={errors.tipo} autocomplete="off" placeholder="Seleccione el tipo de entidad" required />
                    </div>

                    <div class="mt-8">
                        <Textarea label="Nombre de la entidad aliada/Centro de formación" maxlength="255" id="nombre" error={errors.nombre} bind:value={$form.nombre} required />
                    </div>

                    <div class="mt-8">
                        <Label required class="mb-4" labelFor="naturaleza" value="Naturaleza de la entidad" />
                        <Select id="naturaleza" items={naturalezaEntidadAliada} bind:selectedValue={$form.naturaleza} error={errors.naturaleza} autocomplete="off" placeholder="Seleccione la naturaleza de la entidad" required />
                    </div>

                    <div class="mt-8">
                        <Label required class="mb-4" labelFor="tipo_empresa" value="Tipo de empresa" />
                        <Select id="tipo_empresa" items={tiposEmpresa} bind:selectedValue={$form.tipo_empresa} error={errors.tipo_empresa} autocomplete="off" placeholder="Seleccione el tipo de empresa" required />
                    </div>

                    <div class="mt-8">
                        <Input label="NIT" id="nit" type="text" class="mt-1" bind:value={$form.nit} error={errors.nit} required />
                    </div>

                    <div class="mt-8">
                        <p>¿Hay convenio?</p>
                        <Switch bind:checked={$form.tiene_convenio} />
                    </div>
                    {#if $form.tiene_convenio}
                        <div class="mt-8">
                            <Textarea label="Descipción del convenio" maxlength="400" id="descripcion_convenio" error={errors.descripcion_convenio} bind:value={$form.descripcion_convenio} required />
                        </div>
                    {/if}

                    <div class="mt-8">
                        <p>¿La entidad aliada tiene grupo de investigación?</p>
                        <Switch bind:checked={$form.tiene_grupo_investigacion} />
                    </div>
                    {#if $form.tiene_grupo_investigacion}
                        <div class="mt-8">
                            <Textarea label="Grupo de investigación" maxlength="191" id="grupo_investigacion" error={errors.grupo_investigacion} bind:value={$form.grupo_investigacion} required />
                        </div>

                        <div class="mt-8">
                            <Input label="Código del GrupLAC" id="codigo_gruplac" type="text" class="mt-1" error={errors.codigo_gruplac} placeholder="Ejemplo: COL0000000" bind:value={$form.codigo_gruplac} required={!form.tiene_grupo_investigacion ? undefined : 'required'} />
                        </div>

                        <div class="mt-8">
                            <Input label="Enlace del GrupLAC" id="enlace_gruplac" type="url" class="mt-1" error={errors.enlace_gruplac} placeholder="Ejemplo: https://scienti.minciencias.gov.co/gruplac/jsp/Medicion/graficas/verPerfiles.jsp?id_convocatoria=0nroIdGrupo=0000000" bind:value={$form.enlace_gruplac} required={!form.tiene_grupo_investigacion ? undefined : 'required'} />
                        </div>
                    {/if}

                    <div class="mt-8">
                        <Input label="Recursos en especie entidad aliada ($COP)" id="recursos_especie" type="number" input$min="0" class="mt-1" error={errors.recursos_especie} placeholder="COP" bind:value={$form.recursos_especie} required />
                    </div>

                    <div class="mt-8">
                        <Textarea label="Descripción de los recursos en especie aportados" maxlength="2500" id="descripcion_recursos_especie" error={errors.descripcion_recursos_especie} bind:value={$form.descripcion_recursos_especie} required />
                    </div>

                    <div class="mt-8">
                        <Input label="Recursos en dinero entidad aliada ($COP)" id="recursos_dinero" type="number" input$min="0" class="mt-1" error={errors.recursos_dinero} placeholder="COP" bind:value={$form.recursos_dinero} required />
                    </div>

                    <div class="mt-8">
                        <Textarea label="Descripción de la destinación del dinero aportado" maxlength="2500" id="descripcion_recursos_dinero" error={errors.descripcion_recursos_dinero} bind:value={$form.descripcion_recursos_dinero} required />
                    </div>

                    <div class="mt-8">
                        <Textarea label="Metodología o actividades de transferencia al centro de formación" maxlength="2500" id="actividades_transferencia_conocimiento" error={errors.actividades_transferencia_conocimiento} bind:value={$form.actividades_transferencia_conocimiento} required />
                    </div>

                    <div class="mt-8">
                        <Label class="mb-4" labelFor="carta_intencion" value="ANEXO 7. Carta de intención o acta que soporta el trabajo articulado con entidades aliadas (diferentes al SENA)" />
                        <File id="carta_intencion" type="file" accept="application/pdf" maxSize="10000" class="mt-1" bind:value={$form.carta_intencion} error={errors.carta_intencion} />

                        {#if entidadAliada.carta_intencion}
                            <a target="_blank" class="text-indigo-400 underline inline-block mb-4 flex" download href={route('convocatorias.proyectos.entidades-aliadas.download', [convocatoria.id, proyecto.id, entidadAliada.id, 'carta_intencion'])}>
                                <span class="px-2 py-1 text-green-700 inline-block mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Archivo cargado correctamente. Descargar carta de intención haciendo clic aquí.
                                </span>
                            </a>
                        {/if}
                    </div>

                    <div class="mt-8">
                        <Label class="mb-4" labelFor="carta_propiedad_intelectual" value="ANEXO 8. Propiedad intelectual" />
                        <File id="carta_propiedad_intelectual" type="file" accept="application/pdf" maxSize="10000" class="mt-1" bind:value={$form.carta_propiedad_intelectual} error={errors.carta_propiedad_intelectual} />

                        {#if entidadAliada.carta_propiedad_intelectual}
                            <a target="_blank" class="text-indigo-400 underline inline-block mb-4 flex" download href={route('convocatorias.proyectos.entidades-aliadas.download', [convocatoria.id, proyecto.id, entidadAliada.id, 'carta_propiedad_intelectual'])}>
                                <span class="px-2 py-1 text-green-700 inline-block mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Archivo cargado correctamente. Descargar carta de propiedad intelectual haciendo clic aquí.
                                </span>
                            </a>
                        {/if}
                    </div>

                    {#if $form.progress}
                        <progress value={$form.progress.percentage} max="100" class="mt-4">
                            {$form.progress.percentage}%
                        </progress>
                    {/if}

                    <h6 class="mt-20 mb-12 text-2xl" id="actividades">Actividades</h6>

                    <div class="bg-white rounded shadow overflow-hidden">
                        <div class="p-4">
                            <Label required class="mb-4" labelFor="actividad_id" value="Relacione alguna actividad" />
                            <InputError message={errors.actividad_id} />
                        </div>
                        {#if isSuperAdmin || proyecto.modificable == true}
                            <div class="grid grid-cols-2">
                                {#each actividades as { id, descripcion }, i}
                                    <FormField>
                                        <Checkbox bind:group={$form.actividad_id} value={id} />
                                        <span slot="label">{descripcion}</span>
                                    </FormField>
                                {/each}
                            </div>
                        {:else}
                            <div class="p-2">
                                <ul class="list-disc p-4">
                                    {#each actividades as { id, descripcion }, i}
                                        {#each $form.actividad_id as actividad}
                                            {#if id == actividad}
                                                <li class="first-letter-uppercase mb-4">{descripcion}</li>
                                            {/if}
                                        {/each}
                                    {/each}
                                </ul>
                            </div>
                        {/if}
                    </div>
                </fieldset>
                <div class="px-8 py-4 bg-gray-100 border-t border-gray-200 flex items-center sticky bottom-0">
                    {#if isSuperAdmin || (checkPermission(authUser, [4]) && proyecto.modificable == true)}
                        <button class="text-red-600 hover:underline text-left" tabindex="-1" type="button" on:click={(event) => (dialogOpen = true)}> Eliminar entidad aliada </button>
                    {/if}
                    {#if isSuperAdmin || (checkPermission(authUser, [3, 4]) && proyecto.modificable == true)}
                        <LoadingButton loading={sending} class="btn-indigo ml-auto" type="submit">Editar entidad aliada</LoadingButton>
                    {/if}
                </div>
            </form>
        </div>
        <div class="px-4">
            <h1 class="mb-4">Enlaces de interés</h1>
            <ul>
                <li>
                    <a class="bg-indigo-100 hover:bg-indigo-200 mb-4 px-6 py-2 rounded-3xl text-center text-indigo-400" href="#miembros-entidad-aliada"> Crear miembro de la entidad aliada </a>
                </li>
                <li class="mt-6">
                    <a class="bg-indigo-100 hover:bg-indigo-200 mb-4 px-6 py-2 rounded-3xl text-center text-indigo-400" href="#objetivos-especificos"> Objetivos específicos relacionados </a>
                </li>
            </ul>
        </div>
    </div>

    <h1 class="text-2xl mt-10">Archivos</h1>
    <div class="mt-4 bg-white rounded shadow">
        <table class="w-full whitespace-no-wrap table-fixed data-table">
            <thead>
                <tr class="text-left font-bold">
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full">Archivo</th>
                </tr>
            </thead>

            <tbody>
                <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                    <td class="border-t px-6 pt-6 pb-4">
                        <a target="_blank" class="text-indigo-400 underline inline-block mb-4 flex" download href={route('convocatorias.proyectos.entidades-aliadas.download', [convocatoria.id, proyecto.id, entidadAliada.id, 'carta_intencion'])}>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Descargar carta de intención
                        </a>
                    </td>
                </tr>

                <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                    <td class="border-t px-6 pt-6 pb-4">
                        <a target="_blank" class="text-indigo-400 underline inline-block mb-4 flex" download href={route('convocatorias.proyectos.entidades-aliadas.download', [convocatoria.id, proyecto.id, entidadAliada.id, 'carta_propiedad_intelectual'])}>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Descargar carta de propiedad intelectual
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <h1 class="mt-24 mb-8 text-center text-3xl" id="miembros-entidad-aliada">Miembros de la entidad aliada</h1>
    <div class="mb-6 flex justify-end items-center">
        <div>
            <Button on:click={() => Inertia.visit(route('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada.create', [convocatoria.id, proyecto.id, entidadAliada.id]))} variant="raised">Miembros de la entidad aliada</Button>
        </div>
    </div>
    <div class="bg-white rounded shadow">
        <table class="w-full whitespace-no-wrap table-fixed data-table">
            <thead>
                <tr class="text-left font-bold">
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Nombre </th>
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Correo electrónico </th>
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Número de celular </th>
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl text-center th-actions"> Acciones </th>
                </tr>
            </thead>

            <tbody>
                {#each entidadAliada.miembros_entidad_aliada as miembroEntidadAliada (miembroEntidadAliada.id)}
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
                            <DataTableMenu class={entidadAliada.miembros_entidad_aliada.length < 4 ? 'z-50' : ''}>
                                {#if isSuperAdmin || checkPermission(authUser, [3, 4])}
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
                {#if entidadAliada.miembros_entidad_aliada.length === 0}
                    <tr>
                        <td class="border-t px-6 py-4" colspan="4"> Sin información registrada </td>
                    </tr>
                {/if}
            </tbody>
        </table>
    </div>

    <h1 class="mt-24 mb-8 text-center text-3xl" id="objetivos-especificos">Objetivos específicos</h1>
    <p class="mb-6">
        A continuación, se listan los objetivos específicos relacionados con la entidad aliada. Si dice 'Sin información registrada' por favor diríjase a las <a href="#actividades" class="text-indigo-400">actividades</a> y relacione alguna.
    </p>
    <div class="bg-white rounded shadow">
        <table class="w-full whitespace-no-wrap table-fixed data-table">
            <thead>
                <tr class="text-left font-bold">
                    <th class="px-6 pt-6 pb-4 sticky top-0 z-10 bg-white shadow-xl w-full"> Descripción </th>
                </tr>
            </thead>

            <tbody>
                {#each objetivosEspecificosRelacionados as { id, descripcion }}
                    <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                        <td class="border-t">
                            <p class="px-6 py-4 focus:text-indigo-500">
                                {descripcion}
                            </p>
                        </td>
                    </tr>
                {/each}

                {#if objetivosEspecificosRelacionados.length === 0}
                    <tr>
                        <td class="border-t px-6 py-4" colspan="4"> Sin información registrada </td>
                    </tr>
                {/if}
            </tbody>
        </table>
    </div>

    <Dialog bind:open={dialogOpen}>
        <div slot="title" class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Eliminar recurso
        </div>
        <div slot="content">
            <p>
                ¿Está seguro(a) que desea eliminar este recurso?
                <br />
                Todos los datos se eliminarán de forma permanente.
                <br />
                Está acción no se puede deshacer.
            </p>
        </div>
        <div slot="actions">
            <div class="p-4">
                <Button on:click={(event) => (dialogOpen = false)} variant={null}>Cancelar</Button>
                <Button variant="raised" on:click={destroy}>Confirmar</Button>
            </div>
        </div>
    </Dialog>
</AuthenticatedLayout>
